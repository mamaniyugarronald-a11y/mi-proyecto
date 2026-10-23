<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('solicitud_transferencia')->where('estado', 'cargando')->update([
            'estado' => 'transportando',
            'mensaje' => 'Transportando producto',
        ]);
    }

    public function down(): void
    {
        DB::table('solicitud_transferencia')->where('estado', 'transportando')->update([
            'estado' => 'cargando',
            'mensaje' => 'Cargando producto',
        ]);
    }
};
