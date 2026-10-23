<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // VARCHAR evita limitar los nuevos estados y conserva los datos existentes.
        DB::statement("ALTER TABLE venta MODIFY estado VARCHAR(30) NOT NULL DEFAULT 'Completado'");
        DB::statement("ALTER TABLE venta MODIFY tipo_venta VARCHAR(30) NOT NULL DEFAULT 'Venta Directa'");
        DB::table('venta')->where('tipo_venta', 'Pedido')->update(['tipo_venta' => 'Reserva']);
    }

    public function down(): void
    {
        DB::table('venta')->where('estado', 'Confirmado')->update(['estado' => 'Pendiente']);
        DB::table('venta')->where('tipo_venta', 'Reserva')->update(['tipo_venta' => 'Pedido']);
        DB::statement("ALTER TABLE venta MODIFY estado ENUM('Completado', 'Pendiente', 'Cancelado') NOT NULL DEFAULT 'Completado'");
        DB::statement("ALTER TABLE venta MODIFY tipo_venta ENUM('Venta Directa', 'Pedido') NOT NULL DEFAULT 'Venta Directa'");
    }
};
