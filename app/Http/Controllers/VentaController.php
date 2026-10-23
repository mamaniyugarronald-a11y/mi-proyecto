<?php

namespace App\Http\Controllers;

use App\Models\VentaModel;
use App\Models\DetalleVentaModel;
use App\Models\DetallePedidoModel;
use App\Models\ProductoModel;
use App\Models\ClienteModel;
use App\Models\ConfiguracionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    // ==========================================
    // LISTADO DE VENTAS & CONTROL DE DESPACHO (HU19, HU23)
    // ==========================================
    public function index(Request $request)
    {
        return redirect()->route('ventas.directas', $request->query());
    }

    public function directas(Request $request)
    {
        return $this->listadoPorTipo($request, 'directas');
    }

    public function reservas(Request $request)
    {
        return $this->listadoPorTipo($request, 'reservas');
    }

    private function listadoPorTipo(Request $request, string $tipoListado)
    {
        $query = VentaModel::with([
            'cliente.unipersonal',
            'cliente.institucional',
            'usuario',
            'detalles.producto',
            'detallesPedido.producto'
        ])->orderBy('fecha', 'desc');

        if (session('rol') == 2) {
            $query->where('id_usuario', session('idusuario'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('estado_despacho')) {
            $query->where('estado_despacho', $request->estado_despacho);
        }

        if ($tipoListado === 'directas') $query->where('tipo_venta', 'Venta Directa');
        else $query->whereIn('tipo_venta', ['Reserva', 'Pedido']);

        // Por defecto se consulta el día completo. Cuando el usuario cambia las fechas,
        // el mismo rango se aplica sin conservar filtros anteriores.
        $fechaDesde = $request->input('fecha_desde') ?: now()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: $fechaDesde;
        $query->whereBetween('fecha', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        $ventas = $query->get();
        return view('venta', compact('ventas', 'tipoListado'));
    }

    // ==========================================
    // INTERFAZ DE PUNTO DE VENTA (POS - HU16, HU19, HU21)
    // ==========================================
    public function pos()
    {
        $clientes = ClienteModel::with(['unipersonal', 'institucional'])->get();
        $productos = ProductoModel::where('estado', 1)
            ->with('tipoProducto')
            ->orderBy('nombre', 'asc')
            ->get();

        $empresa = ConfiguracionModel::getEmpresa();

        return view('venta_pos', compact('clientes', 'productos', 'empresa'));
    }

    // ==========================================
    // REGISTRAR VENTA / PEDIDO (HU19, HU21)
    // ==========================================
    public function guardar(Request $request)
    {
        $request->validate([
            'id_cliente'        => 'required|exists:cliente,id_cliente',
            'es_venta_mayor'    => 'nullable|boolean',
            'promocion'         => 'required|in:Ninguna,Transporte,Precio',
            'tipo_venta'        => 'required|in:Venta Directa,Reserva',
            'metodo_pago'       => 'required|string',
            'descuento'         => 'nullable|numeric|min:0',
            'estado_despacho'   => 'nullable|in:Pendiente,Despachado,Entregado',
            'productos'         => 'required|array|min:1',
            'productos.*.id_producto' => 'required|exists:producto,id_producto',
            'productos.*.cantidad'    => 'required|integer|min:1',
            'productos.*.precio'      => 'required|numeric|min:0.01'
        ], [
            'id_cliente.required' => 'Debe seleccionar un cliente.',
            'productos.required'  => 'Debe agregar al menos un producto a la venta.',
            'productos.*.cantidad.min' => 'La cantidad debe ser mayor o igual a 1.'
        ]);

        DB::beginTransaction();

        try {
            $esVentaDirecta = ($request->tipo_venta === 'Venta Directa');
            // El precio por mayor se determina automáticamente por cada producto y su mínimo configurado.
            $esMayor = collect($request->productos)->contains(function ($item) {
                $producto = ProductoModel::find($item['id_producto']);
                return $producto && $item['cantidad'] >= $producto->cantidad_min_mayor;
            });
            // Ambas modalidades pueden tener transporte. Toda operación con transporte
            // queda pendiente hasta que el despacho se marque como entregado.
            $requiereDespacho = $request->promocion === 'Transporte';
            // La columna existente no admite NULL, por ello una operación sin transporte
            // queda marcada como entregada aunque la interfaz la muestre como no aplicable.
            $estadoDespacho = $requiereDespacho
                ? ($esVentaDirecta ? 'Pendiente' : ($request->estado_despacho ?? 'Pendiente'))
                : 'Entregado';
            $ventaFinalizada = $esVentaDirecta && !$requiereDespacho;

            // 1. Validar Stock para todos los productos si es Venta Directa
            if ($ventaFinalizada) {
                foreach ($request->productos as $item) {
                    $producto = ProductoModel::lockForUpdate()->find($item['id_producto']);
                    if (!$producto) {
                        throw new \Exception("El producto solicitado no existe.");
                    }
                    if ($producto->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock}, Solicitado: {$item['cantidad']}.");
                    }
                }
            }

            // 2. Crear Venta con Descuento y Método de Pago (HU21)
            $venta = VentaModel::create([
                'id_cliente'       => $request->id_cliente,
                'id_usuario'       => session('idusuario', 1),
                'fecha'            => now(),
                'tipo_comprobante' => 'Factura',
                'tipo_cliente'     => $esMayor ? 'Mayor' : 'Menor',
                'es_venta_mayor'   => $esMayor,
                'promocion'        => $request->promocion,
                'descuento'        => $request->descuento ?? 0,
                'metodo_pago'      => $request->metodo_pago,
                'estado'           => $ventaFinalizada ? 'Completado' : 'Pendiente',
                'estado_despacho'  => $estadoDespacho,
                'guia_despacho'    => $request->guia_despacho,
                'fecha_despacho'   => ($estadoDespacho === 'Despachado') ? now() : null,
                'fecha_entrega'    => ($estadoDespacho === 'Entregado') ? now() : null,
                'tipo_venta'       => $request->tipo_venta
            ]);

            // 3. Crear detalles y actualizar stock
            foreach ($request->productos as $item) {
                $producto = ProductoModel::lockForUpdate()->findOrFail($item['id_producto']);

                $precioEsperado = $item['cantidad'] >= $producto->cantidad_min_mayor
                    ? $producto->precio_mayor
                    : $producto->precio_menor;
                if ((float) $item['precio'] !== (float) $precioEsperado) {
                    throw new \Exception("El precio de '{$producto->nombre}' no corresponde a la modalidad seleccionada.");
                }

                if ($ventaFinalizada) {
                    DetalleVentaModel::create([
                        'id_venta'        => $venta->id_venta,
                        'id_producto'     => $item['id_producto'],
                        'cantidad'        => $item['cantidad'],
                        'precio_aplicado' => $item['precio']
                    ]);

                    // Descontar inventario
                    $producto->stock -= $item['cantidad'];
                    $producto->save();
                } else {
                    DetallePedidoModel::create([
                        'id_venta'        => $venta->id_venta,
                        'id_producto'     => $item['id_producto'],
                        'cantidad'        => $item['cantidad'],
                        'precio_estimado' => $item['precio']
                    ]);
                }
            }

            DB::commit();

            $mensaje = $esVentaDirecta
                ? ($requiereDespacho
                    ? 'Venta directa registrada como Pendiente. Se contabilizará al confirmar la entrega. N° Transacción: #' . $venta->id_venta
                    : 'Venta directa registrada con éxito. N° Transacción: #' . $venta->id_venta)
                : 'Reserva registrada como Pendiente. Debe confirmarse y completarse para contabilizarse.';
            return redirect()->route($esVentaDirecta ? 'ventas.directas' : 'ventas.reservas')
                ->with('mensaje', $mensaje)
                ->with('id_venta_reciente', $venta->id_venta);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    // ==========================================
    // COMPROBANTE / NOTA DE VENTA IMPRIMIBLE (HU20, HU30)
    // ==========================================
    public function comprobante($id)
    {
        $venta = VentaModel::with([
            'cliente.unipersonal',
            'cliente.institucional',
            'usuario',
            'detalles.producto',
            'detallesPedido.producto'
        ])->findOrFail($id);

        if (session('rol') == 2 && $venta->id_usuario != session('idusuario')) abort(403);
        $empresa = ConfiguracionModel::getEmpresa();

        return view('comprobante_venta', compact('venta', 'empresa'));
    }

    public function imprimirListado(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde') ?: now()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: $fechaDesde;
        $query = VentaModel::with(['cliente.unipersonal', 'cliente.institucional', 'usuario', 'detalles.producto', 'detallesPedido.producto'])
            ->whereBetween('fecha', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])->orderByDesc('fecha');
        foreach (['estado', 'estado_despacho'] as $campo) if ($request->filled($campo)) $query->where($campo, $request->$campo);
        if ($request->input('tipo_venta') === 'Venta Directa') $query->where('tipo_venta', 'Venta Directa');
        elseif ($request->input('tipo_venta') === 'Reserva') $query->whereIn('tipo_venta', ['Reserva', 'Pedido']);
        if (session('rol') == 2) $query->where('id_usuario', session('idusuario'));
        $operaciones = $query->get();
        $empresa = ConfiguracionModel::getEmpresa(); $titulo = 'Listado de ventas';
        $total = $operaciones->where('estado', 'Completado')->sum('total');
        return view('comprobante_listado', compact('operaciones', 'empresa', 'titulo', 'fechaDesde', 'fechaHasta', 'total'));
    }

    // ==========================================
    // CAMBIO DE ESTADO DE DESPACHO (HU23)
    // ==========================================
    public function actualizarDespacho(Request $request, $id)
    {
        $request->validate([
            'estado_despacho' => 'required|in:Pendiente,Despachado,Entregado',
            'guia_despacho'   => 'nullable|string|max:100'
        ]);

        DB::beginTransaction();
        try {
        $venta = VentaModel::with('detallesPedido.producto')->findOrFail($id);
        if ($venta->promocion !== 'Transporte') {
            return redirect()->back()->with('error', 'Solo las operaciones con promoción de transporte requieren actualización de despacho.');
        }
        if ($venta->tipo_venta === 'Reserva' && $venta->estado === 'Pendiente') {
            return redirect()->back()->with('error', 'Primero debe confirmar la reserva antes de gestionar su despacho.');
        }
        $venta->estado_despacho = $request->estado_despacho;
        $venta->guia_despacho   = $request->guia_despacho;

        if ($request->estado_despacho === 'Despachado' && !$venta->fecha_despacho) {
            $venta->fecha_despacho = now();
        } elseif ($request->estado_despacho === 'Entregado' && !$venta->fecha_entrega) {
            $venta->fecha_entrega = now();
            // Una venta directa con transporte se contabiliza recién al ser entregada.
            if ($venta->tipo_venta === 'Venta Directa' && $venta->estado === 'Pendiente') {
                foreach ($venta->detallesPedido as $detalle) {
                    $producto = ProductoModel::lockForUpdate()->findOrFail($detalle->id_producto);
                    if ($producto->stock < $detalle->cantidad) {
                        throw new \Exception("Stock insuficiente para '{$producto->nombre}' al completar la entrega.");
                    }
                    DetalleVentaModel::create([
                        'id_venta' => $venta->id_venta,
                        'id_producto' => $detalle->id_producto,
                        'cantidad' => $detalle->cantidad,
                        'precio_aplicado' => $detalle->precio_estimado,
                    ]);
                    $producto->stock -= $detalle->cantidad;
                    $producto->save();
                }
                DetallePedidoModel::where('id_venta', $venta->id_venta)->delete();
                $venta->estado = 'Completado';
            }
        }

        $venta->save();
        DB::commit();
        return redirect()->back()->with('mensaje', 'Estado de despacho para la venta #' . $id . ' actualizado a: ' . $request->estado_despacho);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'No se pudo completar la entrega: ' . $e->getMessage());
        }
    }

    // Confirma una reserva, sin afectar aún el inventario ni los ingresos.
    public function confirmarReserva($id)
    {
        $venta = VentaModel::findOrFail($id);
        if ($venta->tipo_venta !== 'Reserva' || $venta->estado !== 'Pendiente') {
            return redirect()->back()->with('error', 'Solo se pueden confirmar reservas en estado Pendiente.');
        }

        $venta->estado = 'Confirmado';
        $venta->save();

        return redirect()->back()->with('mensaje', 'La reserva #' . $id . ' fue confirmada.');
    }

    // ==========================================
    // ANULAR VENTA (RESTAURAR STOCK)
    // ==========================================
    public function anular($id)
    {
        DB::beginTransaction();

        try {
            $venta = VentaModel::with(['detalles.producto', 'detallesPedido.producto'])->findOrFail($id);

            if ($venta->estado === 'Cancelado') {
                return redirect()->back()->with('error', 'Esta venta ya se encuentra anulada.');
            }

            // Toda operación completada ya descontó inventario, incluso si nació como reserva.
            if ($venta->estado === 'Completado') {
                foreach ($venta->detalles as $detalle) {
                    if ($detalle->producto) {
                        $detalle->producto->stock += $detalle->cantidad;
                        $detalle->producto->save();
                    }
                }
            }

            $venta->estado = 'Cancelado';
            $venta->save();

            DB::commit();

            return redirect()->back()->with('mensaje', 'La venta #' . $id . ' fue anulada y el stock fue repuesto.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al anular la venta: ' . $e->getMessage());
        }
    }

    // ==========================================
    // COMPLETAR PEDIDO PENDIENTE
    // ==========================================
    public function completarPedido($id)
    {
        DB::beginTransaction();

        try {
            $venta = VentaModel::with('detallesPedido.producto')->findOrFail($id);

            if (!in_array($venta->tipo_venta, ['Reserva', 'Pedido']) || $venta->estado !== 'Confirmado') {
                return redirect()->back()->with('error', 'Solo se pueden completar reservas en estado Confirmado.');
            }
            if ($venta->promocion === 'Transporte' && $venta->estado_despacho !== 'Entregado') {
                return redirect()->back()->with('error', 'La reserva con transporte debe estar entregada antes de completarse.');
            }

            // Verificar stock
            foreach ($venta->detallesPedido as $dp) {
                if (!$dp->producto || $dp->producto->stock < $dp->cantidad) {
                    throw new \Exception("Stock insuficiente para '{$dp->producto->nombre}'.");
                }
            }

            // Migrar de detalle_pedido a detalle_venta y descontar stock
            foreach ($venta->detallesPedido as $dp) {
                DetalleVentaModel::create([
                    'id_venta'        => $venta->id_venta,
                    'id_producto'     => $dp->id_producto,
                    'cantidad'        => $dp->cantidad,
                    'precio_aplicado' => $dp->precio_estimado
                ]);

                $dp->producto->stock -= $dp->cantidad;
                $dp->producto->save();
            }

            // Eliminar los detalles de pedido anteriores
            DetallePedidoModel::where('id_venta', $venta->id_venta)->delete();

            $venta->estado = 'Completado';
            $venta->save();

            DB::commit();

            return redirect()->back()->with('mensaje', 'La reserva #' . $id . ' fue completada; el stock y los ingresos fueron actualizados.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al completar el pedido: ' . $e->getMessage());
        }
    }
}
