<?php

namespace App\Http\Controllers;

use App\Models\ProductoModel;
use App\Models\TipoProductoModel;
use App\Models\ProveedorModel;
use App\Models\DetalleCompraModel;
use App\Models\DetalleVentaModel;
use App\Models\MermaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = ProductoModel::with(['tipoProducto', 'proveedor'])
            ->orderBy('id_producto', 'desc')
            ->get();
            
        $tipos = TipoProductoModel::all();
        $proveedores = ProveedorModel::where('estado', 1)->orderBy('nombre_empresa', 'asc')->get();

        return view('producto', compact('productos', 'tipos', 'proveedores'));
    }

    public function guardar(Request $request)
    {
        abort_unless(session('rol') == 1, 403);
        $request->validate([
            'nombre'             => 'required|string|max:150',
            'descripcion'        => 'required|string',
            'marca'              => 'required|string|max:100',
            'precio_menor'       => 'required|numeric|gt:precio_mayor|min:0.01',
            'precio_mayor'       => 'required|numeric|min:0.01',
            'costo_compra'       => 'nullable|numeric|min:0',
            'cantidad_min_mayor' => 'required|integer|min:2',
            'stock'              => 'required|integer|min:0',
            'stock_minimo'       => 'required|integer|min:1',
            'id_tipo_producto'   => 'required|exists:tipo_producto,id_tipo_producto',
            'id_proveedor'       => 'nullable|exists:proveedor,id_proveedor',
            'imagen'             => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'estado'             => 'required|in:0,1'
        ], [
            'precio_menor.gt'         => 'El precio al por menor debe ser estrictamente mayor que el precio al por mayor.',
            'cantidad_min_mayor.min'  => 'La cantidad mínima para precio por mayor debe ser al menos 2.',
            'id_tipo_producto.exists' => 'El tipo de producto seleccionado no es válido.',
            'imagen.required'         => 'Debe seleccionar una fotografía para el producto.',
            'imagen.image'            => 'El archivo seleccionado debe ser una imagen válida.'
        ]);

        $nombreImagen = null;
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            $rutaDestino = public_path('img/productos');
            if (!File::exists($rutaDestino)) {
                File::makeDirectory($rutaDestino, 0755, true);
            }
            $imagen->move($rutaDestino, $nombreImagen);
        }

        ProductoModel::create([
            'nombre'             => $request->nombre,
            'descripcion'        => $request->descripcion,
            'imagen'             => $nombreImagen,
            'marca'              => $request->marca,
            'precio_menor'       => $request->precio_menor,
            'precio_mayor'       => $request->precio_mayor,
            'costo_compra'       => $request->costo_compra ?? 0,
            'cantidad_min_mayor' => $request->cantidad_min_mayor,
            'stock'              => $request->stock,
            'stock_minimo'       => $request->stock_minimo ?? 5,
            'id_tipo_producto'   => $request->id_tipo_producto,
            'id_proveedor'       => $request->id_proveedor,
            'estado'             => $request->estado
        ]);

        return redirect()->back()->with('mensaje', 'Producto registrado correctamente con su fotografía e información técnica.');
    }

    public function actualizar(Request $request)
    {
        abort_unless(session('rol') == 1, 403);
        $request->validate([
            'id_producto'        => 'required|exists:producto,id_producto',
            'nombre'             => 'required|string|max:150',
            'descripcion'        => 'required|string',
            'marca'              => 'required|string|max:100',
            'precio_menor'       => 'required|numeric|gt:precio_mayor|min:0.01',
            'precio_mayor'       => 'required|numeric|min:0.01',
            'costo_compra'       => 'nullable|numeric|min:0',
            'cantidad_min_mayor' => 'required|integer|min:2',
            'stock'              => 'required|integer|min:0',
            'stock_minimo'       => 'required|integer|min:1',
            'id_tipo_producto'   => 'required|exists:tipo_producto,id_tipo_producto',
            'id_proveedor'       => 'nullable|exists:proveedor,id_proveedor',
            'imagen'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'estado'             => 'required|in:0,1'
        ], [
            'precio_menor.gt'         => 'El precio al por menor debe ser estrictamente mayor que el precio al por mayor.',
            'cantidad_min_mayor.min'  => 'La cantidad mínima para precio por mayor debe ser al menos 2.',
            'stock.min'               => 'El stock no puede ser negativo.',
            'id_tipo_producto.exists' => 'El tipo de producto seleccionado no es válido.'
        ]);

        $producto = ProductoModel::findOrFail($request->id_producto);

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            $rutaDestino = public_path('img/productos');
            if (!File::exists($rutaDestino)) {
                File::makeDirectory($rutaDestino, 0755, true);
            }
            $imagen->move($rutaDestino, $nombreImagen);

            // Eliminar imagen anterior si es personalizada (no numérica básica)
            if ($producto->imagen && !preg_match('/^[0-9]+\.jpg$/', $producto->imagen)) {
                if (File::exists(public_path('img/productos/' . $producto->imagen))) {
                    File::delete(public_path('img/productos/' . $producto->imagen));
                }
            }
            $producto->imagen = $nombreImagen;
        }

        $producto->nombre             = $request->nombre;
        $producto->descripcion        = $request->descripcion;
        $producto->marca              = $request->marca;
        $producto->precio_menor       = $request->precio_menor;
        $producto->precio_mayor       = $request->precio_mayor;
        $producto->costo_compra       = $request->costo_compra ?? $producto->costo_compra;
        $producto->cantidad_min_mayor = $request->cantidad_min_mayor;
        $producto->stock              = $request->stock;
        $producto->stock_minimo       = $request->stock_minimo ?? 5;
        $producto->id_tipo_producto   = $request->id_tipo_producto;
        $producto->id_proveedor       = $request->id_proveedor;
        $producto->estado             = $request->estado;
        $producto->save();

        return redirect()->back()->with('mensaje', 'Producto actualizado correctamente.');
    }

    // Edición rápida desde una compra: conserva el formulario de compra abierto.
    public function actualizarDesdeCompra(Request $request)
    {
        abort_unless(session('rol') == 1, 403);
        $request->validate([
            'id_producto' => 'required|exists:producto,id_producto',
            'nombre' => 'required|string|max:150',
            'marca' => 'nullable|string|max:100',
            'costo_compra' => 'required|numeric|min:0.01',
            'precio_mayor' => 'required|numeric|min:0.01',
            'precio_menor' => 'required|numeric|gt:precio_mayor',
            'cantidad_min_mayor' => 'required|integer|min:2'
        ]);

        $producto = ProductoModel::findOrFail($request->id_producto);
        $producto->update($request->only(['nombre', 'marca', 'costo_compra', 'precio_mayor', 'precio_menor', 'cantidad_min_mayor']));

        return response()->json(['mensaje' => 'Producto actualizado correctamente.', 'producto' => $producto->fresh()]);
    }

    public function eliminar($id)
    {
        abort_unless(session('rol') == 1, 403);
        $producto = ProductoModel::findOrFail($id);
        $producto->estado = 0;
        $producto->save();
        return redirect()->back()->with('mensaje', 'Producto desactivado correctamente. Su historial se conserva.');
    }

    // HU14: CONSULTA DE KARDEX DE PRODUCTO
    public function kardex(Request $request, $id)
    {
        $filtros = $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);
        $producto = ProductoModel::with(['tipoProducto', 'proveedor'])->findOrFail($id);

        // 1. Obtener Entradas por Compras Locales
        $compras = DetalleCompraModel::with('compra.usuario')
            ->where('id_producto', $id)
            ->whereHas('compra')
            ->get()
            ->map(function ($c) {
                return [
                    'fecha'       => $c->compra->fecha,
                    'tipo'        => 'ENTRADA',
                    'operacion'   => 'Compra Local (N° ' . ($c->compra->nro_comprobante ?? $c->id_compra) . ')',
                    'usuario'     => $c->compra->usuario->nombre ?? 'Admin',
                    'cantidad'    => $c->cantidad,
                    'precio_unit' => $c->precio_compra,
                    'total'       => $c->cantidad * $c->precio_compra,
                    'clase'       => 'text-success'
                ];
            });

        // 2. Obtener Salidas por Ventas
        $ventas = DetalleVentaModel::with(['venta.usuario', 'venta.cliente.unipersonal', 'venta.cliente.institucional'])
            ->where('id_producto', $id)
            ->whereHas('venta', function ($q) {
                $q->where('estado', '!=', 'Cancelado');
            })
            ->get()
            ->map(function ($v) {
                $clienteNom = 'Cliente';
                if ($v->venta->cliente) {
                    $clienteNom = $v->venta->cliente->tipo_registro === 'Unipersonal' 
                        ? ($v->venta->cliente->unipersonal->nombre ?? 'Cliente') 
                        : ($v->venta->cliente->institucional->razon_social ?? 'Empresa');
                }
                return [
                    'fecha'       => $v->venta->fecha,
                    'tipo'        => 'SALIDA',
                    'operacion'   => 'Venta N° ' . $v->venta->id_venta . ' (' . $clienteNom . ')',
                    'usuario'     => $v->venta->usuario->nombre ?? 'Vendedor',
                    'cantidad'    => $v->cantidad,
                    'precio_unit' => $v->precio_aplicado,
                    'total'       => $v->cantidad * $v->precio_aplicado,
                    'clase'       => 'text-danger'
                ];
            });

        // 3. Obtener Salidas por Mermas / Daños
        $mermas = MermaModel::with('usuario')
            ->where('id_producto', $id)
            ->get()
            ->map(function ($m) {
                return [
                    'fecha'       => $m->fecha,
                    'tipo'        => 'MERMA/BAJA',
                    'operacion'   => 'Baja por: ' . $m->motivo . ' - ' . $m->descripcion_dano,
                    'usuario'     => $m->usuario->nombre ?? 'Admin',
                    'cantidad'    => $m->cantidad,
                    'precio_unit' => 0,
                    'total'       => 0,
                    'clase'       => 'text-warning'
                ];
            });

        // Unificar movimientos y ordenar cronológicamente
        $movimientos = $compras->concat($ventas)->concat($mermas)->sortBy('fecha')->values();

        // Calcular saldo dinámico paso a paso
        $saldoAcumulado = 0;
        $kardexFilas = [];

        foreach ($movimientos as $mov) {
            if ($mov['tipo'] === 'ENTRADA') {
                $saldoAcumulado += $mov['cantidad'];
            } else {
                $saldoAcumulado -= $mov['cantidad'];
            }

            $mov['saldo_resultante'] = $saldoAcumulado;
            $kardexFilas[] = $mov;
        }

        $kardexFilas = array_values(array_filter($kardexFilas, function ($fila) use ($filtros) {
            $fechaMovimiento = \Illuminate\Support\Carbon::parse($fila['fecha'])->toDateString();
            $desde = $filtros['desde'] ?? null;
            $hasta = $filtros['hasta'] ?? null;
            return (!$desde || $fechaMovimiento >= $desde)
                && (!$hasta || $fechaMovimiento <= $hasta);
        }));

        return view('kardex_producto', compact('producto', 'kardexFilas'));
    }
}
