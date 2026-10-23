<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductoModel;

$productos = ProductoModel::all();
$totalImgs = 18;
$i = 1;

foreach ($productos as $p) {
    if (!$p->imagen || !file_exists(public_path('img/productos/' . $p->imagen))) {
        $imgName = (($i - 1) % $totalImgs + 1) . '.jpg';
        $p->imagen = $imgName;
        $p->save();
        echo "Producto #{$p->id_producto} ({$p->nombre}) asignado a imagen: {$imgName}\n";
        $i++;
    }
}

echo "✓ Asignación completada.\n";
