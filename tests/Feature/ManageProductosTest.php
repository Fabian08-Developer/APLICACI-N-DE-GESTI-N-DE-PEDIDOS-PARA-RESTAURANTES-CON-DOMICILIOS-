<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\VarianteProducto;
use App\Models\AdicionProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
use App\Exports\ProductosTemplateExport;
use App\Imports\ProductosImport;

class ManageProductosTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $sucursal;
    protected $empresa;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Scopes\TenantScope::setTenantId(null);

        // Create initial tenant hierarchy
        $this->empresa = Empresa::create([
            'nit' => '123456789-0',
            'nombre' => 'Empresa Test',
            'activo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Central',
            'slug' => 'sucursal-central',
            'activo' => true,
        ]);

        $this->user = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Gerente Test',
            'correo' => 'gerente@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'gerente',
            'activo' => true,
        ]);
    }

    /** @test */
    public function test_it_can_crud_products_rf_93()
    {
        $this->actingAs($this->user);

        // 1. Create product
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('nombre', 'Hamburguesa Especial')
            ->set('descripcion', 'Queso y tocineta')
            ->set('precio', 15000)
            ->set('activo', true)
            ->set('disponible', true)
            ->set('permite_notas', true)
            ->set('limite_minimo_adiciones', 0)
            ->set('limite_maximo_adiciones', 5)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('productos', [
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Hamburguesa Especial',
            'precio' => 15000,
            'activo' => true,
            'disponible' => true,
            'permite_notas' => true,
            'limite_minimo_adiciones' => 0,
            'limite_maximo_adiciones' => 5,
        ]);

        $producto = Producto::where('nombre', 'Hamburguesa Especial')->first();

        // 2. Edit product
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('edit', $producto->id)
            ->assertSet('nombre', 'Hamburguesa Especial')
            ->set('nombre', 'Hamburguesa Especial Doble')
            ->set('precio', 18000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'Hamburguesa Especial Doble',
            'precio' => 18000,
        ]);

        // 3. Delete product
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('delete', $producto->id)
            ->assertHasNoErrors();

        // Soft deletes are used in Producto model (SoftDeletes)
        $this->assertSoftDeleted('productos', [
            'id' => $producto->id
        ]);
    }

    /** @test */
    public function test_it_can_toggle_visibility_and_deactivate_products_rf_94_rf_95()
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Papas Fritas',
            'precio' => 5000,
            'activo' => true,
        ]);

        // Toggle visibility
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('toggleActivo', $producto->id)
            ->assertHasNoErrors();

        $this->assertFalse((bool) $producto->fresh()->activo);

        // Verify it is hidden from the menu scope
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);
        $productosVisibles = Producto::activoConCategoriaActiva()->get();
        $this->assertFalse($productosVisibles->contains($producto));

        // Toggle back to active
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('toggleActivo', $producto->id)
            ->assertHasNoErrors();

        $this->assertTrue((bool) $producto->fresh()->activo);
        $productosVisibles = Producto::activoConCategoriaActiva()->get();
        $this->assertTrue($productosVisibles->contains($producto));
    }

    /** @test */
    public function test_it_can_assign_offer_price_rf_96()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('nombre', 'Pizza Familiar')
            ->set('precio', 40000)
            ->set('precio_oferta', 30000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('productos', [
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Pizza Familiar',
            'precio' => 40000,
            'precio_oferta' => 30000,
        ]);
    }

    /** @test */
    public function test_it_filters_and_searches_products_rf_97()
    {
        $this->actingAs($this->user);

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Bebidas',
        ]);

        $prod1 = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Coca Cola 350ml',
            'precio' => 3500,
        ]);

        $prod2 = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Perro Caliente',
            'precio' => 8000,
        ]);

        // Search by name
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('search', 'Coca')
            ->assertViewHas('productos', function ($productos) use ($prod1, $prod2) {
                return $productos->contains($prod1) && !$productos->contains($prod2);
            });

        // Filter by category
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('filterCategoria', $categoria->id)
            ->assertViewHas('productos', function ($productos) use ($prod1, $prod2) {
                return $productos->contains($prod1) && !$productos->contains($prod2);
            });

        // Filter by no category
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('filterCategoria', 'sin_categoria')
            ->assertViewHas('productos', function ($productos) use ($prod1, $prod2) {
                return !$productos->contains($prod1) && $productos->contains($prod2);
            });
    }

    /** @test */
    public function test_it_exports_products_and_template_rf_98_rf_99()
    {
        Excel::fake();
        $this->actingAs($this->user);

        // Export products
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('export');

        Excel::assertDownloaded('productos.xlsx', function (ProductosExport $export) {
            return $export->collection()->isEmpty(); // No products yet in DB
        });

        // Export template
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('descargarPlantilla');

        Excel::assertDownloaded('plantilla_productos.xlsx', function (ProductosTemplateExport $export) {
            return count($export->array()) === 1;
        });
    }

    /** @test */
    public function test_it_handles_kitchen_availability_toggles_rf_100()
    {
        $cocinaUser = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Cocinero Test',
            'correo' => 'cocina@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'cocina',
            'activo' => true,
        ]);

        $this->actingAs($cocinaUser);
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        // Create a valid Sesion record for the cocina user to pass VerificarAutenticacion middleware
        \App\Models\Sesion::create([
            'usuario_id' => $cocinaUser->id,
            'sucursal_id' => $this->sucursal->id,
            'token' => 'test-kitchen-token',
            'activa' => true,
            'fecha_expiracion' => now()->addDays(1),
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Café Espresso',
            'precio' => 4000,
            'disponible' => true,
        ]);

        $variante = VarianteProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Leche',
            'obligatorio' => false,
            'opciones' => [
                ['nombre' => 'Entera', 'precio' => 0.00, 'tipo_impacto' => 'incremental', 'disponible' => true],
                ['nombre' => 'Deslactosada', 'precio' => 1000, 'tipo_impacto' => 'incremental', 'disponible' => true],
            ],
        ]);

        $adicion = AdicionProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Chispas de Chocolate',
            'precio' => 1500,
            'activo' => true,
        ]);

        // Toggle product availability - pass the session token as _st
        $this->post(route('cocina.disponibilidad.toggle-producto', $producto->id) . '?_st=test-kitchen-token')
            ->assertRedirect();
        $this->assertFalse((bool) $producto->fresh()->disponible);

        // Toggle variant option availability - pass the session token as _st
        $this->post(route('cocina.disponibilidad.toggle-variante', [$variante->id, 'Deslactosada']) . '?_st=test-kitchen-token')
            ->assertRedirect();
        $opciones = $variante->fresh()->opciones;
        $this->assertFalse($opciones[1]['disponible']); // Deslactosada should be false

        // Toggle addition availability - pass the session token as _st
        $this->post(route('cocina.disponibilidad.toggle-adicion', $adicion->id) . '?_st=test-kitchen-token')
            ->assertRedirect();
        $this->assertFalse((bool) $adicion->fresh()->activo);
    }

    /** @test */
    public function test_it_saves_permite_notas_toggle_rf_101()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('nombre', 'Jugo de Naranja')
            ->set('precio', 6000)
            ->set('permite_notas', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('productos', [
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Jugo de Naranja',
            'permite_notas' => false,
        ]);
    }

    /** @test */
    public function test_it_manages_variants_and_price_impacts_rf_102_rf_103()
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Malteada',
            'precio' => 10000,
        ]);

        // Add a variant group with fixed and incremental options
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('openVariantesModal', $producto->id)
            ->set('nuevaVarianteNombre', 'Tamaño')
            ->set('nuevaVarianteObligatorio', true)
            ->set('nuevaVarianteOpciones', [
                ['nombre' => 'Pequeño', 'precio' => 0.00, 'tipo_impacto' => 'incremental', 'disponible' => true],
                ['nombre' => 'Grande', 'precio' => 3000, 'tipo_impacto' => 'incremental', 'disponible' => true],
                ['nombre' => 'Familiar', 'precio' => 15000, 'tipo_impacto' => 'fijo', 'disponible' => true],
            ])
            ->call('saveVariante')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('variantes_producto', [
            'producto_id' => $producto->id,
            'nombre' => 'Tamaño',
            'obligatorio' => true,
        ]);

        $variante = VarianteProducto::where('producto_id', $producto->id)->first();
        $this->assertCount(3, $variante->opciones);
        $this->assertEquals('incremental', $variante->opciones[0]['tipo_impacto']);
        $this->assertEquals('fijo', $variante->opciones[2]['tipo_impacto']);
    }

    /** @test */
    public function test_it_manages_product_additions_rf_104()
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Sandwich Jamón',
            'precio' => 12000,
        ]);

        // Create addition and associate it with product
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->call('openAdicionesModalForProducto', $producto->id)
            ->set('nuevaAdicionNombre', 'Queso Cheddar')
            ->set('nuevaAdicionPrecio', 2000)
            ->call('saveNuevaAdicion')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('adiciones_producto', [
            'producto_id' => $producto->id,
            'nombre' => 'Queso Cheddar',
            'precio' => 2000,
            'activo' => true,
        ]);

        $disponibles = $producto->adiciones;
        $this->assertCount(1, $disponibles);
        $this->assertEquals('Queso Cheddar', $disponibles[0]->nombre);
    }

    /** @test */
    public function test_it_enforces_addition_limits_rf_105()
    {
        $this->actingAs($this->user);

        // 1. Min 0, Max 5 is valid
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('nombre', 'Tacos')
            ->set('precio', 12000)
            ->set('limite_minimo_adiciones', 1)
            ->set('limite_maximo_adiciones', 3)
            ->call('save')
            ->assertHasNoErrors();

        // 2. Max < Min should fail validation
        Livewire::test(\App\Livewire\Admin\Productos\ManageProductos::class)
            ->set('nombre', 'Enchiladas')
            ->set('precio', 14000)
            ->set('limite_minimo_adiciones', 3)
            ->set('limite_maximo_adiciones', 1)
            ->call('save')
            ->assertHasErrors(['limite_maximo_adiciones']);
    }
}
