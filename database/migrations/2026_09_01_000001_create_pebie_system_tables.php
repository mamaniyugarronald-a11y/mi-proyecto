<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. ROL
        if (!Schema::hasTable('rol')) {
            Schema::create('rol', function (Blueprint $table) {
                $table->integer('id_rol')->autoIncrement();
                $table->string('nombre_rol', 100);
            });
        }

        // 2. USUARIO
        if (!Schema::hasTable('usuario')) {
            Schema::create('usuario', function (Blueprint $table) {
                $table->integer('id_usuario')->autoIncrement();
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('usuario', 50)->unique();
                $table->string('email', 150)->unique();
                $table->string('password', 255);
                $table->integer('id_rol');
            });
        }

        // 3. HISTORIAL_SESION
        if (!Schema::hasTable('historial_sesion')) {
            Schema::create('historial_sesion', function (Blueprint $table) {
                $table->integer('id_historial')->autoIncrement();
                $table->integer('id_usuario');
                $table->dateTime('fecha_hora')->useCurrent();
            });
        }

        // 4. TIPO_PRODUCTO
        if (!Schema::hasTable('tipo_producto')) {
            Schema::create('tipo_producto', function (Blueprint $table) {
                $table->integer('id_tipo_producto')->autoIncrement();
                $table->string('nombre_tipo', 100);
                $table->string('descripcion', 255)->nullable();
            });
        }

        // 5. PRODUCTO
        if (!Schema::hasTable('producto')) {
            Schema::create('producto', function (Blueprint $table) {
                $table->integer('id_producto')->autoIncrement();
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->string('marca', 100)->nullable();
                $table->decimal('precio_menor', 10, 2);
                $table->decimal('precio_mayor', 10, 2);
                $table->integer('cantidad_min_mayor')->default(12);
                $table->integer('stock')->default(0);
                $table->integer('id_tipo_producto')->nullable();
                $table->tinyInteger('estado')->default(1);
            });
        }

        // 6. UBICACION
        if (!Schema::hasTable('ubicacion')) {
            Schema::create('ubicacion', function (Blueprint $table) {
                $table->integer('id_ubicacion')->autoIncrement();
                $table->string('nombre_ubicacion', 150);
                $table->string('direccion', 255)->nullable();
            });
        }

        // 7. DETALLE_UBICACION
        if (!Schema::hasTable('detalle_ubicacion')) {
            Schema::create('detalle_ubicacion', function (Blueprint $table) {
                $table->integer('id_detalle_ubicacion')->autoIncrement();
                $table->integer('id_ubicacion');
                $table->integer('id_producto');
                $table->integer('cantidad')->default(0);
            });
        }

        // 8. PROVEEDOR
        if (!Schema::hasTable('proveedor')) {
            Schema::create('proveedor', function (Blueprint $table) {
                $table->integer('id_proveedor')->autoIncrement();
                $table->string('nombre_empresa', 150);
                $table->string('contacto', 100)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->string('pais', 100)->nullable();
                $table->tinyInteger('estado')->default(1);
            });
        }

        // 9. COMPRA
        if (!Schema::hasTable('compra')) {
            Schema::create('compra', function (Blueprint $table) {
                $table->integer('id_compra')->autoIncrement();
                $table->integer('id_proveedor');
                $table->integer('id_usuario');
                $table->dateTime('fecha')->useCurrent();
                $table->string('nro_comprobante', 50)->nullable();
            });
        }

        // 10. DETALLE_COMPRA
        if (!Schema::hasTable('detalle_compra')) {
            Schema::create('detalle_compra', function (Blueprint $table) {
                $table->integer('id_detalle_compra')->autoIncrement();
                $table->integer('id_compra');
                $table->integer('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_compra', 10, 2);
            });
        }

        // 11. CLIENTE
        if (!Schema::hasTable('cliente')) {
            Schema::create('cliente', function (Blueprint $table) {
                $table->integer('id_cliente')->autoIncrement();
                $table->enum('tipo_registro', ['Unipersonal', 'Institucional']);
                $table->string('telefono', 20)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->dateTime('fecha_registro')->useCurrent();
            });
        }

        // 12. CLIENTE_UNIPERSONAL
        if (!Schema::hasTable('cliente_unipersonal')) {
            Schema::create('cliente_unipersonal', function (Blueprint $table) {
                $table->integer('id_cliente')->primary();
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('ci', 50)->unique();
            });
        }

        // 13. CLIENTE_INSTITUCIONAL
        if (!Schema::hasTable('cliente_institucional')) {
            Schema::create('cliente_institucional', function (Blueprint $table) {
                $table->integer('id_cliente')->primary();
                $table->string('razon_social', 150);
                $table->string('nit', 50)->unique();
                $table->string('contacto_nombre', 150)->nullable();
            });
        }

        // 14. VENTA
        if (!Schema::hasTable('venta')) {
            Schema::create('venta', function (Blueprint $table) {
                $table->integer('id_venta')->autoIncrement();
                $table->integer('id_cliente');
                $table->integer('id_usuario');
                $table->dateTime('fecha')->useCurrent();
                $table->enum('tipo_comprobante', ['Nota', 'Factura'])->default('Nota');
                $table->enum('tipo_cliente', ['Menor', 'Mayor'])->default('Menor');
                $table->enum('estado', ['Completado', 'Pendiente', 'Cancelado'])->default('Completado');
                $table->enum('tipo_venta', ['Venta Directa', 'Pedido'])->default('Venta Directa');
            });
        }

        // 15. DETALLE_VENTA
        if (!Schema::hasTable('detalle_venta')) {
            Schema::create('detalle_venta', function (Blueprint $table) {
                $table->integer('id_detalle_venta')->autoIncrement();
                $table->integer('id_venta');
                $table->integer('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_aplicado', 10, 2);
            });
        }

        // 16. DETALLE_PEDIDO
        if (!Schema::hasTable('detalle_pedido')) {
            Schema::create('detalle_pedido', function (Blueprint $table) {
                $table->integer('id_detalle_pedido')->autoIncrement();
                $table->integer('id_venta');
                $table->integer('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_estimado', 10, 2);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('detalle_venta');
        Schema::dropIfExists('venta');
        Schema::dropIfExists('cliente_institucional');
        Schema::dropIfExists('cliente_unipersonal');
        Schema::dropIfExists('cliente');
        Schema::dropIfExists('detalle_compra');
        Schema::dropIfExists('compra');
        Schema::dropIfExists('proveedor');
        Schema::dropIfExists('detalle_ubicacion');
        Schema::dropIfExists('ubicacion');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('tipo_producto');
        Schema::dropIfExists('historial_sesion');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('rol');
    }
};
