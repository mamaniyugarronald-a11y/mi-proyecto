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
        // 1. Ampliar tabla PRODUCTO
        if (Schema::hasTable('producto')) {
            Schema::table('producto', function (Blueprint $table) {
                if (!Schema::hasColumn('producto', 'id_proveedor')) {
                    $table->integer('id_proveedor')->nullable()->after('id_tipo_producto');
                }
                if (!Schema::hasColumn('producto', 'imagen')) {
                    $table->string('imagen', 255)->nullable()->after('descripcion');
                }
                if (!Schema::hasColumn('producto', 'stock_minimo')) {
                    $table->integer('stock_minimo')->default(5)->after('stock');
                }
                if (!Schema::hasColumn('producto', 'costo_compra')) {
                    $table->decimal('costo_compra', 10, 2)->nullable()->default(0)->after('precio_mayor');
                }
            });
        }

        // 2. Ampliar tabla PROVEEDOR
        if (Schema::hasTable('proveedor')) {
            Schema::table('proveedor', function (Blueprint $table) {
                if (!Schema::hasColumn('proveedor', 'calificacion')) {
                    $table->tinyInteger('calificacion')->default(5)->after('pais');
                }
                if (!Schema::hasColumn('proveedor', 'notas')) {
                    $table->text('notas')->nullable()->after('calificacion');
                }
            });
        }

        // 3. Ampliar tabla VENTA
        if (Schema::hasTable('venta')) {
            Schema::table('venta', function (Blueprint $table) {
                if (!Schema::hasColumn('venta', 'descuento')) {
                    $table->decimal('descuento', 10, 2)->default(0)->after('tipo_cliente');
                }
                if (!Schema::hasColumn('venta', 'metodo_pago')) {
                    $table->string('metodo_pago', 50)->default('Efectivo')->after('descuento');
                }
                if (!Schema::hasColumn('venta', 'estado_despacho')) {
                    $table->enum('estado_despacho', ['Pendiente', 'Despachado', 'Entregado'])->default('Pendiente')->after('estado');
                }
                if (!Schema::hasColumn('venta', 'guia_despacho')) {
                    $table->string('guia_despacho', 100)->nullable()->after('estado_despacho');
                }
                if (!Schema::hasColumn('venta', 'fecha_despacho')) {
                    $table->dateTime('fecha_despacho')->nullable()->after('guia_despacho');
                }
                if (!Schema::hasColumn('venta', 'fecha_entrega')) {
                    $table->dateTime('fecha_entrega')->nullable()->after('fecha_despacho');
                }
            });
        }

        // 4. Tabla IMPORTACION (HU7, HU8, HU9, HU10)
        if (!Schema::hasTable('importacion')) {
            Schema::create('importacion', function (Blueprint $table) {
                $table->integer('id_importacion')->autoIncrement();
                $table->integer('id_proveedor');
                $table->integer('id_usuario');
                $table->string('codigo_embarque', 50);
                $table->dateTime('fecha_pedido')->useCurrent();
                $table->enum('estado_logistico', ['En tránsito', 'En Aduana', 'Recibido'])->default('En tránsito');
                $table->decimal('monto_fob_usd', 12, 2)->default(0);
                $table->decimal('costo_flete', 10, 2)->default(0);
                $table->decimal('costo_aduana_aranceles', 10, 2)->default(0);
                $table->decimal('costo_seguro', 10, 2)->default(0);
                $table->decimal('otros_gastos', 10, 2)->default(0);
                $table->decimal('total_gastos_internacion', 12, 2)->default(0);
                $table->decimal('tipo_cambio', 6, 2)->default(6.96);
                $table->string('pais_origen', 100)->nullable();
                $table->date('fecha_llegada_estimada')->nullable();
                $table->dateTime('fecha_recibido')->nullable();
                $table->text('observaciones')->nullable();
            });
        }

        // 5. Tabla DETALLE_IMPORTACION
        if (!Schema::hasTable('detalle_importacion')) {
            Schema::create('detalle_importacion', function (Blueprint $table) {
                $table->integer('id_detalle_importacion')->autoIncrement();
                $table->integer('id_importacion');
                $table->integer('id_producto');
                $table->integer('cantidad');
                $table->decimal('precio_unitario_fob_usd', 10, 2);
                $table->decimal('gasto_internacion_unitario_bs', 10, 2)->default(0);
                $table->decimal('costo_final_unitario_bs', 10, 2)->default(0);
            });
        }

        // 6. Tabla SEGUIMIENTO_IMPORTACION (HU10)
        if (!Schema::hasTable('seguimiento_importacion')) {
            Schema::create('seguimiento_importacion', function (Blueprint $table) {
                $table->integer('id_seguimiento')->autoIncrement();
                $table->integer('id_importacion');
                $table->dateTime('fecha_hora')->useCurrent();
                $table->string('ubicacion', 150);
                $table->string('estado', 50);
                $table->text('detalle')->nullable();
            });
        }

        // 7. Tabla MERMA (HU13)
        if (!Schema::hasTable('merma')) {
            Schema::create('merma', function (Blueprint $table) {
                $table->integer('id_merma')->autoIncrement();
                $table->integer('id_producto');
                $table->integer('id_usuario');
                $table->integer('cantidad');
                $table->enum('motivo', [
                    'Dañado en importación',
                    'Rotura en almacén',
                    'Defecto de fábrica',
                    'Devolución de cliente',
                    'Obsolescencia / Otro'
                ])->default('Rotura en almacén');
                $table->text('descripcion_dano')->nullable();
                $table->dateTime('fecha')->useCurrent();
            });
        }

        // 8. Tabla CONSULTA_CLIENTE (HU17 - CRM / Cotizaciones)
        if (!Schema::hasTable('consulta_cliente')) {
            Schema::create('consulta_cliente', function (Blueprint $table) {
                $table->integer('id_consulta')->autoIncrement();
                $table->string('nombre_cliente', 150);
                $table->string('telefono', 50);
                $table->enum('canal', ['WhatsApp', 'Facebook', 'Presencial', 'Llamada'])->default('WhatsApp');
                $table->integer('id_producto')->nullable();
                $table->text('mensaje_solicitud');
                $table->decimal('monto_cotizado', 10, 2)->nullable();
                $table->enum('estado', ['Pendiente', 'Cotizado', 'Venta Concretada', 'Descartado'])->default('Pendiente');
                $table->dateTime('fecha')->useCurrent();
            });
        }

        // 9. Tabla RESERVA_PRODUCTO (HU25 - Catálogo público)
        if (!Schema::hasTable('reserva_producto')) {
            Schema::create('reserva_producto', function (Blueprint $table) {
                $table->integer('id_reserva')->autoIncrement();
                $table->string('codigo_reserva', 30)->unique();
                $table->integer('id_producto');
                $table->string('nombre_cliente', 150);
                $table->string('telefono_cliente', 50);
                $table->integer('cantidad')->default(1);
                $table->decimal('precio_unitario', 10, 2);
                $table->decimal('total_reserva', 10, 2);
                $table->enum('estado', ['Pendiente', 'Confirmada', 'Retirada / Vendida', 'Cancelada'])->default('Pendiente');
                $table->dateTime('fecha_limite');
                $table->dateTime('fecha_creacion')->useCurrent();
            });
        }

        // 10. Tabla CIERRE_CAJA (HU22 - Arqueo diario)
        if (!Schema::hasTable('cierre_caja')) {
            Schema::create('cierre_caja', function (Blueprint $table) {
                $table->integer('id_cierre')->autoIncrement();
                $table->integer('id_usuario');
                $table->date('fecha_cierre');
                $table->decimal('total_efectivo', 12, 2)->default(0);
                $table->decimal('total_transferencia_qr', 12, 2)->default(0);
                $table->decimal('total_general', 12, 2)->default(0);
                $table->integer('total_ventas_conteo')->default(0);
                $table->text('observaciones')->nullable();
                $table->dateTime('fecha_registro')->useCurrent();
            });
        }

        // 11. Tabla CONFIGURACION_EMPRESA (HU30)
        if (!Schema::hasTable('configuracion_empresa')) {
            Schema::create('configuracion_empresa', function (Blueprint $table) {
                $table->integer('id_config')->autoIncrement();
                $table->string('razon_social', 200)->default('IMPORTADORA PEBIE');
                $table->string('nit', 50)->default('7839401018');
                $table->string('direccion', 255)->default('Calle Bolívar N° 450 entre 6 de Octubre y Soria Galvarro, Oruro - Bolivia');
                $table->string('telefono', 50)->default('+591 72412345');
                $table->string('email', 150)->default('contacto@pebie.com');
                $table->string('ciudad', 100)->default('Oruro, Bolivia');
                $table->string('moneda_principal', 20)->default('Bs.');
                $table->string('logo_path', 255)->nullable();
                $table->text('mensaje_pie_pagina')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_empresa');
        Schema::dropIfExists('cierre_caja');
        Schema::dropIfExists('reserva_producto');
        Schema::dropIfExists('consulta_cliente');
        Schema::dropIfExists('merma');
        Schema::dropIfExists('seguimiento_importacion');
        Schema::dropIfExists('detalle_importacion');
        Schema::dropIfExists('importacion');
    }
};
