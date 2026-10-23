<?php

namespace App\Http\Controllers;

use App\Models\VentaModel;
use App\Models\CompraModel;
use App\Models\ProductoModel;
use App\Models\ClienteModel;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (session('rol') == 4) return redirect()->route('ventas.directas');
        $rol = (int) session('rol');
        // 1. Métricas generales
        $totalProductos = ProductoModel::where('estado', 1)->count();
        $totalClientes = ClienteModel::count();
        $totalUsuarios = UsuarioModel::count();

        // 2. Alertas de Stock Bajo (menor o igual a 5 unidades)
        $productosBajoStock = ProductoModel::with('tipoProducto')
            ->where('estado', 1)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock', 'asc')
            ->get();
        $conteoBajoStock = $productosBajoStock->count();

        // 3. Ventas de Hoy y del Mes
        $hoy = date('Y-m-d');
        $inicioMes = date('Y-m-01 00:00:00');
        $finMes = date('Y-m-t 23:59:59');

        $ventasQuery = VentaModel::with('detalles')->where('estado', 'Completado');
        $ventasHoyQuery = (clone $ventasQuery)->whereDate('fecha', $hoy);
        $ventasMesQuery = (clone $ventasQuery)->whereBetween('fecha', [$inicioMes, $finMes]);
        if ($rol === 2) {
            $ventasHoyQuery->where('id_usuario', session('idusuario'));
            $ventasMesQuery->where('id_usuario', session('idusuario'));
        }
        $ventasHoy = $ventasHoyQuery
            ->get();
        $totalIngresosHoy = $ventasHoy->sum('total');

        $ventasMes = $ventasMesQuery->get();
        $totalIngresosMes = $ventasMes->sum('total');
        $operacionesVentasMes = $ventasMes->count();
        $unidadesVendidasMes = $ventasMes->sum(fn ($venta) => $venta->detalles->sum('cantidad'));
        $ticketPromedioMes = $operacionesVentasMes > 0 ? $totalIngresosMes / $operacionesVentasMes : 0;
        $productosCriticos = $productosBajoStock->count();
        $totalProductosActivos = ProductoModel::where('estado', 1)->count();
        $valorInventario = (float) ProductoModel::where('estado', 1)
            ->selectRaw('COALESCE(SUM(stock * COALESCE(costo_compra, 0)), 0) as valor')->value('valor');

        // 4. Compras del Mes
        $comprasMes = $rol === 1 ? CompraModel::with('detalles')
            ->whereBetween('fecha', [$inicioMes, $finMes])->get() : collect();
        $totalGastosMes = $comprasMes->sum(function ($c) {
            return $c->detalles->sum(function ($d) {
                return $d->cantidad * $d->precio_compra;
            });
        });

        // 5. Últimas Ventas
        $ultimasVentasQuery = VentaModel::with(['cliente.unipersonal', 'cliente.institucional', 'usuario'])
            ->orderBy('fecha', 'desc');
        if ($rol === 2) $ultimasVentasQuery->where('id_usuario', session('idusuario'));
        $ultimasVentas = $ultimasVentasQuery
            ->take(6)
            ->get();

        // 6. Últimas Compras
        $ultimasCompras = $rol === 1 ? CompraModel::with(['proveedor', 'usuario', 'detalles'])
            ->orderBy('fecha', 'desc')
            ->take(5)
            ->get() : collect();

        // 7. Datos mensuales para gráfico (últimos 6 meses)
        $mesesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $mesTimestamp = strtotime("-$i months");
            $mesKey = date('Y-m', $mesTimestamp);
            $mesNombre = date('M Y', $mesTimestamp);
            
            $inicio = date('Y-m-01 00:00:00', $mesTimestamp);
            $fin = date('Y-m-t 23:59:59', $mesTimestamp);

            $ventasDelMesQuery = VentaModel::whereBetween('fecha', [$inicio, $fin])->where('estado', 'Completado');
            if ($rol === 2) $ventasDelMesQuery->where('id_usuario', session('idusuario'));
            $ventasDelMes = $ventasDelMesQuery->get();
            $montoVentas = $ventasDelMes->sum('total');

            $mesesData[] = [
                'mes' => $mesNombre,
                'total' => round($montoVentas, 2)
            ];
        }

        return view('administrador', compact(
            'totalProductos',
            'totalClientes',
            'totalUsuarios',
            'conteoBajoStock',
            'productosBajoStock',
            'totalIngresosHoy',
            'totalIngresosMes',
            'operacionesVentasMes',
            'unidadesVendidasMes',
            'ticketPromedioMes',
            'totalGastosMes',
            'comprasMes',
            'valorInventario',
            'totalProductosActivos',
            'productosCriticos',
            'ultimasVentas',
            'ultimasCompras',
            'mesesData'
        ));
    }
}
