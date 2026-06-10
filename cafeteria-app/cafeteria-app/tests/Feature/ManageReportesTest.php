<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\SesionCliente;
use App\Models\Sesion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageReportesTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $sucursal;
    protected $administrador;
    protected $gerente;
    protected $mesero;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);

        // Set up tenant and branch
        $this->empresa = Empresa::create([
            'nit'    => '999888777-6',
            'nombre' => 'Cafeteria Test',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre'     => 'Sucursal Test',
            'slug'       => 'sucursal-test',
            'activo'     => true,
        ]);

        // Set up roles
        $this->administrador = User::create([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Admin Reportes',
            'correo'      => 'admin.reportes@test.com',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'administrador',
            'activo'      => true,
        ]);

        $this->gerente = User::create([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Gerente Reportes',
            'correo'      => 'gerente.reportes@test.com',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'gerente',
            'activo'      => true,
        ]);

        $this->mesero = User::create([
            'empresa_id'  => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Mesero Reportes',
            'correo'      => 'mesero.reportes@test.com',
            'contrasena'  => bcrypt('password'),
            'rol'         => 'mesero',
            'activo'      => true,
        ]);
    }

    /**
     * Helper to authenticate a user with a token.
     */
    protected function authenticateWithToken($user)
    {
        $token = 'test-token-' . $user->id . '-' . uniqid();
        
        Sesion::create([
            'usuario_id'       => $user->id,
            'sucursal_id'      => $this->sucursal->id,
            'token'            => $token,
            'activa'           => true,
            'fecha_expiracion' => now()->addDays(7),
        ]);

        return $token;
    }

    /** @test */
    public function test_only_admin_and_gerente_can_access_sales_reports()
    {
        // Unauthenticated request without token -> returns 200 with the token bridge view
        $response = $this->get(route('admin.reportes'));
        $response->assertStatus(200);
        $response->assertViewIs('partials.token-bridge');

        // Unauthenticated request with invalid/expired token -> redirects to login
        $response = $this->get(route('admin.reportes', ['_st' => 'invalid-token']));
        $response->assertRedirect(route('login'));

        // Authenticated as Mesero -> 403 Forbidden
        $tokenMesero = $this->authenticateWithToken($this->mesero);
        $response = $this->get(route('admin.reportes', ['_st' => $tokenMesero]));
        $response->assertStatus(403);

        // Authenticated as Administrador -> 200 OK
        $tokenAdmin = $this->authenticateWithToken($this->administrador);
        $response = $this->get(route('admin.reportes', ['_st' => $tokenAdmin]));
        $response->assertStatus(200);

        // Authenticated as Gerente -> 200 OK
        $tokenGerente = $this->authenticateWithToken($this->gerente);
        $response = $this->get(route('admin.reportes', ['_st' => $tokenGerente]));
        $response->assertStatus(200);
    }

    /** @test */
    public function test_kpi_and_data_calculations_rf124_rf125_rf126()
    {
        $this->actingAs($this->administrador);

        // Create categories and products
        $categoriaCafes = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Cafés',
            'activo'      => true,
        ]);
        
        $categoriaPostres = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Postres',
            'activo'      => true,
        ]);

        $espresso = Producto::create([
            'sucursal_id'  => $this->sucursal->id,
            'categoria_id' => $categoriaCafes->id,
            'nombre'       => 'Espresso',
            'precio'       => 5000,
            'activo'       => true,
        ]);

        $torta = Producto::create([
            'sucursal_id'  => $this->sucursal->id,
            'categoria_id' => $categoriaPostres->id,
            'nombre'       => 'Torta Chocolate',
            'precio'       => 8000,
            'activo'       => true,
        ]);

        $sesion = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'mesa',
            'nombre_cliente'=> 'Mesa 1',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        // Create a few sales today
        $pedido1 = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'mesa',
            'estado'            => 'entregado',
            'metodo_pago'       => 'efectivo',
            'subtotal'          => 18000,
            'total'             => 18000,
            'creado_en'         => Carbon::now(),
        ]);

        DetallePedido::create([
            'pedido_id'       => $pedido1->id,
            'producto_id'     => $espresso->id,
            'sucursal_id'     => $this->sucursal->id,
            'nombre_producto' => 'Espresso',
            'precio_unitario' => 5000,
            'cantidad'        => 2,
            'subtotal'        => 10000,
        ]);

        DetallePedido::create([
            'pedido_id'       => $pedido1->id,
            'producto_id'     => $torta->id,
            'sucursal_id'     => $this->sucursal->id,
            'nombre_producto' => 'Torta Chocolate',
            'precio_unitario' => 8000,
            'cantidad'        => 1,
            'subtotal'        => 8000,
        ]);

        // Cancelled order (should be ignored)
        $pedidoCancelado = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'mesa',
            'estado'            => 'cancelado',
            'metodo_pago'       => 'nequi',
            'subtotal'          => 5000,
            'total'             => 5000,
            'creado_en'         => Carbon::now(),
        ]);

        DetallePedido::create([
            'pedido_id'       => $pedidoCancelado->id,
            'producto_id'     => $espresso->id,
            'sucursal_id'     => $this->sucursal->id,
            'nombre_producto' => 'Espresso',
            'precio_unitario' => 5000,
            'cantidad'        => 1,
            'subtotal'        => 5000,
        ]);

        // Test Livewire component state and calculations
        Livewire::test(\App\Livewire\Admin\Reportes\ManageReportes::class)
            ->assertViewHas('currentMetrics', function ($metrics) {
                return $metrics['ventasTotales'] == 18000 &&
                       $metrics['totalPedidos'] == 1 &&
                       $metrics['ticketPromedio'] == 18000;
            })
            ->assertViewHas('categoriasChart', function ($categories) {
                $cafes = $categories->where('nombre', 'Cafés')->first();
                $postres = $categories->where('nombre', 'Postres')->first();
                return $cafes->total == 10000 && $postres->total == 8000;
            });
    }

    /** @test */
    public function test_date_filters_rf127()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 12, 0, 0));
        $this->actingAs($this->administrador);

        // Create categories/products
        $cat = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Bebidas',
            'activo'      => true,
        ]);

        $prod = Producto::create([
            'sucursal_id'  => $this->sucursal->id,
            'categoria_id' => $cat->id,
            'nombre'       => 'Jugo Natural',
            'precio'       => 4000,
            'activo'       => true,
        ]);

        $sesion = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'mesa',
            'nombre_cliente'=> 'Mesa 1',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        // Order today
        $pedidoHoy = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'mesa',
            'estado'            => 'entregado',
            'metodo_pago'       => 'efectivo',
            'subtotal'          => 4000,
            'total'             => 4000,
        ]);
        $pedidoHoy->creado_en = Carbon::today()->hour(10);
        $pedidoHoy->save();

        DetallePedido::create([
            'pedido_id'       => $pedidoHoy->id,
            'producto_id'     => $prod->id,
            'sucursal_id'     => $this->sucursal->id,
            'nombre_producto' => 'Jugo Natural',
            'precio_unitario' => 4000,
            'cantidad'        => 1,
            'subtotal'        => 4000,
        ]);

        // Order 10 days ago (belongs to 'mes' but not 'semana' or 'hoy')
        $pedidoMes = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'mesa',
            'estado'            => 'entregado',
            'metodo_pago'       => 'efectivo',
            'subtotal'          => 12000,
            'total'             => 12000,
        ]);
        $pedidoMes->creado_en = Carbon::today()->subDays(2);
        $pedidoMes->save();

        DetallePedido::create([
            'pedido_id'       => $pedidoMes->id,
            'producto_id'     => $prod->id,
            'sucursal_id'     => $this->sucursal->id,
            'nombre_producto' => 'Jugo Natural',
            'precio_unitario' => 4000,
            'cantidad'        => 3,
            'subtotal'        => 12000,
        ]);

        // 1. Test 'hoy' filter
        Livewire::withQueryParams(['period' => 'hoy'])
            ->test(\App\Livewire\Admin\Reportes\ManageReportes::class)
            ->assertViewHas('currentMetrics', function ($metrics) {
                return $metrics['ventasTotales'] == 4000;
            });

        // 2. Test 'mes' filter
        Livewire::withQueryParams(['period' => 'mes'])
            ->test(\App\Livewire\Admin\Reportes\ManageReportes::class)
            ->assertViewHas('currentMetrics', function ($metrics) {
                return $metrics['ventasTotales'] == 16000;
            });

        // 3. Test 'personalizado' filter
        Livewire::withQueryParams([
            'period' => 'personalizado',
            'start'  => Carbon::today()->subDays(4)->format('Y-m-d'),
            'end'    => Carbon::today()->subDays(1)->format('Y-m-d')
        ])
            ->test(\App\Livewire\Admin\Reportes\ManageReportes::class)
            ->assertViewHas('currentMetrics', function ($metrics) {
                return $metrics['ventasTotales'] == 12000;
            });
    }

    /** @test */
    public function test_additional_filters_rf128_rf129_rf130()
    {
        $this->actingAs($this->administrador);

        // Set up categories and products
        $catCafes = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Cafés',
            'activo'      => true,
        ]);
        
        $catPostres = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre'      => 'Postres',
            'activo'      => true,
        ]);

        $espresso = Producto::create([
            'sucursal_id'  => $this->sucursal->id,
            'categoria_id' => $catCafes->id,
            'nombre'       => 'Espresso',
            'precio'       => 5000,
            'activo'       => true,
        ]);

        $torta = Producto::create([
            'sucursal_id'  => $this->sucursal->id,
            'categoria_id' => $catPostres->id,
            'nombre'       => 'Torta',
            'precio'       => 8000,
            'activo'       => true,
        ]);

        $sesion = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'mesa',
            'nombre_cliente'=> 'Mesa 1',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);

        // Order 1: Espresso paid with NEQUI
        $pedido1 = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'mesa',
            'estado'            => 'entregado',
            'metodo_pago'       => 'NEQUI',
            'subtotal'          => 5000,
            'total'             => 5000,
            'creado_en'         => Carbon::now(),
        ]);
        DetallePedido::create([
            'pedido_id'       => $pedido1->id,
            'producto_id'     => $espresso->id,
            'sucursal_id'     => $this->sucursal->id,
            'nombre_producto' => 'Espresso',
            'precio_unitario' => 5000,
            'cantidad'        => 1,
            'subtotal'        => 5000,
        ]);

        // Order 2: Torta paid with EFECTIVO
        $pedido2 = Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'mesa',
            'estado'            => 'entregado',
            'metodo_pago'       => 'efectivo',
            'subtotal'          => 8000,
            'total'             => 8000,
            'creado_en'         => Carbon::now(),
        ]);
        DetallePedido::create([
            'pedido_id'       => $pedido2->id,
            'producto_id'     => $torta->id,
            'sucursal_id'     => $this->sucursal->id,
            'nombre_producto' => 'Torta',
            'precio_unitario' => 8000,
            'cantidad'        => 1,
            'subtotal'        => 8000,
        ]);

        // 1. Test Filter by Category (RF-128)
        Livewire::withQueryParams(['categorias' => [$catCafes->id]])
            ->test(\App\Livewire\Admin\Reportes\ManageReportes::class)
            ->assertViewHas('currentMetrics', function ($metrics) {
                return $metrics['ventasTotales'] == 5000;
            });

        // 2. Test Filter by Payment Method (RF-129) - Case-insensitive match check
        Livewire::withQueryParams(['metodos_pago' => ['nequi']]) // lowercase input should match uppercase DB
            ->test(\App\Livewire\Admin\Reportes\ManageReportes::class)
            ->assertViewHas('currentMetrics', function ($metrics) {
                return $metrics['ventasTotales'] == 5000;
            });

        // 3. Test Filter by Top Products (RF-130)
        Livewire::withQueryParams(['productos_top' => ['Torta']])
            ->test(\App\Livewire\Admin\Reportes\ManageReportes::class)
            ->assertViewHas('currentMetrics', function ($metrics) {
                return $metrics['ventasTotales'] == 8000;
            });
    }

    /** @test */
    public function test_pdf_and_excel_export_endpoints_rf131()
    {
        $token = $this->authenticateWithToken($this->administrador);

        // Create a simple record
        $sesion = SesionCliente::create([
            'sucursal_id'   => $this->sucursal->id,
            'tipo'          => 'mesa',
            'nombre_cliente'=> 'Mesa 1',
            'token'         => \Illuminate\Support\Str::uuid(),
            'activa'        => true,
        ]);
        Pedido::create([
            'sucursal_id'       => $this->sucursal->id,
            'sesion_cliente_id' => $sesion->id,
            'tipo'              => 'mesa',
            'estado'            => 'entregado',
            'metodo_pago'       => 'efectivo',
            'subtotal'          => 2000,
            'total'             => 2000,
            'creado_en'         => Carbon::now(),
        ]);

        // 1. Export PDF
        $response = $this->get(route('admin.reportes.exportar', ['format' => 'pdf', '_st' => $token]));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        // 2. Export Excel
        $response = $this->get(route('admin.reportes.exportar', ['format' => 'excel', '_st' => $token]));
        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));

        // 3. Export CSV
        $response = $this->get(route('admin.reportes.exportar', ['format' => 'csv', '_st' => $token]));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }
}
