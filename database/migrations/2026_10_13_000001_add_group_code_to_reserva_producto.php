<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reserva_producto', function (Blueprint $table) {
            $table->string('grupo_reserva', 40)->nullable()->index()->after('codigo_reserva');
        });
    }

    public function down(): void
    {
        Schema::table('reserva_producto', function (Blueprint $table) {
            $table->dropIndex(['grupo_reserva']);
            $table->dropColumn('grupo_reserva');
        });
    }
};
