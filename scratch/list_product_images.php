<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (Illuminate\Support\Facades\DB::table('producto as p')->join('tipo_producto as t','t.id_tipo_producto','=','p.id_tipo_producto')->select('p.id_producto','p.nombre','p.marca','t.nombre_tipo as categoria','p.imagen')->orderBy('p.id_producto')->get() as $p) echo json_encode($p, JSON_UNESCAPED_UNICODE)."\n";