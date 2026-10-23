<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_transferencia', function (Blueprint $table) {
            $table->bigIncrements('id_solicitud');
            $table->unsignedInteger('id_detalle_destino');
            $table->unsignedInteger('id_ubicacion_origen');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('cantidad');
            $table->string('estado', 20)->default('solicitada');
            $table->string('mensaje', 255)->nullable();
            $table->timestamp('creada_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_transferencia');
    }
};
