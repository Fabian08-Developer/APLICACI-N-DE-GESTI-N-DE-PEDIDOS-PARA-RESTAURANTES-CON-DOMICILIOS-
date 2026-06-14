<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminQuickToolsTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean settings file if it exists
        $path = storage_path('app/global_settings.json');
        if (file_exists($path)) {
            unlink($path);
        }

        // Create super admin
        $this->superAdmin = User::create([
            'nombre' => 'Super Admin Test',
            'correo' => 'superadmin@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'super-admin',
            'activo' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $path = storage_path('app/global_settings.json');
        if (file_exists($path)) {
            unlink($path);
        }
        parent::tearDown();
    }

    /** @test */
    public function test_super_admin_can_update_global_variables()
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(\App\Livewire\SuperAdmin\Dashboard::class)
            ->assertSet('plataforma_nombre', 'Panel de Control Global')
            ->set('plataforma_nombre', 'Mi Cafetería Premium')
            ->set('soporte_correo', 'nuevosoporte@test.com')
            ->set('subida_limite', 25)
            ->set('registro_abierto', false)
            ->call('actualizarVariables')
            ->assertSet('showVariablesModal', false);

        // Verify it was saved to the JSON file
        $this->assertFileExists(storage_path('app/global_settings.json'));
        $settings = json_decode(file_get_contents(storage_path('app/global_settings.json')), true);
        $this->assertEquals('Mi Cafetería Premium', $settings['plataforma_nombre']);
        $this->assertEquals('nuevosoporte@test.com', $settings['soporte_correo']);
        $this->assertEquals(25, $settings['subida_limite']);
        $this->assertFalse($settings['registro_abierto']);
    }

    /** @test */
    public function test_super_admin_can_publish_and_disable_aviso_masivo()
    {
        $this->actingAs($this->superAdmin);

        // Create a couple of standard users to verify notification inbox propagation
        User::create([
            'nombre' => 'User One',
            'correo' => 'user1@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'mesero',
            'activo' => true,
        ]);

        User::create([
            'nombre' => 'User Two',
            'correo' => 'user2@test.com',
            'contrasena' => bcrypt('password'),
            'rol' => 'cocina',
            'activo' => true,
        ]);

        Livewire::test(\App\Livewire\SuperAdmin\Dashboard::class)
            ->set('aviso_titulo', 'Corte de Luz Programado')
            ->set('aviso_mensaje', 'Habrá mantenimiento del sistema de fluido eléctrico de 2am a 4am.')
            ->set('aviso_mostrar_banner', true)
            ->set('aviso_guardar_historial', true)
            ->call('publicarAviso')
            ->assertSet('aviso_activo', true)
            ->assertSet('showAvisoModal', false);

        // Verify settings saved
        $settings = json_decode(file_get_contents(storage_path('app/global_settings.json')), true);
        $this->assertTrue($settings['aviso_activo']);
        $this->assertTrue($settings['aviso_mostrar_banner']);
        $this->assertEquals('Corte de Luz Programado', $settings['aviso_titulo']);

        // Verify notifications table entries populated for standard users
        $notificacionesCount = DB::table('notificaciones')
            ->where('tipo', 'aviso')
            ->where('titulo', 'Corte de Luz Programado')
            ->count();
        // Super Admin + 2 users = 3 notifications
        $this->assertEquals(3, $notificacionesCount);

        // Test disabling notice
        Livewire::test(\App\Livewire\SuperAdmin\Dashboard::class)
            ->call('desactivarAviso')
            ->assertSet('aviso_activo', false);

        $settings = json_decode(file_get_contents(storage_path('app/global_settings.json')), true);
        $this->assertFalse($settings['aviso_activo']);
    }

    /** @test */
    public function test_super_admin_can_manage_version_history_and_maintenance()
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(\App\Livewire\SuperAdmin\Dashboard::class)
            ->set('mantenimiento_fecha', 'Lunes 1 de Junio - 03:00 AM')
            ->call('actualizarMantenimiento')
            ->assertSet('mantenimiento_fecha', 'Lunes 1 de Junio - 03:00 AM');

        // Verify maintenance updated in json
        $settings = json_decode(file_get_contents(storage_path('app/global_settings.json')), true);
        $this->assertEquals('Lunes 1 de Junio - 03:00 AM', $settings['mantenimiento_fecha']);

        // Add a new version
        Livewire::test(\App\Livewire\SuperAdmin\Dashboard::class)
            ->set('version_nueva_numero', 'v1.3.0')
            ->set('version_nueva_notas', 'Nuevas funciones de optimización y mejoras visuales.')
            ->call('agregarVersion')
            ->assertSet('version_actual', 'v1.3.0')
            ->assertSet('version_nueva_numero', '')
            ->assertSet('version_nueva_notas', '');

        // Verify version prepended
        $settings = json_decode(file_get_contents(storage_path('app/global_settings.json')), true);
        $this->assertEquals('v1.3.0', $settings['version_actual']);
        $this->assertEquals('v1.3.0', $settings['versiones_lista'][0]['version']);

        // Delete version
        Livewire::test(\App\Livewire\SuperAdmin\Dashboard::class)
            ->call('eliminarVersion', 0);

        $settings = json_decode(file_get_contents(storage_path('app/global_settings.json')), true);
        $this->assertNotEquals('v1.3.0', $settings['version_actual']);
    }
}
