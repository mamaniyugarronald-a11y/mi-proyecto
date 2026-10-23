<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RolModel;
use App\Models\UsuarioModel;

echo "=== ROLES EN LA BASE DE DATOS ===\n";
$roles = RolModel::all();
foreach ($roles as $r) {
    echo "ID: " . $r->id_rol . " | Nombre: " . $r->nombre_rol . "\n";
}

echo "\n=== USUARIOS EN LA BASE DE DATOS ===\n";
$usuarios = UsuarioModel::with('rol')->get();
foreach ($usuarios as $u) {
    echo "ID: {$u->id_usuario} | Usuario: {$u->usuario} | Password: {$u->password} | Rol: " . ($u->rol->nombre_rol ?? 'Sin rol') . " (ID Rol: {$u->id_rol}) | Nombre: {$u->nombre} {$u->apellido} | Email: {$u->email}\n";
}
