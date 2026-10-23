<?php

namespace App\Http\Controllers;

use App\Models\CompraModel;
use App\Models\HistorialSesionModel;
use App\Models\ProductoModel;
use App\Models\VentaModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $agrupacion = $request->input('agrupacion', 'dia');
        abort_unless(in_array($agrupacion, ['dia', 'mes'], true), 422);
        $formatoFecha = $agrupacion === 'mes' ? 'Y-m' : 'Y-m-d';
        $request->validate([
            'fecha_desde' => ['nullable', 'date_format:' . $formatoFecha],
            'fecha_hasta' => ['nullable', 'date_format:' . $formatoFecha],
        ]);

        $rol = (int) session('rol');
        abort_unless(in_array($rol, [1, 2, 3, 4], true), 403);
        $fechaDesde = $request->input('fecha_desde') ?: ($agrupacion === 'mes' ? now()->format('Y-m') : now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta') ?: ($agrupacion === 'mes' ? now()->format('Y-m') : now()->toDateString());
        if ($fechaHasta < $fechaDesde) {
            throw ValidationException::withMessages(['fecha_hasta' => 'La fecha final debe ser igual o posterior a la fecha inicial.']);
        }
        $inicioPeriodo = $agrupacion === 'mes'
            ? Carbon::createFromFormat('Y-m', $fechaDesde)->startOfMonth()
            : Carbon::createFromFormat('Y-m-d', $fechaDesde)->startOfDay();
        $finPeriodo = $agrupacion === 'mes'
            ? Carbon::createFromFormat('Y-m', $fechaHasta)->endOfMonth()
            : Carbon::createFromFormat('Y-m-d', $fechaHasta)->endOfDay();
        $inicioPeriodoSql = $inicioPeriodo->toDateTimeString();
        $finPeriodoSql = $finPeriodo->toDateTimeString();

        $ventasQuery = VentaModel::with(['cliente.unipersonal', 'cliente.institucional', 'usuario', 'detalles.producto.tipoProducto'])
            ->whereBetween('fecha', [$inicioPeriodoSql, $finPeriodoSql])
            ->orderByDesc('fecha');
        if ($rol === 2) {
            $ventasQuery->where('id_usuario', session('idusuario'))->where('estado', 'Completado');
        } elseif ($rol === 1) {
            $ventasQuery->where('estado', 'Completado');
        } elseif ($rol === 4) {
            $ventasQuery->where('promocion', 'Transporte')->where('estado', '!=', 'Cancelado');
        }
        $ventas = $ventasQuery->get();
        $ventasCompletadas = $ventas->where('estado', 'Completado');
        $totalVentasMonto = $ventasCompletadas->sum(fn ($venta) => $venta->total);
        $unidadesVendidas = $ventasCompletadas->sum(fn ($venta) => $venta->detalles->sum('cantidad'));

        $compras = collect();
        $comprasPorProveedor = collect();
        $totalComprasMonto = 0;
        $productos = collect();
        $productosCriticos = collect();
        $stockCriticoGrafica = collect();
        $inventarioPorCategoria = collect();
        $valorInventario = 0;
        $ventasPorDia = collect();
        $ventasPorDiaGrafica = collect();
        $productosVendidos = collect();
        $clientesFrecuentes = collect();
        $combinacionesProductos = collect();
        $ticketPromedio = 0;
        $totalDescuentos = 0;
        $ventasConDescuento = 0;
        $actividadUsuarios = collect();
        $actividadPorUsuario = collect();
        $ventasPendientesDespacho = collect();
        $despachosPorEstado = collect();

        if (in_array($rol, [1, 2], true)) {
            $ventasPorDia = $ventasCompletadas->groupBy(fn ($venta) => Carbon::parse($venta->fecha)->format($agrupacion === 'mes' ? 'Y-m' : 'Y-m-d'))
                ->map(fn ($grupo, $fecha) => ['fecha' => $fecha, 'operaciones' => $grupo->count(), 'total' => $grupo->sum(fn ($venta) => $venta->total)])
                ->sortBy('fecha')->values();
            $ventasPorDiaGrafica = $ventasPorDia->map(fn ($periodo) => [
                'etiqueta' => $agrupacion === 'mes' ? Carbon::createFromFormat('Y-m', $periodo['fecha'])->format('m/Y') : Carbon::parse($periodo['fecha'])->format('d/m/Y'),
                'valor' => $periodo['total'],
            ]);
        }

        if ($rol === 2) {
            $ticketPromedio = $ventasCompletadas->count() > 0 ? $totalVentasMonto / $ventasCompletadas->count() : 0;
            $totalDescuentos = $ventasCompletadas->sum(fn ($venta) => (float) ($venta->descuento ?? 0));
            $ventasConDescuento = $ventasCompletadas->filter(fn ($venta) => (float) ($venta->descuento ?? 0) > 0)->count();

            $detalleFilas = $ventasCompletadas->flatMap(fn ($venta) => $venta->detalles);
            $productosVendidos = $detalleFilas->groupBy('id_producto')->map(function ($detalles) {
                $producto = $detalles->first()->producto;
                return [
                    'producto' => $producto->nombre ?? 'Producto eliminado',
                    'marca' => $producto->marca ?? '—',
                    'categoria' => $producto->tipoProducto->nombre_tipo ?? 'General',
                    'unidades' => (int) $detalles->sum('cantidad'),
                    'importe' => (float) $detalles->sum(fn ($detalle) => (float) $detalle->cantidad * (float) $detalle->precio_aplicado),
                ];
            })->sortByDesc('unidades')->values();

            $clientesFrecuentes = $ventasCompletadas->whereNotNull('id_cliente')->groupBy('id_cliente')->map(function ($grupo) {
                $cliente = $grupo->first()->cliente;
                return [
                    'cliente' => $cliente->nombre_completo ?? 'Cliente',
                    'operaciones' => $grupo->count(),
                    'total' => (float) $grupo->sum('total'),
                    'ultima_compra' => $grupo->max('fecha'),
                ];
            })->sortByDesc('operaciones')->values();

            $pares = [];
            foreach ($ventasCompletadas as $venta) {
                $nombres = $venta->detalles->map(fn ($detalle) => $detalle->producto->nombre ?? null)->filter()->unique()->sort()->values();
                for ($i = 0; $i < $nombres->count(); $i++) {
                    for ($j = $i + 1; $j < $nombres->count(); $j++) {
                        $key = $nombres[$i] . ' + ' . $nombres[$j];
                        $pares[$key] = ($pares[$key] ?? 0) + 1;
                    }
                }
            }
            $combinacionesProductos = collect($pares)->map(fn ($cantidad, $par) => ['combinacion' => $par, 'ventas' => $cantidad])->sortByDesc('ventas')->take(10)->values();
        }

        if ($rol === 1) {
            $compras = CompraModel::with(['proveedor', 'usuario', 'detalles.producto'])
                ->whereBetween('fecha', [$inicioPeriodoSql, $finPeriodoSql])
                ->orderByDesc('fecha')->get();
            $totalComprasMonto = $compras->sum(fn ($compra) => $compra->detalles->sum(fn ($detalle) => $detalle->cantidad * $detalle->precio_compra));
            $comprasPorProveedor = $compras->groupBy(fn ($compra) => $compra->proveedor->nombre_empresa ?? 'Sin proveedor')
                ->map(fn ($grupo, $proveedor) => ['etiqueta' => $proveedor, 'valor' => $grupo->sum(fn ($compra) => $compra->detalles->sum(fn ($detalle) => $detalle->cantidad * $detalle->precio_compra))])
                ->sortByDesc('valor')->take(10)->values();

            $productos = ProductoModel::with('tipoProducto')->where('estado', 1)->orderBy('nombre')->get();
            $productos->each(function ($producto) {
                $producto->valor_inventario = (float) $producto->stock * (float) $producto->costo_compra;
            });
            $productosCriticos = $productos->filter(fn ($producto) => $producto->stock <= $producto->stock_minimo)->values();
            $stockCriticoGrafica = collect([
                ['etiqueta' => 'Crítico', 'valor' => $productosCriticos->count()],
                ['etiqueta' => 'Sobre mínimo', 'valor' => $productos->count() - $productosCriticos->count()],
            ]);
            $valorInventario = $productos->sum('valor_inventario');
            $inventarioPorCategoria = $productos->groupBy(fn ($producto) => $producto->tipoProducto->nombre_tipo ?? 'General')
                ->map(fn ($grupo, $categoria) => ['etiqueta' => $categoria, 'valor' => $grupo->sum('valor_inventario')])
                ->sortByDesc('valor')->take(10)->values();

            $actividadUsuarios = HistorialSesionModel::with('usuario.rol')
                ->whereBetween('fecha_hora', [$inicioPeriodoSql, $finPeriodoSql])
                ->orderByDesc('fecha_hora')->limit(100)->get();
            $actividadPorUsuario = $actividadUsuarios->groupBy(fn ($sesion) => $sesion->usuario->nombre ?? 'Usuario eliminado')
                ->map(fn ($grupo, $usuario) => ['etiqueta' => $usuario, 'valor' => $grupo->count()])
                ->sortByDesc('valor')->take(10)->values();
            $ventasPendientesDespacho = VentaModel::with(['cliente.unipersonal', 'cliente.institucional', 'usuario'])
                ->where('promocion', 'Transporte')->where('estado', '!=', 'Cancelado')
                ->whereBetween('fecha', [$inicioPeriodoSql, $finPeriodoSql])
                ->orderBy('fecha')->get();
            $despachosPorEstado = $ventasPendientesDespacho->groupBy('estado_despacho')
                ->map(fn ($grupo, $estado) => ['etiqueta' => $estado, 'valor' => $grupo->count()])->values();
        } elseif ($rol === 3) {
            $productos = ProductoModel::with('tipoProducto')->where('estado', 1)->orderBy('nombre')->get();
            $productosCriticos = $productos->filter(fn ($producto) => $producto->stock <= $producto->stock_minimo)->values();
            $stockCriticoGrafica = collect([
                ['etiqueta' => 'Crítico', 'valor' => $productosCriticos->count()],
                ['etiqueta' => 'Sobre mínimo', 'valor' => $productos->count() - $productosCriticos->count()],
            ]);
            $inventarioPorCategoria = $productos->groupBy(fn ($producto) => $producto->tipoProducto->nombre_tipo ?? 'General')
                ->map(fn ($grupo, $categoria) => ['etiqueta' => $categoria, 'valor' => $grupo->sum('stock')])
                ->sortByDesc('valor')->take(10)->values();
        }
        if ($rol === 4) {
            $despachosPorEstado = $ventas->where('estado', '!=', 'Cancelado')->groupBy('estado_despacho')
                ->map(fn ($grupo, $estado) => ['etiqueta' => $estado, 'valor' => $grupo->count()])->values();
        }

        return view('reporte', compact(
            'rol', 'fechaDesde', 'fechaHasta', 'agrupacion', 'ventas', 'ventasCompletadas', 'totalVentasMonto', 'unidadesVendidas',
            'compras', 'totalComprasMonto', 'comprasPorProveedor', 'productos', 'productosCriticos', 'stockCriticoGrafica', 'valorInventario', 'inventarioPorCategoria',
            'ventasPorDia', 'ventasPorDiaGrafica', 'productosVendidos', 'clientesFrecuentes', 'combinacionesProductos', 'ticketPromedio', 'totalDescuentos', 'ventasConDescuento',
            'actividadUsuarios', 'actividadPorUsuario', 'ventasPendientesDespacho', 'despachosPorEstado'
        ));
    }
}
