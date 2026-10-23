<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cliente_institucional', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_institucional', 'nombre_empresa')) {
                $table->string('nombre_empresa', 150)->nullable();
            }
            if (!Schema::hasColumn('cliente_institucional', 'nombre_empleado')) {
                $table->string('nombre_empleado', 150)->nullable();
            }
            if (!Schema::hasColumn('cliente_institucional', 'cargo_area')) {
                $table->string('cargo_area', 150)->nullable();
            }
        });

        DB::table('cliente_institucional')->whereNull('nombre_empresa')->update(['nombre_empresa' => DB::raw('razon_social')]);
        DB::table('cliente_institucional')->whereNull('nombre_empleado')->update(['nombre_empleado' => DB::raw('contacto_nombre')]);
    }

    public function down(): void
    {
        Schema::table('cliente_institucional', function (Blueprint $table) {
            foreach (['nombre_empresa', 'nombre_empleado', 'cargo_area'] as $column) {
                if (Schema::hasColumn('cliente_institucional', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
