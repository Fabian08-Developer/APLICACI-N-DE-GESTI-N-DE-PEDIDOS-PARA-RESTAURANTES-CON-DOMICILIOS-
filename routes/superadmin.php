<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use App\Livewire\SuperAdmin\ManageRequests;
use App\Livewire\SuperAdmin\ManageTenants;
use App\Livewire\SuperAdmin\ManageGlobalUsers;
use App\Livewire\SuperAdmin\ManageTrash;

Route::group(['prefix' => 'master', 'middleware' => ['auth', \App\Http\Middleware\EnsureIsSuperAdmin::class]], function() {
    Route::get('/dashboard', SuperAdminDashboard::class)->name('super-admin.dashboard');
    Route::get('/solicitudes', ManageRequests::class)->name('super-admin.requests');
    Route::get('/tenants', ManageTenants::class)->name('super-admin.tenants');
    Route::get('/usuarios', ManageGlobalUsers::class)->name('super-admin.users');
    Route::get('/papelera', ManageTrash::class)->name('super-admin.trash');
});
