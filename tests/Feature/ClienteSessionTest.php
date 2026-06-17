<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Mesa;
use App\Models\SesionCliente;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClienteSessionTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $sucursal;
    protected $mesa;
    protected $usuarioGerente;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);

        $this->empresa = Empresa::create([
            'nit' => '123456789-0',
            'nombre' => 'Restaurante Test',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Central',
            'slug' => 'sucursal-central',
            'hora_apertura' => '08:00:00',
            'hora_cierre' => '22:00:00',
            'activo' => true,
        ]);

        $this->mesa = Mesa::create([
            'sucursal_id' => $this->sucursal->id,
            'numero' => 1,
            'capacidad' => 4,
            'estado' => 'disponible',
            'qr_activo' => true,
        ]);
        $this->mesa->update([
            'codigo_qr' => Mesa::generarCodigoQR($this->mesa->id),
        ]);

        $this->usuarioGerente = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Gerente Central',
            'correo' => 'gerente@central.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'gerente',
            'activo' => true,
        ]);
    }

    protected function tearDown(): void
    {
        \App\Scopes\TenantScope::setTenantId(null);
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    /** @test */
    public function test_escanear_qr_crea_nueva_sesion_y_ocupa_mesa_rf_c04_rf_c05_rf_c06()
    {
        $response = $this->get(route('cliente.qr', [
            'empresa_slug' => $this->sucursal->empresa->slug,
            'sucursal_slug' => $this->sucursal->slug,
            'codigo' => $this->mesa->codigo_qr
        ]));

        $sesion = SesionCliente::latest()->first();

        $this->assertNotNull($sesion);
        $this->assertEquals('local', $sesion->tipo);
        $this->assertEquals($this->mesa->id, $sesion->mesa_id);
        $this->assertTrue($sesion->activo);
        $this->assertNotEmpty($sesion->token);

        $response->assertRedirect(route('cliente.menu', ['t' => $sesion->token]));

        $this->mesa->refresh();
        $this->assertEquals('ocupada', $this->mesa->estado);
    }

    /** @test */
    public function test_escanear_qr_reutiliza_sesion_activa_de_mesa_rf_c12()
    {
        // Crear primera sesión activa
        $sesionExistente = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-grupo-compartido-123',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Mesa 1',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        // Escanear de nuevo el mismo QR por otro cliente/pestaña
        $response = $this->get(route('cliente.qr', [
            'empresa_slug' => $this->sucursal->empresa->slug,
            'sucursal_slug' => $this->sucursal->slug,
            'codigo' => $this->mesa->codigo_qr
        ]));

        // Debe redirigir con el MISMO token
        $response->assertRedirect(route('cliente.menu', ['t' => $sesionExistente->token]));

        // Verificar que no se creó otra sesión en la base de datos
        $this->assertEquals(1, SesionCliente::count());
    }

    /** @test */
    public function test_escanear_qr_falla_si_mesa_inexistente_o_qr_inactivo()
    {
        // QR Inactivo
        $this->mesa->update(['qr_activo' => false]);

        $response = $this->get(route('cliente.qr', [
            'empresa_slug' => $this->sucursal->empresa->slug,
            'sucursal_slug' => $this->sucursal->slug,
            'codigo' => $this->mesa->codigo_qr
        ]));

        $response->assertRedirect(route('cliente.sin-sesion'));
        $response->assertSessionHas('error');

        // Código QR inexistente
        $responseInexistente = $this->get(route('cliente.qr', [
            'empresa_slug' => $this->sucursal->empresa->slug,
            'sucursal_slug' => $this->sucursal->slug,
            'codigo' => 'qr-inexistente'
        ]));

        $responseInexistente->assertRedirect(route('cliente.sin-sesion'));
    }

    /** @test */
    public function test_acceso_domicilio_muestra_formulario_si_esta_abierto_rf_c03()
    {
        // Congelar tiempo a las 12:00:00 (sucursal abre de 08:00 a 22:00)
        Carbon::setTestNow(Carbon::createFromTimeString('12:00:00'));

        $response = $this->get(route('cliente.domicilio', ['empresa_slug' => $this->sucursal->empresa->slug, 'sucursal_slug' => $this->sucursal->slug]));

        $response->assertStatus(200);
        $response->assertViewIs('cliente.acceso-domicilio');
    }

    /** @test */
    public function test_acceso_domicilio_redirige_si_esta_cerrado_rf_c03()
    {
        // Congelar tiempo a las 23:00:00 (sucursal cerrada)
        Carbon::setTestNow(Carbon::createFromTimeString('23:00:00'));

        $response = $this->get(route('cliente.domicilio', ['empresa_slug' => $this->sucursal->empresa->slug, 'sucursal_slug' => $this->sucursal->slug]));

        $response->assertRedirect(route('cliente.sin-sesion'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_crear_sesion_domicilio_valida_y_crea_sesion_rf_c01_rf_c02()
    {
        Carbon::setTestNow(Carbon::createFromTimeString('12:00:00'));

        $data = [
            'nombre_cliente' => 'John Doe',
            'telefono_cliente' => '3001234567',
            'direccion_cliente' => 'Calle 123 #45-67',
        ];

        $response = $this->post(route('cliente.domicilio.registro', ['empresa_slug' => $this->sucursal->empresa->slug, 'sucursal_slug' => $this->sucursal->slug]), $data);

        $sesion = SesionCliente::latest()->first();

        $this->assertNotNull($sesion);
        $this->assertEquals('domicilio', $sesion->tipo);
        $this->assertNull($sesion->mesa_id);
        $this->assertEquals('John Doe', $sesion->nombre_cliente);
        $this->assertEquals('3001234567', $sesion->telefono_cliente);
        $this->assertEquals('Calle 123 #45-67', $sesion->direccion_cliente);
        $this->assertTrue($sesion->activo);

        $response->assertRedirect(route('cliente.menu', ['t' => $sesion->token]));
    }

    /** @test */
    public function test_crear_sesion_domicilio_falla_si_faltan_campos_rf_c02()
    {
        Carbon::setTestNow(Carbon::createFromTimeString('12:00:00'));

        $dataIncompleta = [
            'nombre_cliente' => '',
            'telefono_cliente' => '3001234567',
            // dirección faltante
        ];

        $response = $this->post(route('cliente.domicilio.registro', ['empresa_slug' => $this->sucursal->empresa->slug, 'sucursal_slug' => $this->sucursal->slug]), $dataIncompleta);

        $response->assertSessionHasErrors(['nombre_cliente', 'direccion_cliente']);
        $this->assertEquals(0, SesionCliente::count());
    }

    /** @test */
    public function test_menu_cliente_requiere_token_valido()
    {
        // Sin token
        $responseSinToken = $this->get(route('cliente.menu'));
        $responseSinToken->assertRedirect(route('cliente.sin-sesion'));

        // Con token incorrecto
        $responseTokenInvalido = $this->get(route('cliente.menu', ['t' => 'token-invalido-12345']));
        $responseTokenInvalido->assertRedirect(route('cliente.sin-sesion'));
    }

    /** @test */
    public function test_menu_cliente_carga_correctamente_con_token_valido()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-valido-123',
            'tipo' => 'domicilio',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);

        $response = $this->get(route('cliente.menu', ['t' => $sesion->token]));

        $response->assertStatus(200);
        $response->assertViewIs('cliente.menu');
        $response->assertViewHas('sesion');
        $response->assertViewHas('token', $sesion->token);
    }

    /** @test */
    public function test_logout_manual_cierra_sesion_y_libera_mesa_rf_c09()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-manual-logout-123',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $response = $this->post(route('cliente.logout', ['t' => $sesion->token]));

        $response->assertRedirect(route('cliente.sin-sesion'));

        $sesion->refresh();
        $this->assertFalse($sesion->activo);
        $this->assertNotNull($sesion->token); // Token remains, constraint not violated

        $this->mesa->refresh();
        $this->assertEquals('disponible', $this->mesa->estado);
    }

    /** @test */
    public function test_logout_inactividad_cierra_sesion_rf_c07()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-inactivity-logout-123',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $response = $this->post(route('cliente.logout.inactividad', ['t' => $sesion->token]));

        $response->assertRedirect(route('cliente.sin-sesion'));
        $response->assertSessionHas('error');

        $sesion->refresh();
        $this->assertFalse($sesion->activo);
    }

    /** @test */
    public function test_middleware_inactividad_bloquea_y_cierra_sesion_despues_de_10_minutos_rf_c07()
    {
        Carbon::setTestNow(Carbon::createFromTimeString('12:00:00'));

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-timeout-123',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Inactivo',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        // 1. Acceder dentro de los 10 minutos (por ejemplo, a los 5 minutos)
        Carbon::setTestNow(Carbon::createFromTimeString('12:05:00'));
        $responseOK = $this->get(route('cliente.menu', ['t' => $sesion->token]));
        $responseOK->assertStatus(200);

        // 2. Acceder después de los 10 minutos (por ejemplo, a los 12:16:00, 11 minutos después de la última actividad a las 12:05:00)
        Carbon::setTestNow(Carbon::createFromTimeString('12:16:00'));
        $responseTimeout = $this->get(route('cliente.menu', ['t' => $sesion->token]));

        $responseTimeout->assertRedirect(route('cliente.sin-sesion'));

        $sesion->refresh();
        $this->assertFalse($sesion->activo);

        $this->mesa->refresh();
        $this->assertEquals('disponible', $this->mesa->estado);
    }

    /** @test */
    public function test_administrador_y_gerente_pueden_forzar_cierre_de_sesion_rf_c10()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-force-logout-123',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente A Forzar',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $this->actingAs($this->usuarioGerente);

        Livewire::test(\App\Livewire\Admin\Mesas\ManageMesas::class)
            ->call('cerrarSesionCliente', $sesion->id)
            ->assertHasNoErrors();

        $sesion->refresh();
        $this->assertFalse($sesion->activo);

        $this->mesa->refresh();
        $this->assertEquals('disponible', $this->mesa->estado);
    }

    /** @test */
    public function test_cliente_ownership_middleware_autoriza_pedido_y_pago()
    {
        $sesionMia = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-mio',
            'tipo' => 'domicilio',
            'nombre_cliente' => 'Mi Sesion',
            'activo' => true,
        ]);

        $sesionAjena = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-ajeno',
            'tipo' => 'domicilio',
            'nombre_cliente' => 'Ajeno',
            'activo' => true,
        ]);

        $pedidoMio = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionMia->id,
            'tipo' => 'domicilio',
            'estado' => 'PENDIENTE',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $pedidoAjeno = Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionAjena->id,
            'tipo' => 'domicilio',
            'estado' => 'PENDIENTE',
            'subtotal' => 200,
            'total' => 200,
        ]);

        $pagoMio = Pago::create([
            'pedido_id' => $pedidoMio->id,
            'sucursal_id' => $this->sucursal->id,
            'metodo' => 'NEQUI',
            'monto' => 100,
            'estado' => 'PENDIENTE',
        ]);

        $pagoAjeno = Pago::create([
            'pedido_id' => $pedidoAjeno->id,
            'sucursal_id' => $this->sucursal->id,
            'metodo' => 'NEQUI',
            'monto' => 200,
            'estado' => 'PENDIENTE',
        ]);

        // Intentar acceder a pedido propio -> OK
        $responsePedidoMio = $this->get(route('cliente.pedido.estado', ['pedidoId' => $pedidoMio->id, 't' => $sesionMia->token]));
        $responsePedidoMio->assertStatus(200);

        // Intentar acceder a pedido ajeno -> Forbidden
        $responsePedidoAjeno = $this->get(route('cliente.pedido.estado', ['pedidoId' => $pedidoAjeno->id, 't' => $sesionMia->token]));
        $responsePedidoAjeno->assertStatus(403);

        // Intentar acceder a pago propio -> OK
        $responsePagoMio = $this->get(route('cliente.pago.estado', ['pagoId' => $pagoMio->id, 't' => $sesionMia->token]));
        $responsePagoMio->assertStatus(200);

        // Intentar acceder a pago ajeno -> Forbidden
        $responsePagoAjeno = $this->get(route('cliente.pago.estado', ['pagoId' => $pagoAjeno->id, 't' => $sesionMia->token]));
        $responsePagoAjeno->assertStatus(403);
    }

    /** @test */
    public function test_agregar_producto_sin_personalizacion_al_carrito()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-carrito-1',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Bebidas',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Americano',
            'descripcion' => 'Café negro clásico',
            'precio' => 5000,
            'precio_oferta' => 0,
            'activo' => true,
            'disponible' => true,
            'permite_notas' => true,
        ]);

        $response = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cart_count' => 2,
            'cart_total' => 10000,
        ]);

        $this->assertDatabaseHas('items_carrito', [
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => 5000,
            'subtotal' => 10000,
        ]);
    }

    /** @test */
    public function test_agregar_producto_con_variantes_y_adiciones()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-carrito-2',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cafés',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Latte',
            'precio' => 6000,
            'precio_oferta' => 0,
            'activo' => true,
            'disponible' => true,
            'permite_notas' => true,
        ]);

        // Crear una variante
        $variante = \App\Models\VarianteProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Tamaño',
            'obligatorio' => true,
            'opciones' => [
                ['nombre' => 'Mediano', 'precio' => 1000, 'tipo_impacto' => 'incremental'],
                ['nombre' => 'Grande', 'precio' => 8000, 'tipo_impacto' => 'fijo']
            ]
        ]);

        // Crear otra variante (leche)
        $varianteLeche = \App\Models\VarianteProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Leche',
            'obligatorio' => false,
            'opciones' => [
                ['nombre' => 'Deslactosada', 'precio' => 500, 'tipo_impacto' => 'incremental'],
            ]
        ]);

        // Crear adiciones
        $adicion1 = \App\Models\AdicionProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Chispas de Chocolate',
            'precio' => 800,
            'activo' => true,
        ]);

        $adicion2 = \App\Models\AdicionProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Crema Batida',
            'precio' => 1200,
            'activo' => true,
        ]);

        // Caso 1: Variante fija + Variante incremental + Adiciones
        // Elegimos: Tamaño: Grande (fijo 8000), Leche: Deslactosada (+500), Adiciones: Chispas de Chocolate (+800), Crema Batida (+1200)
        // Precio unitario esperado: 8000 (fijo reemplaza 6000 base) + 500 (incremental) + 800 (adicion 1) + 1200 (adicion 2) = 10500.
        // Cantidad: 2. Subtotal esperado: 21000.
        $response = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'variantes_elegidas' => [
                'Tamaño' => 'Grande',
                'Leche' => 'Deslactosada',
            ],
            'adiciones_elegidas' => [
                $adicion1->id,
                $adicion2->id
            ],
            'notas' => 'Bien caliente'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cart_count' => 2,
            'cart_total' => 21000,
        ]);

        $item = \App\Models\ItemCarrito::latest()->first();
        $this->assertEquals(10500, $item->precio_unitario);
        $this->assertEquals(21000, $item->subtotal);

        // Caso 2: Variante incremental únicamente
        // Elegimos: Tamaño: Mediano (incremental 1000)
        // Precio unitario esperado: 6000 (base) + 1000 (incremental) = 7000.
        // Cantidad: 1.
        $response2 = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'variantes_elegidas' => [
                'Tamaño' => 'Mediano',
            ],
        ]);

        $response2->assertStatus(200);
        $this->assertEquals(3, \App\Models\ItemCarrito::sum('cantidad'));
        $this->assertEquals(28000, \App\Models\ItemCarrito::sum('subtotal'));
    }

    /** @test */
    public function test_agregar_mismo_producto_con_y_sin_adiciones_crea_items_separados()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-mismo-prod-adiciones',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test Mismo',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Bebidas',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Americano',
            'precio' => 4000,
            'activo' => true,
            'disponible' => true,
        ]);

        $adicion = \App\Models\AdicionProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Leche Condensada',
            'precio' => 1500,
            'activo' => true,
        ]);

        // 1. Agregar con la adición
        $response1 = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'adiciones_elegidas' => [$adicion->id],
        ]);
        $response1->assertStatus(200);

        // 2. Agregar sin la adición
        $response2 = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'adiciones_elegidas' => [],
        ]);
        $response2->assertStatus(200);

        // Deben haber 2 items en el carrito (no deben haberse fusionado)
        $items = \App\Models\ItemCarrito::where('sesion_cliente_id', $sesion->id)->get();
        $this->assertCount(2, $items);

        $itemConAdicion = $items->first(function($i) { return !empty($i->adiciones_elegidas); });
        $itemSinAdicion = $items->first(function($i) { return empty($i->adiciones_elegidas); });

        $this->assertNotNull($itemConAdicion);
        $this->assertNotNull($itemSinAdicion);

        $this->assertEquals(5500, $itemConAdicion->precio_unitario); // 4000 base + 1500 adicion
        $this->assertEquals(4000, $itemSinAdicion->precio_unitario); // 4000 base
    }

    /** @test */
    public function test_validar_variantes_obligatorias()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-carrito-3',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cafés',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Latte',
            'precio' => 6000,
            'precio_oferta' => 0,
            'activo' => true,
            'disponible' => true,
            'permite_notas' => true,
        ]);

        // Crear una variante obligatoria
        $variante = \App\Models\VarianteProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Tamaño',
            'obligatorio' => true,
            'opciones' => [
                ['nombre' => 'Mediano', 'precio' => 1000, 'tipo_impacto' => 'incremental'],
            ]
        ]);

        // Intentar agregar al carrito sin seleccionar la variante obligatoria 'Tamaño'
        $response = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'variantes_elegidas' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => "La variante 'Tamaño' es obligatoria."
        ]);

        $this->assertEquals(0, \App\Models\ItemCarrito::count());
    }

    /** @test */
    public function test_validar_limite_adiciones()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-carrito-4',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cafés',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Latte',
            'precio' => 6000,
            'precio_oferta' => 0,
            'activo' => true,
            'disponible' => true,
            'permite_notas' => true,
            'limite_minimo_adiciones' => 1,
            'limite_maximo_adiciones' => 2,
        ]);

        $adicion1 = \App\Models\AdicionProducto::create(['producto_id' => $producto->id, 'nombre' => 'A1', 'precio' => 500, 'activo' => true]);
        $adicion2 = \App\Models\AdicionProducto::create(['producto_id' => $producto->id, 'nombre' => 'A2', 'precio' => 500, 'activo' => true]);
        $adicion3 = \App\Models\AdicionProducto::create(['producto_id' => $producto->id, 'nombre' => 'A3', 'precio' => 500, 'activo' => true]);

        // Caso 1: Cero adiciones elegidas (mínimo es 1)
        $response1 = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'adiciones_elegidas' => [],
        ]);

        $response1->assertStatus(422);
        $response1->assertJson([
            'error' => "Debes seleccionar al menos 1 adiciones."
        ]);

        // Caso 2: Tres adiciones elegidas (máximo es 2)
        $response2 = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'adiciones_elegidas' => [$adicion1->id, $adicion2->id, $adicion3->id],
        ]);

        $response2->assertStatus(422);
        $response2->assertJson([
            'error' => "No puedes seleccionar más de 2 adiciones."
        ]);

        $this->assertEquals(0, \App\Models\ItemCarrito::count());
    }

    /** @test */
    public function test_aplicar_precio_oferta_automatico()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-carrito-5',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cafés',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Promo',
            'precio' => 6000,
            'precio_oferta' => 4500,
            'activo' => true,
            'disponible' => true,
        ]);

        $response = $this->postJson(route('cliente.carrito.agregar', ['t' => $sesion->token]), [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cart_total' => 4500,
        ]);

        $this->assertDatabaseHas('items_carrito', [
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'precio_unitario' => 4500,
        ]);
    }

    /** @test */
    public function test_actualizar_cantidad_y_eliminar_item()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-carrito-6',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cafés',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Latte',
            'precio' => 6000,
            'activo' => true,
            'disponible' => true,
        ]);

        $item = \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesion->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 6000,
            'cantidad' => 1,
            'subtotal' => 6000,
        ]);

        // Actualizar cantidad a 3
        $responseActualizar = $this->postJson(route('cliente.carrito.actualizar', ['id' => $item->id, 't' => $sesion->token]), [
            'cantidad' => 3,
        ]);

        $responseActualizar->assertStatus(200);
        $responseActualizar->assertJson([
            'success' => true,
            'cart_count' => 3,
            'cart_total' => 18000,
        ]);

        $this->assertDatabaseHas('items_carrito', [
            'id' => $item->id,
            'cantidad' => 3,
            'subtotal' => 18000,
        ]);

        // Eliminar el item
        $responseEliminar = $this->postJson(route('cliente.carrito.eliminar', ['id' => $item->id, 't' => $sesion->token]));

        $responseEliminar->assertStatus(200);
        $responseEliminar->assertJson([
            'success' => true,
            'cart_count' => 0,
            'cart_total' => 0,
        ]);

        $this->assertDatabaseMissing('items_carrito', [
            'id' => $item->id,
        ]);
    }

    /** @test */
    public function test_confirmar_pedido_vacio_falla()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-carrito-7',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Test',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $response = $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesion->token]));

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'El carrito está vacío.'
        ]);

        $this->assertEquals(0, Pedido::count());
    }

    /** @test */
    public function test_confirmar_pedido_exitoso()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-carrito-8',
            'tipo' => 'domicilio',
            'nombre_cliente' => 'Cliente Test',
            'direccion_cliente' => 'Calle Falsa 123',
            'activo' => true,
        ]);

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cafés',
            'orden' => 1,
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Café Exitoso',
            'precio' => 5000,
            'activo' => true,
            'disponible' => true,
        ]);

        // Crear una zona de cobertura
        $zona = \App\Models\ZonaCobertura::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Norte',
            'costo_envio' => 2000,
            'activo' => true,
        ]);

        $sesion->update(['zona_id' => $zona->id]);

        $item = \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesion->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 5000,
            'cantidad' => 2,
            'subtotal' => 10000,
            'variantes_elegidas' => [],
            'adiciones_elegidas' => [],
            'notas' => 'Sin azucar',
        ]);

        $response = $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesion->token]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pedido confirmado con éxito.',
        ]);

        // Verificar que se creó el pedido
        $pedido = Pedido::first();
        $this->assertNotNull($pedido);
        $this->assertEquals($sesion->id, $pedido->sesion_cliente_id);
        $this->assertEquals('domicilio', $pedido->tipo);
        $this->assertEquals('PENDIENTE_PAGO', $pedido->estado);
        $this->assertEquals(10000, $pedido->subtotal);
        $this->assertEquals(2000, $pedido->costo_envio);
        $this->assertEquals(12000, $pedido->total);
        $this->assertEquals('Calle Falsa 123', $pedido->direccion_entrega);

        // Verificar detalle_pedido
        $this->assertDatabaseHas('detalle_pedido', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'precio_unitario' => 5000,
            'cantidad' => 2,
            'subtotal' => 10000,
            'notas' => 'Sin azucar',
        ]);

        // Verificar historial_estado_pedido
        $this->assertDatabaseHas('historial_estado_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => 'PENDIENTE_PAGO',
        ]);

        // Verificar que se vació el carrito
        $this->assertEquals(0, \App\Models\ItemCarrito::count());
    }

    /** @test */
    public function test_flujo_pago_efectivo_local_espera_mesero()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-efectivo-local',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Local',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create(['sucursal_id' => $this->sucursal->id, 'nombre' => 'Comida', 'orden' => 1, 'activo' => true]);
        $producto = Producto::create(['sucursal_id' => $this->sucursal->id, 'categoria_id' => $categoria->id, 'nombre' => 'Producto 1', 'precio' => 10000, 'activo' => true, 'disponible' => true]);

        \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesion->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
        ]);

        // 1. Confirmar pedido
        $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesion->token]))->assertStatus(200);
        $pedido = Pedido::first();
        $this->assertEquals('PENDIENTE_PAGO', $pedido->estado);

        // 2. Procesar pago en efectivo
        $responsePago = $this->post(route('cliente.pago.procesar', ['t' => $sesion->token]), [
            'metodo' => 'Efectivo',
        ]);
        $responsePago->assertRedirect(route('cliente.pago.pendiente', ['pagoId' => Pago::first()->id, 't' => $sesion->token]));

        $pedido->refresh();
        $this->assertEquals('PENDIENTE_PAGO', $pedido->estado);
        $this->assertEquals('Efectivo', $pedido->metodo_pago);

        // 3. Confirmar pago como Mesero
        $mesero = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Mesero Test',
            'correo' => 'mesero@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'mesero',
            'activo' => true,
        ]);
        // Setup session for token auth
        \App\Models\Sesion::create([
            'usuario_id' => $mesero->id,
            'sucursal_id' => $this->sucursal->id,
            'token' => 'mesero-token-123',
            'activa' => true,
            'fecha_expiracion' => now()->addDays(1),
        ]);
        // Asignar el mesero al pedido
        $pedido->update(['mesero_id' => $mesero->id]);

        $this->actingAs($mesero);
        $responseConfirmar = $this->postJson(route('mesero.pedidos.confirmar-pago', ['id' => $pedido->id, '_st' => 'mesero-token-123']));
        $responseConfirmar->assertStatus(200)->assertJson(['ok' => true]);

        $pedido->refresh();
        $this->assertEquals('CREADO', $pedido->estado);
        $this->assertEquals('COMPLETADO', $pedido->estado_pago);
        $this->assertEquals('COMPLETADO', Pago::first()->estado);
    }

    /** @test */
    public function test_flujo_pago_efectivo_domicilio_cocina_directo()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-efectivo-domi',
            'tipo' => 'domicilio',
            'nombre_cliente' => 'Cliente Domicilio',
            'direccion_cliente' => 'Calle 100',
            'activo' => true,
        ]);

        $categoria = Categoria::create(['sucursal_id' => $this->sucursal->id, 'nombre' => 'Comida', 'orden' => 1, 'activo' => true]);
        $producto = Producto::create(['sucursal_id' => $this->sucursal->id, 'categoria_id' => $categoria->id, 'nombre' => 'Producto 1', 'precio' => 10000, 'activo' => true, 'disponible' => true]);

        \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesion->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
        ]);

        // Confirmar pedido
        $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesion->token]))->assertStatus(200);

        // Procesar pago en efectivo para Domicilio
        $responsePago = $this->post(route('cliente.pago.procesar', ['t' => $sesion->token]), [
            'metodo' => 'Efectivo',
        ]);

        $pedido = Pedido::first();
        // Para domicilio en efectivo, pasa directo a CREADO para cocina
        $this->assertEquals('CREADO', $pedido->estado);
        $this->assertEquals('PENDIENTE', $pedido->estado_pago);
    }

    /** @test */
    public function test_flujo_pago_nequi_aprobado()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-nequi-ok',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Local',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create(['sucursal_id' => $this->sucursal->id, 'nombre' => 'Comida', 'orden' => 1, 'activo' => true]);
        $producto = Producto::create(['sucursal_id' => $this->sucursal->id, 'categoria_id' => $categoria->id, 'nombre' => 'Producto 1', 'precio' => 10000, 'activo' => true, 'disponible' => true]);

        \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesion->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
        ]);

        $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesion->token]))->assertStatus(200);

        // Procesar pago con Nequi
        $responsePago = $this->post(route('cliente.pago.procesar', ['t' => $sesion->token]), [
            'metodo' => 'Nequi',
            'nequi_telefono' => '3009999999',
            'nequi_correo' => 'test@nequi.com',
        ]);
        
        $pago = Pago::first();
        $responsePago->assertRedirect(route('cliente.pago.pendiente', ['pagoId' => $pago->id, 't' => $sesion->token]));

        // Simular confirmación aprobada
        $responseSimular = $this->post(route('cliente.pago.simular', ['pagoId' => $pago->id, 't' => $sesion->token]), [
            'resultado' => 'approved',
        ]);

        $responseSimular->assertRedirect(route('cliente.confirmacion', ['t' => $sesion->token]));

        $pedido = Pedido::first();
        $pedido->refresh();
        $pago->refresh();

        $this->assertEquals('CREADO', $pedido->estado);
        $this->assertEquals('COMPLETADO', $pedido->estado_pago);
        $this->assertEquals('COMPLETADO', $pago->estado);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PagoAprobadoMail::class, function ($mail) use ($pago) {
            return $mail->pago->id === $pago->id;
        });
    }

    /** @test */
    public function test_flujo_pago_nequi_fallido_incrementa_intentos()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-nequi-fail',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Local',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create(['sucursal_id' => $this->sucursal->id, 'nombre' => 'Comida', 'orden' => 1, 'activo' => true]);
        $producto = Producto::create(['sucursal_id' => $this->sucursal->id, 'categoria_id' => $categoria->id, 'nombre' => 'Producto 1', 'precio' => 10000, 'activo' => true, 'disponible' => true]);

        \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesion->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
        ]);

        $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesion->token]))->assertStatus(200);

        $this->post(route('cliente.pago.procesar', ['t' => $sesion->token]), [
            'metodo' => 'Nequi',
            'nequi_telefono' => '3009999999',
            'nequi_correo' => 'test@nequi.com',
        ]);
        
        $pago = Pago::first();

        // Simular confirmación fallida
        $responseSimular = $this->post(route('cliente.pago.simular', ['pagoId' => $pago->id, 't' => $sesion->token]), [
            'resultado' => 'declined',
        ]);

        $responseSimular->assertRedirect(route('cliente.pago', ['t' => $sesion->token]));

        $pago->refresh();
        $this->assertEquals('FALLIDO', $pago->estado);
        $this->assertEquals(1, $pago->intentos);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\PagoFallidoMail::class, function ($mail) use ($pago) {
            return $mail->pago->id === $pago->id;
        });
    }

    /** @test */
    public function test_limite_intentos_nequi_bloquea_formulario()
    {
        $sesion = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-nequi-lock',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Local',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create(['sucursal_id' => $this->sucursal->id, 'nombre' => 'Comida', 'orden' => 1, 'activo' => true]);
        $producto = Producto::create(['sucursal_id' => $this->sucursal->id, 'categoria_id' => $categoria->id, 'nombre' => 'Producto 1', 'precio' => 10000, 'activo' => true, 'disponible' => true]);

        \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesion->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesion->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
        ]);

        $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesion->token]))->assertStatus(200);

        // Crear pago con 3 intentos ya realizados
        $pedido = Pedido::first();
        $pago = Pago::create([
            'pedido_id' => $pedido->id,
            'sucursal_id' => $pedido->sucursal_id,
            'metodo' => 'Nequi',
            'monto' => $pedido->total,
            'estado' => 'FALLIDO',
            'nequi_telefono' => '3009999999',
            'nequi_correo' => 'test@nequi.com',
            'referencia' => 'NEQUI-LOCK',
            'intentos' => 3,
        ]);

        // Acceder a la página de pago
        $response = $this->get(route('cliente.pago', ['t' => $sesion->token]));
        $response->assertStatus(200);
        $response->assertViewHas('nequiBloqueado', true);

        // Intentar procesar pago con Nequi de nuevo debe fallar
        $responseProcesar = $this->post(route('cliente.pago.procesar', ['t' => $sesion->token]), [
            'metodo' => 'Nequi',
            'nequi_telefono' => '3009999999',
            'nequi_correo' => 'test@nequi.com',
        ]);
        $responseProcesar->assertSessionHas('error');
    }

    /** @test */
    public function test_auto_asignacion_mesero_y_domiciliario()
    {
        // 1. Caso Mesero (Local)
        // Crear 2 meseros, uno con 1 pedido activo, otro con 0
        $meseroCargado = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Mesero Cargado',
            'correo' => 'cargado@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'mesero',
            'activo' => true,
        ]);
        
        $meseroLibre = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Mesero Libre',
            'correo' => 'libre@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'mesero',
            'activo' => true,
        ]);

        $sesionDummy = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-dummy-auto',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Dummy',
            'activo' => true,
        ]);

        // Asignar pedidos al cargado
        Pedido::create([
            'sucursal_id' => $this->sucursal->id,
            'sesion_cliente_id' => $sesionDummy->id,
            'tipo' => 'local',
            'estado' => 'CREADO',
            'subtotal' => 1000,
            'total' => 1000,
            'mesero_id' => $meseroCargado->id,
        ]);

        $sesionLocal = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'mesa_id' => $this->mesa->id,
            'token' => 'token-auto-mesero',
            'tipo' => 'local',
            'nombre_cliente' => 'Cliente Auto Mesero',
            'activo' => true,
        ]);
        $this->mesa->ocupar();

        $categoria = Categoria::create(['sucursal_id' => $this->sucursal->id, 'nombre' => 'Comida', 'orden' => 1, 'activo' => true]);
        $producto = Producto::create(['sucursal_id' => $this->sucursal->id, 'categoria_id' => $categoria->id, 'nombre' => 'Producto 1', 'precio' => 10000, 'activo' => true, 'disponible' => true]);

        \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesionLocal->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesionLocal->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
        ]);

        $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesionLocal->token]))->assertStatus(200);

        $pedidoLocal = Pedido::where('sesion_cliente_id', $sesionLocal->id)->first();
        // Debe asignarse al mesero con menos carga (Mesero Libre)
        $this->assertEquals($meseroLibre->id, $pedidoLocal->mesero_id);

        // 2. Caso Domiciliario
        $domiciliarioUser = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Domi User',
            'correo' => 'domi@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'domiciliario',
            'activo' => true,
        ]);

        $perfilDomi = \App\Models\PerfilDomiciliario::create([
            'usuario_id' => $domiciliarioUser->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => 'disponible',
            'tiene_bloqueo' => false,
            'pedidos_hoy' => 0,
        ]);

        $sesionDomi = SesionCliente::create([
            'sucursal_id' => $this->sucursal->id,
            'token' => 'token-auto-domi',
            'tipo' => 'domicilio',
            'nombre_cliente' => 'Cliente Auto Domi',
            'direccion_cliente' => 'Calle 100',
            'activo' => true,
        ]);

        \App\Models\ItemCarrito::create([
            'sesion_cliente_id' => $sesionDomi->id,
            'producto_id' => $producto->id,
            'sucursal_id' => $sesionDomi->sucursal_id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => 10000,
            'cantidad' => 1,
            'subtotal' => 10000,
        ]);

        $this->postJson(route('cliente.pedido.confirmar', ['t' => $sesionDomi->token]))->assertStatus(200);

        $pedidoDomi = Pedido::where('sesion_cliente_id', $sesionDomi->id)->first();
        // Debe auto-asignarse el domiciliario disponible
        $this->assertEquals($perfilDomi->id, $pedidoDomi->perfil_domiciliario_id);
    }
}

