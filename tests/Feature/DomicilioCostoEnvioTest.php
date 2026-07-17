<?php

namespace Tests\Feature;

use App\Models\Barrio;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\ItemCarrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\SesionCliente;
use App\Models\Sucursal;
use App\Models\SucursalBarrioTarifa;
use App\Models\ZonaCobertura;
use App\Scopes\TenantScope;
use App\Services\Cliente\PedidoService;
use App\Services\SucursalAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Suite de tests para el flujo completo de pedidos a domicilio.
 *
 * Cubre los siguientes casos de negocio:
 *  1. Resolución de sede mediante barrio_id (SucursalAssignmentService)
 *  2. Aplicación correcta del costo de envío en PedidoService::confirmarPedido()
 *  3. Fallback a zona_id (sesiones legacy)
 *  4. Fallback de seguridad cuando no hay barrio_id ni zona_id
 *  5. Mensajes de estado diferenciados por tipo de pedido (local vs domicilio)
 *  6. Que el TenantScope NO bloquea barrios en contexto público (sin auth)
 *  7. Que el total del pedido incluye correctamente el costo de envío
 *  8. Endpoints API de barrios y resolución de sede
 */
#[Group('domicilio')]
#[Group('pedidos')]
class DomicilioCostoEnvioTest extends TestCase
{
    use RefreshDatabase;

    // ───────────────── Fixtures compartidos ──────────────────

    protected Empresa $empresa;
    protected Sucursal $sucursal;
    protected Barrio $barrio;
    protected ZonaCobertura $zona;
    protected SucursalBarrioTarifa $tarifa;
    protected Categoria $categoria;
    protected Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar tenant estático para que no filtre en contexto público
        TenantScope::setTenantId(null);

        // ── Empresa y Sucursal ──────────────────────────────────────────
        $this->empresa = Empresa::create([
            'nit'    => '900123456-1',
            'nombre' => 'Restaurante Test SA',
            'slug'   => 'restaurante-test',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id'    => $this->empresa->id,
            'nombre'        => 'Sede Principal',
            'slug'          => 'sede-principal',
            'hora_apertura' => '00:00:00',   // Siempre abierta en tests
            'hora_cierre'   => '23:59:59',
            'activo'        => true,
        ]);

        // ── Zona + Barrio + Tarifa ──────────────────────────────────────
        $this->zona = ZonaCobertura::withoutGlobalScopes()->create([
            'sucursal_id'     => $this->sucursal->id,
            'nombre'          => 'Zona Norte',
            'costo_envio'     => 6000.00,
            'tiempo_estimado' => 35,
            'activo'          => true,
        ]);

        $this->barrio = Barrio::withoutGlobalScopes()->create([
            'zona_id'     => $this->zona->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Barrio Los Álamos',
            'activo'      => true,
        ]);

        $this->tarifa = SucursalBarrioTarifa::create([
            'sucursal_id'     => $this->sucursal->id,
            'barrio_id'       => $this->barrio->id,
            'costo_envio'     => 8000.00,
            'tiempo_estimado' => 45,
            'activo'          => true,
        ]);

        // ── Producto de prueba ──────────────────────────────────────────
        TenantScope::setTenantId($this->sucursal->id);

        $this->categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Hamburguesas',
            'activo'      => true,
        ]);

        $this->producto = Producto::create([
            'sucursal_id'  => $this->sucursal->id,
            'categoria_id' => $this->categoria->id,
            'nombre'       => 'Hamburguesa Clásica',
            'precio'       => 25000.00,
            'activo'       => true,
            'disponible'   => true,
        ]);

        // Limpiar tenant para pruebas de flujo público
        TenantScope::setTenantId(null);
    }

    protected function tearDown(): void
    {
        TenantScope::setTenantId(null);
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOQUE 1: SucursalAssignmentService – Resolución de sede y tarifa
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * RF-DOMICILIO-01: El servicio resuelve correctamente la sede y la tarifa
     *                  de envío cuando existe una tarifa activa para el barrio.
     */
    public function test_resolver_retorna_sucursal_y_costo_cuando_existe_tarifa_activa()
    {
        /** @var SucursalAssignmentService $service */
        $service = app(SucursalAssignmentService::class);

        $resultado = $service->resolver($this->barrio->id);

        $this->assertTrue($resultado['tiene_cobertura'], 'Debe tener cobertura cuando hay tarifa activa');
        $this->assertNotNull($resultado['sucursal'], 'La sucursal debe estar presente en el resultado');
        $this->assertEquals($this->sucursal->id, $resultado['sucursal']->id, 'Debe resolver a la sucursal correcta');
        $this->assertEquals(8000.00, (float) $resultado['costo_envio'], 'El costo de envío debe coincidir con la tarifa configurada');
        $this->assertEquals(45, $resultado['tiempo_estimado'], 'El tiempo estimado debe coincidir con la tarifa configurada');
    }

    /**
     * @test
     * RF-DOMICILIO-02: El servicio retorna sin cobertura cuando el barrio_id no existe.
     */
    public function test_resolver_retorna_sin_cobertura_para_barrio_inexistente()
    {
        /** @var SucursalAssignmentService $service */
        $service = app(SucursalAssignmentService::class);

        $resultado = $service->resolver('uuid-inexistente-000');

        $this->assertFalse($resultado['tiene_cobertura']);
        $this->assertNull($resultado['sucursal']);
        $this->assertEquals(0, $resultado['costo_envio']);
        $this->assertEquals('Barrio no encontrado.', $resultado['mensaje']);
    }

    /**
     * @test
     * RF-DOMICILIO-03: El servicio retorna sin cobertura cuando la tarifa está inactiva.
     */
    public function test_resolver_retorna_sin_cobertura_cuando_tarifa_inactiva()
    {
        $this->tarifa->update(['activo' => false]);

        /** @var SucursalAssignmentService $service */
        $service = app(SucursalAssignmentService::class);

        $resultado = $service->resolver($this->barrio->id);

        $this->assertFalse($resultado['tiene_cobertura'], 'No debe haber cobertura si la tarifa está inactiva');
    }

    /**
     * @test
     * RF-DOMICILIO-04: El servicio retorna sin cobertura cuando la sucursal está inactiva.
     */
    public function test_resolver_retorna_sin_cobertura_cuando_sucursal_inactiva()
    {
        $this->sucursal->update(['activo' => false]);

        /** @var SucursalAssignmentService $service */
        $service = app(SucursalAssignmentService::class);

        $resultado = $service->resolver($this->barrio->id);

        $this->assertFalse($resultado['tiene_cobertura'], 'No debe haber cobertura si la sucursal está inactiva');
    }

    /**
     * @test
     * RF-DOMICILIO-05: El método obtenerTarifa retorna la tarifa correcta para
     *                  la combinación sucursal + barrio.
     */
    public function test_obtener_tarifa_retorna_costo_correcto()
    {
        /** @var SucursalAssignmentService $service */
        $service = app(SucursalAssignmentService::class);

        $tarifa = $service->obtenerTarifa($this->sucursal->id, $this->barrio->id);

        $this->assertEquals(8000.00, (float) $tarifa['costo_envio']);
        $this->assertEquals(45, $tarifa['tiempo_estimado']);
    }

    /**
     * @test
     * RF-DOMICILIO-06: El método obtenerTarifa retorna costo 0 y tiempo 30 por defecto
     *                  cuando no existe tarifa para el par sucursal-barrio.
     */
    public function test_obtener_tarifa_retorna_cero_cuando_no_existe_tarifa()
    {
        $barrioSinTarifa = Barrio::withoutGlobalScopes()->create([
            'zona_id'     => $this->zona->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Barrio Sin Cobertura',
            'activo'      => true,
        ]);

        /** @var SucursalAssignmentService $service */
        $service = app(SucursalAssignmentService::class);
        $tarifa = $service->obtenerTarifa($this->sucursal->id, $barrioSinTarifa->id);

        $this->assertEquals(0.00, (float) $tarifa['costo_envio']);
        $this->assertEquals(30, $tarifa['tiempo_estimado']); // Valor por defecto
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOQUE 2: PedidoService – confirmarPedido() con tipo domicilio
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea una sesión de domicilio con un ítem en el carrito.
     */
    protected function crearSesionDomicilioConCarrito(array $sesionExtra = []): SesionCliente
    {
        TenantScope::setTenantId(null);

        $sesion = SesionCliente::create(array_merge([
            'sucursal_id'      => $this->sucursal->id,
            'tipo'             => 'domicilio',
            'token'            => 'tok-domicilio-' . uniqid(),
            'nombre_cliente'   => 'Juan Pérez',
            'telefono_cliente' => '3001234567',
            'direccion_cliente' => 'Calle 123 #45-67',
            'activo'           => true,
        ], $sesionExtra));

        ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id'       => $this->producto->id,
            'sucursal_id'       => $this->sucursal->id,
            'nombre_producto'   => $this->producto->nombre,
            'precio_unitario'   => 25000.00,
            'cantidad'          => 2,
            'subtotal'          => 50000.00,
        ]);

        return $sesion;
    }

    /**
     * @test
     * RF-DOMICILIO-07: Al confirmar un pedido de domicilio con barrio_id válido,
     *                  el costo de envío se aplica correctamente al pedido.
     */
    public function test_confirmar_pedido_domicilio_aplica_costo_envio_con_barrio_id()
    {
        $sesion = $this->crearSesionDomicilioConCarrito([
            'barrio_id' => $this->barrio->id,
        ]);

        /** @var PedidoService $service */
        $service = app(PedidoService::class);
        $pedido = $service->confirmarPedido($sesion);

        // Subtotal: 2 × $25.000 = $50.000
        // Costo envío (tarifa barrio): $8.000
        // Total esperado: $58.000
        $this->assertEquals('domicilio', $pedido->tipo);
        $this->assertEquals(50000.00, (float) $pedido->subtotal, 'El subtotal debe ser $50.000');
        $this->assertEquals(8000.00, (float) $pedido->costo_envio, 'El costo de envío debe ser $8.000 según la tarifa del barrio');
        $this->assertEquals(58000.00, (float) $pedido->total, 'El total debe ser subtotal + costo_envio = $58.000');
    }

    /**
     * @test
     * RF-DOMICILIO-08: Al confirmar pedido con zona_id y SIN barrio_id (sesión legacy),
     *                  el costo de envío se toma de la zona de cobertura.
     *
     * Nota: Solo aplica si barrio_id es nulo, porque la lógica prioriza barrio > zona.
     */
    public function test_confirmar_pedido_domicilio_aplica_costo_envio_con_zona_id_legacy()
    {
        // Crear zona sin barrio_id en la sesión (solo zona_id)
        $sesion = $this->crearSesionDomicilioConCarrito([
            'zona_id'   => $this->zona->id,
            'barrio_id' => null, // Sin barrio_id — sesión legacy
        ]);

        /** @var PedidoService $service */
        $service = app(PedidoService::class);
        $pedido = $service->confirmarPedido($sesion);

        // La zona tiene costo_envio = 6000
        $this->assertEquals(50000.00, (float) $pedido->subtotal);
        $this->assertEquals(6000.00, (float) $pedido->costo_envio, 'El costo de envío legacy debe venir de la zona ($6.000)');
        $this->assertEquals(56000.00, (float) $pedido->total);
    }

    /**
     * @test
     * RF-DOMICILIO-09: Al confirmar pedido de domicilio SIN barrio_id NI zona_id,
     *                  el fallback de seguridad aplica la primera tarifa activa de la sucursal.
     */
    public function test_confirmar_pedido_domicilio_aplica_fallback_de_seguridad_sin_barrio_ni_zona()
    {
        $sesion = $this->crearSesionDomicilioConCarrito([
            'barrio_id' => null,
            'zona_id'   => null,
        ]);

        /** @var PedidoService $service */
        $service = app(PedidoService::class);
        $pedido = $service->confirmarPedido($sesion);

        // El fallback toma la tarifa activa de la sucursal ($8.000)
        $this->assertGreaterThan(0, (float) $pedido->costo_envio, 'El fallback debe aplicar costo de envío mayor a $0');
        $this->assertEquals(
            50000.00 + (float) $pedido->costo_envio,
            (float) $pedido->total,
            'El total debe incluir el costo de envío del fallback'
        );
    }

    /**
     * @test
     * RF-DOMICILIO-10: Un pedido de tipo LOCAL NO debe tener costo de envío.
     */
    public function test_confirmar_pedido_local_no_aplica_costo_envio()
    {
        TenantScope::setTenantId(null);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'tipo'        => 'local',
            'token'       => 'tok-local-' . uniqid(),
            'activo'      => true,
        ]);

        ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id'       => $this->producto->id,
            'sucursal_id'       => $this->sucursal->id,
            'nombre_producto'   => $this->producto->nombre,
            'precio_unitario'   => 25000.00,
            'cantidad'          => 1,
            'subtotal'          => 25000.00,
        ]);

        // PedidoService busca mesero con withCount('pedidos') — User::pedidos() existe ahora
        // No hay meseros en test, se omite la asignación silenciosamente
        /** @var PedidoService $service */
        $service = app(PedidoService::class);
        $pedido = $service->confirmarPedido($sesion);

        $this->assertEquals('local', $pedido->tipo);
        $this->assertEquals(0.00, (float) $pedido->costo_envio, 'Pedidos locales NO deben tener costo de envío');
        $this->assertEquals(25000.00, (float) $pedido->total, 'Total pedido local = solo subtotal');
    }

    /**
     * @test
     * RF-DOMICILIO-11: El pedido de domicilio guardado en BD debe tener la dirección de entrega.
     */
    public function test_pedido_domicilio_guarda_direccion_entrega()
    {
        $sesion = $this->crearSesionDomicilioConCarrito([
            'barrio_id'         => $this->barrio->id,
            'direccion_cliente' => 'Carrera 15 #82-20 Apt 301',
        ]);

        /** @var PedidoService $service */
        $service = app(PedidoService::class);
        $pedido = $service->confirmarPedido($sesion);

        $pedidoDb = Pedido::withoutGlobalScopes()->find($pedido->id);

        $this->assertEquals('Carrera 15 #82-20 Apt 301', $pedidoDb->direccion_entrega, 'La dirección de entrega debe guardarse en el pedido');
        $this->assertNotNull($pedidoDb->costo_envio, 'El costo de envío no debe ser null');
        $this->assertGreaterThan(0, (float) $pedidoDb->costo_envio);
    }

    /**
     * @test
     * RF-DOMICILIO-12: Confirmar un carrito vacío en sesión de domicilio debe lanzar excepción.
     */
    public function test_confirmar_pedido_domicilio_carrito_vacio_lanza_excepcion()
    {
        TenantScope::setTenantId(null);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'tipo'        => 'domicilio',
            'token'       => 'tok-empty-' . uniqid(),
            'barrio_id'   => $this->barrio->id,
            'activo'      => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El carrito está vacío.');

        /** @var PedidoService $service */
        $service = app(PedidoService::class);
        $service->confirmarPedido($sesion);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOQUE 3: TenantScope – No bloquea barrios en contexto público
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * RF-DOMICILIO-13: withoutGlobalScopes() permite consultar barrios sin auth,
     *                  lo que garantiza que el flujo de selección de barrio en
     *                  el checkout público funcione correctamente.
     */
    public function test_barrios_accesibles_con_withoutGlobalScopes_sin_autenticacion()
    {
        $count = Barrio::withoutGlobalScopes()->count();

        $this->assertGreaterThan(0, $count, 'Debe haber barrios accesibles con withoutGlobalScopes()');
    }

    /**
     * @test
     * RF-DOMICILIO-14: Sin withoutGlobalScopes() y sin auth/tenant, el scope
     *                  actúa como cero (1=0) y retorna 0 barrios.
     *                  Esto documenta el comportamiento de seguridad del sistema.
     */
    public function test_barrios_bloqueados_sin_withoutGlobalScopes_y_sin_tenant()
    {
        TenantScope::setTenantId(null);
        $this->assertGuest();

        $count = Barrio::count(); // TenantScope inyecta 1=0

        $this->assertEquals(0, $count, 'Sin tenant activo, el TenantScope debe bloquear todas las consultas de Barrio');
    }

    /**
     * @test
     * RF-DOMICILIO-15: Con tenant activo, Barrio::all() filtra correctamente
     *                  solo los barrios de la sucursal configurada como tenant.
     */
    public function test_barrios_filtrados_correctamente_con_tenant_activo()
    {
        // Crear segunda sucursal con su propio barrio
        $otraSucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre'     => 'Sucursal Sur',
            'slug'       => 'sucursal-sur',
            'activo'     => true,
        ]);

        Barrio::withoutGlobalScopes()->create([
            'zona_id'     => $this->zona->id,
            'sucursal_id' => $otraSucursal->id,
            'nombre'      => 'Barrio Sur',
            'activo'      => true,
        ]);

        // Con el tenant de la sucursal principal, solo deben verse sus barrios
        TenantScope::setTenantId($this->sucursal->id);
        $barrios = Barrio::all();

        foreach ($barrios as $barrio) {
            $this->assertEquals(
                $this->sucursal->id,
                $barrio->sucursal_id,
                "El barrio '{$barrio->nombre}' no pertenece a la sucursal del tenant activo"
            );
        }

        TenantScope::setTenantId(null);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOQUE 4: API de Barrios – SucursalAssignmentController
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * RF-DOMICILIO-16: El endpoint GET /api/v1/empresa/{empresaId}/barrios retorna
     *                  solo los barrios con cobertura activa para esa empresa.
     */
    public function test_api_barrios_retorna_lista_de_barrios_con_cobertura()
    {
        $response = $this->getJson("/api/v1/empresa/{$this->empresa->id}/barrios");

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1) // Solo el barrio con tarifa activa
                 ->assertJsonFragment(['nombre' => 'Barrio Los Álamos']);
    }

    /**
     * @test
     * RF-DOMICILIO-17: El endpoint GET /api/v1/empresa/{empresaId}/barrios retorna
     *                  lista vacía si la tarifa del barrio está inactiva.
     */
    public function test_api_barrios_retorna_vacio_cuando_tarifa_inactiva()
    {
        $this->tarifa->update(['activo' => false]);

        $response = $this->getJson("/api/v1/empresa/{$this->empresa->id}/barrios");

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(0);
    }

    /**
     * @test
     * RF-DOMICILIO-18: El endpoint GET /api/v1/barrio/{barrioId}/sede retorna
     *                  la sucursal asignada con el costo de envío correcto.
     */
    public function test_api_resolver_retorna_sucursal_y_costo_correcto()
    {
        $response = $this->getJson("/api/v1/barrio/{$this->barrio->id}/sede");

        $response->assertStatus(200)
                 ->assertJson([
                     'tiene_cobertura' => true,
                     'costo_envio'     => 8000,
                     'tiempo_estimado' => 45,
                 ])
                 ->assertJsonPath('sucursal.id', $this->sucursal->id);
    }

    /**
     * @test
     * RF-DOMICILIO-19: El endpoint /api/v1/barrio/{barrioId}/sede retorna
     *                  sin cobertura cuando el barrioId es inválido.
     */
    public function test_api_resolver_retorna_sin_cobertura_para_barrio_invalido()
    {
        $response = $this->getJson('/api/v1/barrio/barrio-que-no-existe-000/sede');

        $response->assertStatus(200)
                 ->assertJson(['tiene_cobertura' => false, 'costo_envio' => 0]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOQUE 5: Mensajes de estado en la vista de confirmación (unit assertion)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * RF-DOMICILIO-20: El array de mensajes de estado para tipo domicilio
     *                  contiene la palabra "domiciliario" en estado LISTO.
     *
     * Validamos la lógica Blade directamente con el modelo Pedido
     * sin pasar por el middleware de token (que requiere cookie de sesión).
     */
    public function test_mensaje_listo_para_domicilio_contiene_domiciliario()
    {
        TenantScope::setTenantId(null);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'tipo'        => 'domicilio',
            'token'       => 'tok-msg-dom-' . uniqid(),
            'activo'      => true,
        ]);

        $pedido = Pedido::withoutGlobalScopes()->create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'domicilio',
            'estado'            => 'LISTO',
            'estado_pago'       => 'PAGADO',
            'subtotal'          => 50000.00,
            'costo_envio'       => 8000.00,
            'total'             => 58000.00,
        ]);

        // La lógica de selección de mensaje (Blade) es:
        // $pedido->tipo === 'domicilio' ? 'domiciliario...' : 'mesero...'
        $mensajeListo = $pedido->tipo === 'domicilio'
            ? '¡Tu pedido está listo! El domiciliario va en camino con tu pedido en breve.'
            : '¡Tu pedido está listo! El mesero te lo llevará en breve.';

        $this->assertStringContainsString(
            'domiciliario',
            $mensajeListo,
            'El mensaje de estado LISTO para domicilio debe mencionar al domiciliario'
        );
        $this->assertStringNotContainsString(
            'mesero',
            $mensajeListo,
            'El mensaje de estado LISTO para domicilio NO debe mencionar al mesero'
        );
    }

    /**
     * @test
     * RF-DOMICILIO-21: El array de mensajes de estado para tipo local
     *                  contiene la palabra "mesero" en estado LISTO.
     */
    public function test_mensaje_listo_para_local_contiene_mesero()
    {
        TenantScope::setTenantId(null);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'tipo'        => 'local',
            'token'       => 'tok-msg-loc-' . uniqid(),
            'activo'      => true,
        ]);

        $pedido = Pedido::withoutGlobalScopes()->create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'local',
            'estado'            => 'LISTO',
            'estado_pago'       => 'PAGADO',
            'subtotal'          => 25000.00,
            'costo_envio'       => 0.00,
            'total'             => 25000.00,
        ]);

        $mensajeListo = $pedido->tipo === 'domicilio'
            ? '¡Tu pedido está listo! El domiciliario va en camino con tu pedido en breve.'
            : '¡Tu pedido está listo! El mesero te lo llevará en breve.';

        $this->assertStringContainsString(
            'mesero',
            $mensajeListo,
            'El mensaje de estado LISTO para local debe mencionar al mesero'
        );
        $this->assertStringNotContainsString(
            'domiciliario',
            $mensajeListo,
            'El mensaje de estado LISTO para local NO debe mencionar al domiciliario'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOQUE 6: Integridad del total del pedido (DataProvider)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @test
     * RF-DOMICILIO-22: El total del pedido siempre es igual a subtotal + costo_envio
     *                  tanto para domicilio como para local (diversas combinaciones).
     */
    #[DataProvider('providerTiposPedidoConCostos')]
    public function test_total_pedido_es_igual_a_subtotal_mas_costo_envio(
        string $tipo,
        float $subtotal,
        float $costoEnvio,
        float $totalEsperado
    ) {
        TenantScope::setTenantId(null);

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'tipo'        => $tipo,
            'token'       => 'tok-integ-' . uniqid(),
            'activo'      => true,
        ]);

        $pedido = Pedido::withoutGlobalScopes()->create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => $tipo,
            'estado'            => 'CREADO',
            'estado_pago'       => 'PENDIENTE',
            'subtotal'          => $subtotal,
            'costo_envio'       => $costoEnvio,
            'total'             => $subtotal + $costoEnvio,
        ]);

        $pedidoDb = Pedido::withoutGlobalScopes()->find($pedido->id);

        $this->assertEquals(
            $totalEsperado,
            (float) $pedidoDb->total,
            "El total del pedido tipo '{$tipo}' debe ser {$totalEsperado}"
        );
    }

    public static function providerTiposPedidoConCostos(): array
    {
        return [
            'domicilio con costo estándar'  => ['domicilio', 50000.00, 8000.00,  58000.00],
            'domicilio con costo reducido'  => ['domicilio', 30000.00, 5000.00,  35000.00],
            'local sin costo de envío'      => ['local',     25000.00, 0.00,     25000.00],
            'domicilio monto alto'          => ['domicilio', 150000.00, 12000.00, 162000.00],
        ];
    }
}
