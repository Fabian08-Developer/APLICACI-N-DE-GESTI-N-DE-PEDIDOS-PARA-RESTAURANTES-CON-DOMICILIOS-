<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = App\Models\User::with('roles')->get();
foreach ($users as $u) {
    $roles = $u->roles->pluck('name')->implode(', ');
    echo "ID: {$u->id} | Email: {$u->email} | empresa_id: {$u->empresa_id} | Roles: {$roles}\n";
}
