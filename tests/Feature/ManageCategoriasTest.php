<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageCategoriasTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $sucursal;
    protected $empresa;

    protected function setUp(): void
    {
        parent::setUp();

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
    public function test_it_can_create_a_category_and_read_it_rf_88()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->set('nombre', 'Bebidas')
            ->set('descripcion', 'Refrescos y jugos')
            ->set('activo', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categorias', [
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Bebidas',
            'descripcion' => 'Refrescos y jugos',
            'activo' => true,
        ]);
    }

    /** @test */
    public function test_it_can_edit_a_category_rf_88()
    {
        $this->actingAs($this->user);

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Postres',
            'descripcion' => 'Dulces',
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->call('edit', $categoria->id)
            ->assertSet('nombre', 'Postres')
            ->assertSet('activo', true)
            ->set('nombre', 'Postres Especiales')
            ->set('activo', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nombre' => 'Postres Especiales',
            'activo' => false,
        ]);
    }

    /** @test */
    public function test_it_can_toggle_active_status_rf_92()
    {
        $this->actingAs($this->user);

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Bebidas',
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->call('toggleActivo', $categoria->id)
            ->assertHasNoErrors();

        $this->assertFalse((bool) $categoria->fresh()->activo);

        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->call('toggleActivo', $categoria->id)
            ->assertHasNoErrors();

        $this->assertTrue((bool) $categoria->fresh()->activo);
    }

    /** @test */
    public function test_it_prevents_deleting_a_category_with_active_products_rf_89()
    {
        $this->actingAs($this->user);

        $categoria = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Comida Rápida',
            'activo' => true,
        ]);

        $producto = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Hamburguesa',
            'precio' => 15000,
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->call('delete', $categoria->id)
            ->assertHasErrors(['general' => 'No se puede eliminar la categoría porque tiene productos asociados.']);

        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);

        // If product is soft-deleted, deleting the category should be allowed (since RF-89 states: soft-deleted ones are excluded)
        $producto->delete(); // Soft delete product

        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->call('delete', $categoria->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }

    /** @test */
    public function test_it_prevents_duplicate_category_names_case_insensitively_rf_90()
    {
        $this->actingAs($this->user);

        Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Ensaladas',
            'activo' => true,
        ]);

        // Test creating "ensaladas" case-insensitively
        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->set('nombre', 'ensaladas')
            ->call('save')
            ->assertHasErrors(['nombre']);

        // Test creating "EnSaLaDaS" case-insensitively
        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->set('nombre', 'EnSaLaDaS')
            ->call('save')
            ->assertHasErrors(['nombre']);

        // Test creating a duplicate name in another sucursal should be allowed (multi-tenant isolation)
        $otraSucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Norte',
            'slug' => 'sucursal-norte',
            'activo' => true,
        ]);

        $otroUser = User::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $otraSucursal->id,
            'nombre' => 'Gerente Norte',
            'correo' => 'gerente_norte@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'gerente',
            'activo' => true,
        ]);

        $this->actingAs($otroUser);
        // Force the static scope tenant override for current request scope
        \App\Scopes\TenantScope::setTenantId($otraSucursal->id);

        Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
            ->set('nombre', 'Ensaladas')
            ->call('save')
            ->assertHasNoErrors();
    }

    /** @test */
    public function test_it_enforces_regex_pattern_on_category_name_rf_91()
    {
        $this->actingAs($this->user);

        // Disallowed names with sequential/correlative numbers sueltos
        $badNames = ['Bebidas 1', 'Bebidas 2', 'Entradas 02', '1 Especiales', 'Platos 123'];
        foreach ($badNames as $name) {
            Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
                ->set('nombre', $name)
                ->call('save')
                ->assertHasErrors(['nombre' => 'regex']);
        }

        // Allowed names (either no numbers, or numbers part of a commercial description not sueltos)
        $goodNames = ['Bebidas', 'Entradas Especiales', 'Combo 2x1', 'Promociones'];
        foreach ($goodNames as $name) {
            Livewire::test(\App\Livewire\Admin\Categorias\ManageCategorias::class)
                ->set('nombre', $name)
                ->call('save')
                ->assertHasNoErrors();
        }
    }

    /** @test */
    public function test_it_hides_products_on_inactive_categories_rf_92()
    {
        $this->actingAs($this->user);

        $categoriaActiva = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Activa',
            'activo' => true,
        ]);

        $categoriaInactiva = Categoria::create([
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Inactiva',
            'activo' => false,
        ]);

        $producto1 = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoriaActiva->id,
            'nombre' => 'Producto 1',
            'precio' => 1000,
            'activo' => true,
        ]);

        $producto2 = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoriaInactiva->id,
            'nombre' => 'Producto 2',
            'precio' => 2000,
            'activo' => true,
        ]);

        $producto3 = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => null,
            'nombre' => 'Producto 3',
            'precio' => 3000,
            'activo' => true,
        ]);

        $producto4 = Producto::create([
            'sucursal_id' => $this->sucursal->id,
            'categoria_id' => $categoriaActiva->id,
            'nombre' => 'Producto 4',
            'precio' => 4000,
            'activo' => false, // Inactive product
        ]);

        // Force set tenant id for scoping in console/test environment if needed
        \App\Scopes\TenantScope::setTenantId($this->sucursal->id);

        $productosVisibles = Producto::activoConCategoriaActiva()->get();

        $this->assertTrue($productosVisibles->contains($producto1));
        $this->assertFalse($productosVisibles->contains($producto2)); // Category is inactive
        $this->assertTrue($productosVisibles->contains($producto3));  // Category is null, product is active
        $this->assertFalse($productosVisibles->contains($producto4)); // Product is inactive
    }
}
