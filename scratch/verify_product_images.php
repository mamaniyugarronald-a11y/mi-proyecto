<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = Illuminate\Support\Facades\DB::table('producto')->whereNotNull('imagen')->get(['id_producto', 'imagen']);
$missing = [];
foreach ($rows as $row) {
    if (!is_file(public_path('img/productos/' . $row->imagen))) $missing[] = $row->id_producto;
}
echo 'Productos con imagen: ' . $rows->count() . PHP_EOL;
echo 'Archivos faltantes: ' . count($missing) . PHP_EOL;
if ($missing) echo implode(', ', $missing) . PHP_EOL;
