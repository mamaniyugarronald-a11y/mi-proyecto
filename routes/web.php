<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\TipoProductoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\MermaController;
use App\Http\Controllers\CatalogoPublicoController;
use App\Http\Controllers\ConfiguracionController;

/*
|--------------------------------------------------------------------------
| Web Routes - Importadora PEBIE
|--------------------------------------------------------------------------
*/

// ==========================================
// RUTAS PÚBLICAS (AUTENTICACIÓN & CATÁLOGO CLIENTE)
// ==========================================
// El sitio abre siempre en el catálogo para los clientes.
Route::get('/', [CatalogoPublicoController::class, 'index'])->name('inicio');
Route::get('/login', [UsuarioController::class, 'index'])->name('login');
Route::post('/validar', [UsuarioController::class, 'validar'])->name('validar');
Route::get('/logout', [UsuarioController::class, 'cerrar'])->name('logout');

// HU24 & HU25: CATÁLOGO PÚBLICO WEB Y RESERVAS
Route::get('/catalogo', [CatalogoPublicoController::class, 'index'])->name('catalogo.publico');
Route::post('/catalogo/reservar', [CatalogoPublicoController::class, 'reservar'])->name('catalogo.reservar');


// ==========================================
// RUTAS PROTEGIDAS (REQUIEREN INICIO DE SESIÓN)
// ==========================================
Route::middleware(['auth.custom'])->group(function () {

    // --- DASHBOARD / INICIO ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/administrador', function () {
        return redirect()->route('dashboard');
    })->name('administrador');

    // --- VENTAS & POS & DESPACHO (HU16, HU19, HU20, HU21, HU23) ---
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas');
    Route::get('/ventas/directas', [VentaController::class, 'directas'])->name('ventas.directas');
    Route::get('/ventas/reservas', [VentaController::class, 'reservas'])->name('ventas.reservas');
    Route::get('/ventas/pos', [VentaController::class, 'pos'])->name('ventas.pos');
    Route::post('/guardarventa', [VentaController::class, 'guardar'])->name('guardar.venta');
    Route::get('/ventas/comprobante/{id}', [VentaController::class, 'comprobante'])->name('ventas.comprobante');
    Route::get('/ventas/imprimir-listado', [VentaController::class, 'imprimirListado'])->name('ventas.imprimir.listado');
    Route::post('/ventas/despacho/{id}', [VentaController::class, 'actualizarDespacho'])->name('ventas.despacho');
    Route::post('/ventas/anular/{id}', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::post('/ventas/reservas/{id}/confirmar', [VentaController::class, 'confirmarReserva'])->name('ventas.reservas.confirmar');
    Route::post('/ventas/completar/{id}', [VentaController::class, 'completarPedido'])->name('ventas.completar');

    // --- COMPRAS LOCALES & HISTORIAL ---
    Route::get('/compras', [CompraController::class, 'index'])->name('compras');
    Route::post('/guardarcompra', [CompraController::class, 'guardar'])->name('guardarcompra');
    Route::get('/compras/comprobante/{id}', [CompraController::class, 'comprobante'])->name('compras.comprobante');
    Route::get('/compras/imprimir-listado', [CompraController::class, 'imprimirListado'])->name('compras.imprimir.listado');
    Route::get('/historial-compras', [CompraController::class, 'historial'])->name('historial.compras');


    // --- PRODUCTOS & KARDEX (HU11, HU12, HU14) ---
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos');
    Route::post('/guardarproducto', [ProductoController::class, 'guardar'])->name('guardar.producto');
    Route::post('/actualizarproducto', [ProductoController::class, 'actualizar'])->name('actualizar.producto');
    Route::post('/compras/actualizar-producto', [ProductoController::class, 'actualizarDesdeCompra'])->name('compras.actualizar.producto');
    Route::get('/eliminarproducto/{id}', [ProductoController::class, 'eliminar'])->name('eliminar.producto');
    Route::get('/productos/kardex/{id}', [ProductoController::class, 'kardex'])->name('productos.kardex');

    // --- MERMAS, DAÑOS Y DEVOLUCIONES (HU13) ---
    Route::get('/mermas', [MermaController::class, 'index'])->name('mermas');
    Route::post('/guardarmerma', [MermaController::class, 'guardar'])->name('guardar.merma');

    // --- CLASIFICACIÓN / TIPOS DE PRODUCTO ---
    Route::get('/tipos-producto', [TipoProductoController::class, 'index'])->name('tipos.producto');
    Route::post('/guardar-tipo-producto', [TipoProductoController::class, 'guardar'])->name('guardar.tipo.producto');
    Route::post('/actualizar-tipo-producto', [TipoProductoController::class, 'actualizar'])->name('actualizar.tipo.producto');
    Route::get('/eliminar-tipo-producto/{id}', [TipoProductoController::class, 'eliminar'])->name('eliminar.tipo.producto');

    // --- CLIENTES & HISTORIAL COMERCIAL (HU3, HU18) ---
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
    Route::post('/guardarcliente', [ClienteController::class, 'guardar'])->name('guardarcliente');
    Route::post('/actualizarcliente', [ClienteController::class, 'actualizar'])->name('actualizarcliente');
    Route::get('/eliminarcliente/{id}', [ClienteController::class, 'eliminar'])->name('eliminarcliente');

    // --- PROVEEDORES & AJAX MODAL RÁPIDO (HU6) ---
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores');
    Route::post('/guardarproveedor', [ProveedorController::class, 'guardar'])->name('guardarproveedor');
    Route::post('/proveedores/rapido', [ProveedorController::class, 'guardarRapido'])->name('guardarproveedor.rapido');
    Route::post('/actualizarproveedor', [ProveedorController::class, 'actualizar'])->name('actualizarproveedor');
    Route::get('/eliminarproveedor/{id}', [ProveedorController::class, 'eliminar'])->name('eliminarproveedor');


    // --- RESERVAS DEL CATÁLOGO PÚBLICO (ADMIN) ---
    Route::get('/reservas', [CatalogoPublicoController::class, 'listaReservas'])->name('reservas.admin');
    Route::post('/reservas/estado/{id}', [CatalogoPublicoController::class, 'cambiarEstadoReserva'])->name('reservas.estado');


    // --- UBICACIONES / SUCURSALES / ALMACÉN (HU15) ---
    Route::get('/ubicaciones', [UbicacionController::class, 'index'])->name('ubicaciones');
    Route::post('/guardarubicacion', [UbicacionController::class, 'guardar'])->name('guardar.ubicacion');
    Route::post('/actualizarubicacion', [UbicacionController::class, 'actualizar'])->name('actualizar.ubicacion');
    Route::get('/eliminarubicacion/{id}', [UbicacionController::class, 'eliminar'])->name('eliminar.ubicacion');
    Route::post('/asignarstockubicacion', [UbicacionController::class, 'asignarStock'])->name('asignar.stock.ubicacion');
    Route::post('/ubicaciones/solicitudes', [UbicacionController::class, 'solicitarTransferencia'])->name('solicitar.transferencia.ubicacion');
    Route::post('/ubicaciones/solicitudes/{id}/cargando', [UbicacionController::class, 'marcarCargando'])->name('cargando.transferencia.ubicacion');
    Route::post('/ubicaciones/solicitudes/{id}/completar', [UbicacionController::class, 'completarTransferencia'])->name('completar.transferencia.ubicacion');

    // --- REPORTES & ESTADÍSTICAS (HU26, HU27, HU28, HU29) ---
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');

    // --- CONFIGURACIÓN DE IDENTIDAD CORPORATIVA (HU30) ---
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');
    Route::post('/guardarconfiguracion', [ConfiguracionController::class, 'guardar'])->name('guardar.configuracion');

    // --- USUARIOS & AUDITORÍA DE ACCESOS (HU1, HU2, HU4, HU5) ---
    Route::get('/usuarios', [UsuarioController::class, 'usuarios'])->name('usuarios');
    Route::post('/guardarusuario', [UsuarioController::class, 'guardar'])->name('guardarusuario');
    Route::post('/actualizarusuario', [UsuarioController::class, 'actualizar'])->name('actualizarusuario');
    Route::get('/eliminarusuario/{id}', [UsuarioController::class, 'eliminar'])->name('eliminarusuario');
    Route::post('/roles', [UsuarioController::class, 'guardarRol'])->name('roles.guardar');
    Route::post('/roles/actualizar', [UsuarioController::class, 'actualizarRol'])->name('roles.actualizar');
    Route::post('/roles/{id}/eliminar', [UsuarioController::class, 'eliminarRol'])->name('roles.eliminar');
    Route::get('/auditoria', [UsuarioController::class, 'auditoria'])->name('auditoria');

});
