<?php

namespace App\Http\Controllers;

use App\Models\CompraModel;
use App\Models\ProveedorModel;
use App\Models\ProductoModel;
use App\Models\DetalleCompraModel;
use App\Models\ConfiguracionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // ==========================================
    // LISTAR COMPRAS
    // ==========================================
    public function index(Request $request)
    {
        $query = CompraModel::with(['proveedor', 'usuario', 'detalles.producto'])
            ->orderBy('fecha', 'desc')
            ;
        if ($request->filled('id_proveedor')) $query->where('id_proveedor', $request->id_proveedor);
        // El panel operativo inicia con las compras del día completo.
        $fechaDesde = $request->input('fecha_desde') ?: now()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: $fechaDesde;
        $query->whereBetween('fecha', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);
        $compras = $query->get();

        $proveedores = ProveedorModel::where('estado', 1)->get();
        $productos = ProductoModel::where('estado', 1)->get();

        return view('compra', compact('compras', 'proveedores', 'productos', 'fechaDesde', 'fechaHasta'));
    }

    // ==========================================
    // GUARDAR COMPRA Y DETALLE
    // ==========================================
    public function guardar(Request $request)
    {
        $request->validate([
            'id_proveedor'              => 'required|exists:proveedor,id_proveedor',
            'fecha'                     => 'nullable|date',
            'productos'                 => 'required|array|min:1',
            'productos.*.id_producto'   => 'required|exists:producto,id_producto|distinct',
            'productos.*.cantidad'      => 'required|numeric|min:1',
            'productos.*.precio_compra' => 'nullable|numeric|min:0'
        ], [
            'id_proveedor.required'     => 'Debe seleccionar un proveedor.',
            'productos.required'        => 'Debe agregar al menos un producto a la compra.'
        ]);

        DB::beginTransaction();

        try {
            $idUsuario = session('idusuario', 1);

            $compra = CompraModel::create([
                'id_proveedor'    => $request->id_proveedor,
                'id_usuario'      => $idUsuario,
                'fecha'           => ($request->fecha ? $request->fecha . ' ' : '') . now()->format('H:i:s'),
                'nro_comprobante' => 'COM-' . now()->format('Ymd-His')
            ]);

            foreach ($request->productos as $item) {
                $producto = ProductoModel::lockForUpdate()->findOrFail($item['id_producto']);
                // El costo proviene de la ficha del producto: se actualiza allí antes de registrar la compra.
                $costoCompra = (float) ($producto->costo_compra ?? 0);
                if ($costoCompra <= 0) {
                    throw new \Exception("Defina el costo de compra de '{$producto->nombre}' antes de registrarlo en la compra.");
                }

                DetalleCompraModel::create([
                    'id_compra'      => $compra->id_compra,
                    'id_producto'   => $item['id_producto'],
                    'cantidad'       => $item['cantidad'],
                    'precio_compra'  => $costoCompra
                ]);

                // Incrementar inventario
                $producto->stock += $item['cantidad'];
                $producto->save();
            }

            DB::commit();

            return redirect()->route('compras')
                ->with('mensaje', 'Compra registrada correctamente y stock actualizado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }

    public function comprobante($id)
    {
        $compra = CompraModel::with(['proveedor', 'usuario', 'detalles.producto'])->findOrFail($id);
        $empresa = ConfiguracionModel::getEmpresa();
        return view('comprobante_compra', compact('compra', 'empresa'));
    }

    public function imprimirListado(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde') ?: now()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: $fechaDesde;
        $query = CompraModel::with(['proveedor', 'usuario', 'detalles.producto'])
            ->whereBetween('fecha', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])->orderByDesc('fecha');
        if ($request->filled('id_proveedor')) $query->where('id_proveedor', $request->id_proveedor);
        $operaciones = $query->get();
        $empresa = ConfiguracionModel::getEmpresa(); $titulo = 'Listado de compras';
        $total = $operaciones->sum(fn ($compra) => $compra->detalles->sum(fn ($detalle) => $detalle->cantidad * $detalle->precio_compra));
        return view('comprobante_listado', compact('operaciones', 'empresa', 'titulo', 'fechaDesde', 'fechaHasta', 'total'));
    }

    // ==========================================
    // HISTORIAL DE COMPRAS (CON FILTROS)
    // ==========================================
    public function historial(Request $request)
    {
        $query = CompraModel::with([
            'proveedor',
            'usuario',
            'detalles.producto'
        ])->orderBy('fecha', 'desc');

        if ($request->filled('id_proveedor')) {
            $query->where('id_proveedor', $request->id_proveedor);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $compras = $query->get();
        $proveedores = ProveedorModel::all();

        return view('historial_compras', compact('compras', 'proveedores'));
    }
}
