<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('venta', function (Blueprint $table) {
            if (!Schema::hasColumn('venta', 'es_venta_mayor')) $table->boolean('es_venta_mayor')->default(false)->after('tipo_cliente');
            if (!Schema::hasColumn('venta', 'promocion')) $table->enum('promocion', ['Ninguna', 'Transporte', 'Precio'])->default('Ninguna')->after('es_venta_mayor');
        });
    }
    public function down(): void
    {
        Schema::table('venta', function (Blueprint $table) {
            $table->dropColumn(['es_venta_mayor', 'promocion']);
        });
    }
};
