<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\PerfilDomiciliario;
use App\Models\LiquidacionDomiciliario;
use App\Models\ZonaCobertura;
use App\Models\Pedido;
use App\Models\SesionCliente;
use App\Mail\ComprobanteLiquidacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ManageDomiciliariosTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $sucursal;
    protected $administrador;
    protected $zona;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);

        $this->empresa = Empresa::create([
            'nit'    => '999888777-0',
            'nombre' => 'Empresa Dom Test',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre'     => 'Sucursal Dom',
            'slug'       => 'sucursal-dom',
            'activo'     => true,
        ]);

        $this->administrador = User::create([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Admin Dom',
            'correo'      => 'admin.dom@test.com',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'administrador',
            'activo'      => true,
        ]);

        $this->zona = ZonaCobertura::create([
            'sucursal_id'    => $this->sucursal->id,
            'nombre'         => 'Zona Norte',
            'costo_envio'    => 3000,
            'tiempo_estimado'=> 30,
            'activo'         => true,
        ]);
    }

    protected function makeDomiciliario(array $attrs = []): PerfilDomiciliario
    {
        $user = User::create(array_merge([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Dom Test ' . rand(1, 999),
            'correo'      => 'dom' . rand(1,9999) . '@test.com',
            'telefono'    => '3001234567',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'domiciliario',
            'activo'      => true,
        ], $attrs['user'] ?? []));

        return PerfilDomiciliario::create(array_merge([
            'usuario_id'        => $user->id,
            'sucursal_id'       => $this->sucursal->id,
            'zona_id'           => $this->zona->id,
            'tipo_vehiculo'     => 'moto',
            'estado'            => 'disponible',
            'efectivo_pendiente'=> 0,
            'limite_efectivo'   => 200000,
            'calificacion'      => 4.8,
            'pedidos_hoy'       => 0,
        ], $attrs['perfil'] ?? []));
    }

    protected function makePedidoDomicilio(): Pedido
    {
        $sesion = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'domicilio',
            'nombre_cliente'=> 'Cliente Test',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        return Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'domicilio',
            'estado'            => 'PENDIENTE',
            'zona_id'           => $this->zona->id,
            'total'             => 30000,
            'subtotal'          => 27000,
            'costo_envio'       => 3000,
        ]);
    }

    // ── RF-135: Búsqueda por nombre y teléfono ──────────────────────────────

    /** @test */
    public function test_busqueda_por_nombre_filtra_domiciliarios_rf135()
    {
        $this->actingAs($this->administrador);

        $dom1 = $this->makeDomiciliario(['user' => ['nombre' => 'Carlos Domínguez', 'correo' => 'carlos@test.com']]);
        $dom2 = $this->makeDomiciliario(['user' => ['nombre' => 'Pedro Ramírez', 'correo' => 'pedro@test.com']]);

        Livewire::test(\App\Livewire\Admin\Domiciliarios\ManageDomiciliarios::class)
            ->set('busqueda', 'Carlos')
            ->assertViewHas('domiciliarios', function ($doms) use ($dom1, $dom2) {
                return $doms->contains('id', $dom1->id)
                    && !$doms->contains('id', $dom2->id);
            });
    }

    /** @test */
    public function test_busqueda_por_telefono_filtra_domiciliarios_rf135()
    {
        $this->actingAs($this->administrador);

        $dom1 = $this->makeDomiciliario(['user' => ['nombre' => 'Dom Uno', 'correo' => 'dom1@test.com', 'telefono' => '3001111111']]);
        $dom2 = $this->makeDomiciliario(['user' => ['nombre' => 'Dom Dos', 'correo' => 'dom2@test.com', 'telefono' => '3002222222']]);

        Livewire::test(\App\Livewire\Admin\Domiciliarios\ManageDomiciliarios::class)
            ->set('busqueda', '3001111111')
            ->assertViewHas('domiciliarios', function ($doms) use ($dom1, $dom2) {
                return $doms->contains('id', $dom1->id)
                    && !$doms->contains('id', $dom2->id);
            });
    }

    // ── RF-136: Filtro por estado ────────────────────────────────────────────

    /** @test */
    public function test_filtro_por_estado_muestra_solo_ese_estado_rf136()
    {
        $this->actingAs($this->administrador);

        $disponible = $this->makeDomiciliario(['perfil' => ['estado' => 'disponible']]);
        $enRuta     = $this->makeDomiciliario(['perfil' => ['estado' => 'en_ruta']]);

        Livewire::test(\App\Livewire\Admin\Domiciliarios\ManageDomiciliarios::class)
            ->call('setFiltroEstado', 'disponible')
            ->assertViewHas('domiciliarios', function ($doms) use ($disponible, $enRuta) {
                return $doms->contains('id', $disponible->id)
                    && !$doms->contains('id', $enRuta->id);
            });
    }

    // ── RF-140: Bloqueo de asignación ────────────────────────────────────────

    /** @test */
    public function test_tiene_bloqueo_si_efectivo_supera_limite_rf140()
    {
        $dom = $this->makeDomiciliario(['perfil' => [
            'efectivo_pendiente' => 250000,
            'limite_efectivo'    => 200000,
        ]]);

        $this->assertTrue($dom->tiene_bloqueo);
    }

    /** @test */
    public function test_tiene_bloqueo_si_tiene_liquidacion_pendiente_rf140()
    {
        $dom = $this->makeDomiciliario(['perfil' => [
            'efectivo_pendiente' => 50000,
            'limite_efectivo'    => 200000,
        ]]);

        LiquidacionDomiciliario::create([
            'perfil_domiciliario_id' => $dom->id,
            'sucursal_id'            => $this->sucursal->id,
            'aprobado_por'           => $this->administrador->id,
            'monto'                  => 50000,
            'estado'                 => 'pendiente',
            'liquidado_en'           => now(),
        ]);

        $this->assertTrue($dom->fresh()->tiene_bloqueo);
    }

    /** @test */
    public function test_sin_bloqueo_cuando_efectivo_es_cero_y_sin_pendientes_rf140()
    {
        $dom = $this->makeDomiciliario(['perfil' => [
            'efectivo_pendiente' => 0,
            'limite_efectivo'    => 200000,
        ]]);

        $this->assertFalse($dom->tiene_bloqueo);
    }

    // ── RF-138: Liquidación de caja ──────────────────────────────────────────

    /** @test */
    public function test_liquidacion_resetea_efectivo_y_crea_registro_rf138()
    {
        Mail::fake();
        $this->actingAs($this->administrador);

        $dom = $this->makeDomiciliario(['perfil' => ['efectivo_pendiente' => 75000]]);

        Livewire::test(\App\Livewire\Admin\Domiciliarios\ManageDomiciliarios::class)
            ->call('iniciarLiquidacion', $dom->id)
            ->assertSet('liquidandoId', $dom->id)
            ->assertSet('montoLiquidacion', 75000.0)
            ->call('confirmarLiquidacion');

        // Efectivo reseteado a 0
        $this->assertEquals(0, (float) $dom->fresh()->efectivo_pendiente);

        // Registro creado
        $this->assertDatabaseHas('liquidaciones_domiciliario', [
            'perfil_domiciliario_id' => $dom->id,
            'monto'                  => 75000,
            'estado'                 => 'completada',
        ]);
    }

    // ── RF-139: Envío de email ────────────────────────────────────────────────

    /** @test */
    public function test_comprobante_email_enviado_al_liquidar_rf139()
    {
        Mail::fake();
        $this->actingAs($this->administrador);

        $dom = $this->makeDomiciliario(['perfil' => ['efectivo_pendiente' => 30000]]);

        Livewire::test(\App\Livewire\Admin\Domiciliarios\ManageDomiciliarios::class)
            ->call('iniciarLiquidacion', $dom->id)
            ->call('confirmarLiquidacion');

        Mail::assertSent(ComprobanteLiquidacion::class);
    }

    // ── RF-188: Auto-asignación inteligente ─────────────────────────────────

    /** @test */
    public function test_auto_asignar_selecciona_disponible_sin_bloqueo_rf188()
    {
        $this->actingAs($this->administrador);

        // Dom disponible con bloqueo
        $bloqueado = $this->makeDomiciliario(['perfil' => [
            'estado'             => 'disponible',
            'efectivo_pendiente' => 250000,
            'limite_efectivo'    => 200000,
        ]]);

        // Dom disponible sin bloqueo
        $libre = $this->makeDomiciliario(['perfil' => [
            'estado'             => 'disponible',
            'efectivo_pendiente' => 0,
            'pedidos_hoy'        => 0,
        ]]);

        $pedido = $this->makePedidoDomicilio();

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->call('autoAsignar', $pedido->id);

        $this->assertEquals($libre->id, $pedido->fresh()->perfil_domiciliario_id);
        $this->assertNotEquals($bloqueado->id, $pedido->fresh()->perfil_domiciliario_id);
    }

    /** @test */
    public function test_auto_asignar_prioriza_menor_carga_rf188()
    {
        $this->actingAs($this->administrador);

        $cargado = $this->makeDomiciliario(['perfil' => ['estado' => 'disponible', 'pedidos_hoy' => 8]]);
        $fresco  = $this->makeDomiciliario(['perfil' => ['estado' => 'disponible', 'pedidos_hoy' => 1]]);

        $pedido = $this->makePedidoDomicilio();

        Livewire::test(\App\Livewire\Admin\Pedidos\ManagePedidos::class)
            ->call('autoAsignar', $pedido->id);

        $this->assertEquals($fresco->id, $pedido->fresh()->perfil_domiciliario_id);
    }
}
