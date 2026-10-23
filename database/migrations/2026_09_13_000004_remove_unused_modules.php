<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Módulos retirados del alcance: importaciones/aduanas, CRM y arqueo de caja.
        Schema::dropIfExists('seguimiento_importacion');
        Schema::dropIfExists('detalle_importacion');
        Schema::dropIfExists('importacion');
        Schema::dropIfExists('consulta_cliente');
        Schema::dropIfExists('cierre_caja');

        // Tablas estándar que esta aplicación no utiliza: trabaja con `usuario`.
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('users');
    }

    public function down(): void
    {
        // La eliminación de módulos se considera definitiva; los esquemas históricos
        // permanecen documentados en las migraciones anteriores.
    }
};
