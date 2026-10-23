<?php

namespace App\Http\Controllers;

use App\Models\UbicacionModel;
use App\Models\DetalleUbicacionModel;
use App\Models\ProductoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UbicacionController extends Controller
{
    public function index()
    {
        abort_unless(in_array((int) session('rol'), [1, 3, 4], true), 403);
        $ubicaciones = UbicacionModel::with('detalles.producto')->get();
        $productos = ProductoModel::where('estado', 1)->orderBy('nombre', 'asc')->get();
        $consultaSolicitudes = DB::table('solicitud_transferencia as s')
            ->join('detalle_ubicacion as d', 'd.id_detalle_ubicacion', '=', 's.id_detalle_destino')
            ->join('ubicacion as destino', 'destino.id_ubicacion', '=', 'd.id_ubicacion')
            ->join('ubicacion as origen', 'origen.id_ubicacion', '=', 's.id_ubicacion_origen')
            ->join('producto as p', 'p.id_producto', '=', 's.id_producto')
            ->select('s.*', 'destino.nombre_ubicacion as nombre_destino', 'origen.nombre_ubicacion as nombre_origen', 'p.nombre as nombre_producto');
        $solicitudesTransferencia = (clone $consultaSolicitudes)->orderByDesc('s.id_solicitud')->get();
        $solicitudes = $solicitudesTransferencia->keyBy('id_detalle_destino');

        return view('ubicacion', compact('ubicaciones', 'productos', 'solicitudes', 'solicitudesTransferencia'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre_ubicacion' => 'required|string|max:150',
            'direccion'        => 'nullable|string|max:255'
        ]);

        UbicacionModel::create([
            'nombre_ubicacion' => $request->nombre_ubicacion,
            'direccion'        => $request->direccion
        ]);

        return redirect()->back()->with('mensaje', 'Ubicación / Almacén registrado correctamente.');
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'id_ubicacion'     => 'required|exists:ubicacion,id_ubicacion',
            'nombre_ubicacion' => 'required|string|max:150',
            'direccion'        => 'nullable|string|max:255'
        ]);

        $ubicacion = UbicacionModel::findOrFail($request->id_ubicacion);
        $ubicacion->update([
            'nombre_ubicacion' => $request->nombre_ubicacion,
            'direccion'        => $request->direccion
        ]);

        return redirect()->back()->with('mensaje', 'Ubicación actualizada correctamente.');
    }

    public function eliminar($id)
    {
        $ubicacion = UbicacionModel::findOrFail($id);
        if (DetalleUbicacionModel::where('id_ubicacion', $id)->where('cantidad', '>', 0)->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar una ubicación con stock asignado. Reasigne el stock primero.');
        }
        
        // Eliminar detalles asociados
        DetalleUbicacionModel::where('id_ubicacion', $id)->delete();
        $ubicacion->delete();

        return redirect()->back()->with('mensaje', 'Ubicación eliminada correctamente.');
    }

    public function asignarStock(Request $request)
    {
        $request->validateWithBag('asignarStock', [
            'id_ubicacion' => 'required|exists:ubicacion,id_ubicacion',
            'id_producto'  => 'required|exists:producto,id_producto',
            'cantidad'     => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
        $producto = ProductoModel::lockForUpdate()->findOrFail($request->id_producto);
        $detalle = DetalleUbicacionModel::where('id_ubicacion', $request->id_ubicacion)
            ->where('id_producto', $request->id_producto)
            ->first();

        $asignadoOtrasUbicaciones = DetalleUbicacionModel::where('id_producto', $request->id_producto)
            ->when($detalle, fn ($query) => $query->where('id_detalle_ubicacion', '!=', $detalle->id_detalle_ubicacion))
            ->sum('cantidad');
        if (($asignadoOtrasUbicaciones + $request->cantidad) > $producto->stock) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors([
                'cantidad' => "No puede asignar {$request->cantidad} unidades. Stock total: {$producto->stock}; ya distribuido en otras ubicaciones: {$asignadoOtrasUbicaciones}."
            ], 'asignarStock');
        }
        if ($detalle) {
            $detalle->cantidad = $request->cantidad;
            $detalle->save();
        } else {
            DetalleUbicacionModel::create([
                'id_ubicacion' => $request->id_ubicacion,
                'id_producto'  => $request->id_producto,
                'cantidad'     => $request->cantidad
            ]);
        }

        DB::commit();
        return redirect()->back()->with('mensaje', 'Distribución de stock en ubicación actualizada.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['cantidad' => 'No se pudo asignar el stock: ' . $e->getMessage()], 'asignarStock');
        }
    }

    public function transferirStock(Request $request)
    {
        $request->validateWithBag('transferirStock', [
            'id_detalle_ubicacion' => 'required|exists:detalle_ubicacion,id_detalle_ubicacion',
            'id_ubicacion_destino' => 'required|exists:ubicacion,id_ubicacion',
            'cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $origen = DetalleUbicacionModel::lockForUpdate()->findOrFail($request->id_detalle_ubicacion);
            if ((int) $origen->id_ubicacion === (int) $request->id_ubicacion_destino) {
                DB::rollBack();
                return redirect()->back()->withInput()->withErrors([
                    'id_ubicacion_destino' => 'Debe seleccionar una ubicación de destino distinta a la ubicación de origen.'
                ], 'transferirStock');
            }
            if ($request->cantidad > $origen->cantidad) {
                DB::rollBack();
                return redirect()->back()->withInput()->withErrors([
                    'cantidad' => "No puede transferir {$request->cantidad} unidades. En esta ubicación solo hay {$origen->cantidad} unidades disponibles."
                ], 'transferirStock');
            }

            $destino = DetalleUbicacionModel::where('id_ubicacion', $request->id_ubicacion_destino)
                ->where('id_producto', $origen->id_producto)
                ->lockForUpdate()
                ->first();

            $origen->cantidad -= $request->cantidad;
            $origen->save();

            if ($destino) {
                $destino->cantidad += $request->cantidad;
                $destino->save();
            } else {
                DetalleUbicacionModel::create([
                    'id_ubicacion' => $request->id_ubicacion_destino,
                    'id_producto' => $origen->id_producto,
                    'cantidad' => $request->cantidad,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('mensaje', 'Stock transferido correctamente entre ubicaciones.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['cantidad' => 'No se pudo transferir el stock: ' . $e->getMessage()], 'transferirStock');
        }
    }

    public function solicitarTransferencia(Request $request)
    {
        abort_unless(in_array((int) session('rol'), [1, 3], true), 403);
        $request->validate([
            'id_detalle_destino' => 'required|exists:detalle_ubicacion,id_detalle_ubicacion',
            'id_ubicacion_origen' => 'required|exists:ubicacion,id_ubicacion',
            'cantidad' => 'required|integer|min:1',
        ]);
        $destino = DetalleUbicacionModel::findOrFail($request->id_detalle_destino);
        if ((int)$destino->id_ubicacion === (int)$request->id_ubicacion_origen) {
            return back()->withErrors(['origen' => 'Elija un almacén distinto a la sucursal solicitante.'], 'solicitarTransferencia');
        }
        if (DB::table('solicitud_transferencia')->where('id_detalle_destino', $destino->id_detalle_ubicacion)->whereIn('estado', ['solicitada', 'transportando'])->exists()) {
            return back()->with('error', 'Este producto ya tiene una solicitud de transferencia en curso.');
        }
        DB::table('solicitud_transferencia')->insert([
            'id_detalle_destino' => $destino->id_detalle_ubicacion,
            'id_ubicacion_origen' => $request->id_ubicacion_origen,
            'id_producto' => $destino->id_producto,
            'cantidad' => $request->cantidad,
            'estado' => 'solicitada',
            'mensaje' => 'Solicitud de transporte pendiente',
        ]);
        return back()->with('mensaje', 'Solicitud de transporte enviada al encargado de almacén.');
    }

    public function marcarCargando($id)
    {
        abort_unless((int) session('rol') === 4, 403);
        $actualizadas = DB::table('solicitud_transferencia')->where('id_solicitud', $id)->where('estado', 'solicitada')->update(['estado' => 'transportando', 'mensaje' => 'Transportando producto']);
        abort_unless($actualizadas, 404);
        return back()->with('mensaje', 'La solicitud fue marcada como: Transportando producto.');
    }

    public function completarTransferencia($id)
    {
        abort_unless(in_array((int) session('rol'), [1, 3], true), 403);
        DB::beginTransaction();
        try {
            $solicitud = DB::table('solicitud_transferencia')->where('id_solicitud', $id)->lockForUpdate()->first();
            abort_unless($solicitud && $solicitud->estado === 'transportando', 404);
            $origen = DetalleUbicacionModel::where('id_ubicacion', $solicitud->id_ubicacion_origen)->where('id_producto', $solicitud->id_producto)->lockForUpdate()->first();
            if (!$origen || $origen->cantidad < $solicitud->cantidad) {
                DB::rollBack();
                return back()->with('error', 'No hay suficiente stock disponible en el almacén para completar el traslado.');
            }
            $destino = DetalleUbicacionModel::where('id_ubicacion', function ($query) use ($solicitud) {
                $query->select('id_ubicacion')->from('detalle_ubicacion')->where('id_detalle_ubicacion', $solicitud->id_detalle_destino);
            })->where('id_producto', $solicitud->id_producto)->lockForUpdate()->first();
            if (!$destino) {
                $destino = DetalleUbicacionModel::create([
                    'id_ubicacion' => DB::table('detalle_ubicacion')->where('id_detalle_ubicacion', $solicitud->id_detalle_destino)->value('id_ubicacion'),
                    'id_producto' => $solicitud->id_producto,
                    'cantidad' => 0,
                ]);
            }
            $origen->cantidad -= $solicitud->cantidad;
            $origen->save();
            $destino->cantidad += $solicitud->cantidad;
            $destino->save();
            DB::table('solicitud_transferencia')->where('id_solicitud', $id)->update(['estado' => 'completada', 'mensaje' => 'Transferencia completada']);
            DB::commit();
            return back()->with('mensaje', 'Transferencia completada y stock actualizado.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
