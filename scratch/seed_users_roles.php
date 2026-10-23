<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RolModel;
use App\Models\UsuarioModel;

echo "=== CONFIGURANDO ROLES Y USUARIOS PARA CADA PERFIL ===\n\n";

// 1. Asegurar los 4 Roles
$roles = [
    1 => 'Administrador',
    2 => 'Vendedor',
    3 => 'Encargado de Almacén',
    4 => 'Encargado de Despacho'
];

foreach ($roles as $id => $nombre) {
    $r = RolModel::find($id);
    if ($r) {
        $r->nombre_rol = $nombre;
        $r->save();
    } else {
        RolModel::create([
            'id_rol' => $id,
            'nombre_rol' => $nombre
        ]);
    }
}

// 2. Asegurar Usuarios para cada Rol
$usuariosSeed = [
    [
        'usuario'  => 'admin',
        'nombre'   => 'Griselda',
        'apellido' => 'Rodriguez Flores',
        'email'    => 'admin@pebie.com',
        'password' => '1234',
        'id_rol'   => 1
    ],
    [
        'usuario'  => 'vendedor',
        'nombre'   => 'Ronald Adilson',
        'apellido' => 'Mamani Yugar',
        'email'    => 'vendedor@pebie.com',
        'password' => '1234',
        'id_rol'   => 2
    ],
    [
        'usuario'  => 'almacen',
        'nombre'   => 'Carlos',
        'apellido' => 'Almacenero PEBIE',
        'email'    => 'almacen@pebie.com',
        'password' => '1234',
        'id_rol'   => 3
    ],
    [
        'usuario'  => 'despacho',
        'nombre'   => 'David',
        'apellido' => 'Encargado Despacho',
        'email'    => 'despacho@pebie.com',
        'password' => '1234',
        'id_rol'   => 4
    ]
];

foreach ($usuariosSeed as $uData) {
    $u = UsuarioModel::where('usuario', $uData['usuario'])->first();
    if ($u) {
        $u->update($uData);
    } else {
        UsuarioModel::create($uData);
    }
}

echo "✓ Roles y Usuarios actualizados correctamente.\n";
