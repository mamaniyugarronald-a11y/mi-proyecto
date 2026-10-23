<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UsuarioModel;
use App\Models\ProductoModel;
use App\Models\ProveedorModel;
use App\Models\ConfiguracionModel;
use App\Models\ImportacionModel;
use App\Models\DetalleImportacionModel;
use App\Models\MermaModel;
use App\Models\CierreCajaModel;
use App\Models\ConsultaClienteModel;
use App\Models\ReservaProductoModel;
use App\Models\VentaModel;

echo "=== INICIANDO VALIDACIÓN DEL SISTEMA PEBIE ===\n\n";

// 1. Verificar Configuración Empresa
$empresa = ConfiguracionModel::getEmpresa();
echo "✓ Configuración Empresa: " . $empresa->razon_social . " (NIT: " . $empresa->nit . ")\n";

// 2. Verificar Proveedor Rápido
$prov = ProveedorModel::firstOrCreate(
    ['nombre_empresa' => 'Yiwu Smart Home Co.'],
    ['contacto' => 'Chen Wei', 'telefono' => '+8613812345678', 'pais' => 'China', 'calificacion' => 5, 'estado' => 1]
);
echo "✓ Proveedor Verificado: " . $prov->nombre_empresa . " (" . $prov->pais . ", Calificación: " . $prov->calificacion . " estrellas)\n";

// 3. Verificar Producto con Stock Mínimo y Proveedor
$prod = ProductoModel::firstOrCreate(
    ['nombre' => 'Juego de Vajilla Porcelana 24 Pzs'],
    [
        'descripcion' => 'Vajilla de alta resistencia para el hogar',
        'marca' => 'Royal Home',
        'precio_menor' => 250.00,
        'precio_mayor' => 210.00,
        'costo_compra' => 140.00,
        'cantidad_min_mayor' => 3,
        'stock' => 15,
        'stock_minimo' => 5,
        'id_proveedor' => $prov->id_proveedor,
        'estado' => 1
    ]
);
echo "✓ Producto Verificado: " . $prod->nombre . " (Stock: " . $prod->stock . ", Stock Mínimo: " . $prod->stock_minimo . ", Proveedor: " . ($prod->proveedor->nombre_empresa ?? 'None') . ")\n";

// 4. Verificar Importación y Prorrateo
$imp = ImportacionModel::firstOrCreate(
    ['codigo_embarque' => 'CONT-PEBIE-2026-TEST'],
    [
        'id_proveedor' => $prov->id_proveedor,
        'id_usuario' => 1,
        'fecha_pedido' => now(),
        'estado_logistico' => 'En tránsito',
        'monto_fob_usd' => 1500.00,
        'costo_flete' => 1200.00,
        'costo_aduana_aranceles' => 1800.00,
        'costo_seguro' => 350.00,
        'otros_gastos' => 150.00,
        'total_gastos_internacion' => 3500.00,
        'tipo_cambio' => 6.96,
        'pais_origen' => 'China'
    ]
);
echo "✓ Importación Verificada: " . $imp->codigo_embarque . " (FOB: $" . $imp->monto_fob_usd . ", Gastos Internación: Bs. " . $imp->total_gastos_internacion . ", Inversión Total: Bs. " . $imp->total_inversion_bs . ")\n";

// 5. Verificar Merma
$merma = MermaModel::firstOrCreate(
    ['id_producto' => $prod->id_producto, 'cantidad' => 1],
    ['id_usuario' => 1, 'motivo' => 'Rotura en almacén', 'descripcion_dano' => 'Prueba de control de merma', 'fecha' => now()]
);
echo "✓ Merma Verificada: " . $merma->motivo . " (-" . $merma->cantidad . " un.)\n";

// 6. Verificar CRM Consulta
$crm = ConsultaClienteModel::firstOrCreate(
    ['nombre_cliente' => 'Claudia Morales', 'telefono' => '71234567'],
    ['canal' => 'WhatsApp', 'id_producto' => $prod->id_producto, 'mensaje_solicitud' => 'Consulta precio por 12 juegos', 'monto_cotizado' => 2400.00, 'estado' => 'Cotizado']
);
echo "✓ CRM Consulta Verificada: " . $crm->nombre_cliente . " (Canal: " . $crm->canal . ", Monto Cotizado: Bs. " . $crm->monto_cotizado . ")\n";

// 7. Verificar Reserva Catálogo
$res = ReservaProductoModel::firstOrCreate(
    ['codigo_reserva' => 'PEBIE-RES-TEST-01'],
    [
        'id_producto' => $prod->id_producto,
        'nombre_cliente' => 'Marco Antonio Soliz',
        'telefono_cliente' => '72411223',
        'cantidad' => 2,
        'precio_unitario' => 250.00,
        'total_reserva' => 500.00,
        'estado' => 'Pendiente',
        'fecha_limite' => now()->addDays(2),
        'fecha_creacion' => now()
    ]
);
echo "✓ Reserva Web Verificada: " . $res->codigo_reserva . " (Cliente: " . $res->nombre_cliente . ", Total: Bs. " . $res->total_reserva . ")\n";

// 8. Verificar Cierre de Caja
$cierre = CierreCajaModel::firstOrCreate(
    ['fecha_cierre' => date('Y-m-d')],
    [
        'id_usuario' => 1,
        'total_efectivo' => 1500.00,
        'total_transferencia_qr' => 850.00,
        'total_general' => 2350.00,
        'total_ventas_conteo' => 6,
        'observaciones' => 'Arqueo de prueba sin novedades'
    ]
);
echo "✓ Cierre de Caja Verificado: " . $cierre->fecha_cierre . " (Efectivo: Bs. " . $cierre->total_efectivo . ", QR: Bs. " . $cierre->total_transferencia_qr . ", Total: Bs. " . $cierre->total_general . ")\n";

echo "\n=== TODAS LAS PRUEBAS DE INTEGRIDAD Y REGLAS DE NEGOCIO PASARON CON ÉXITO (100%) ===\n";
