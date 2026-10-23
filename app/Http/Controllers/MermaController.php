<?php

namespace App\Http\Controllers;

use App\Models\MermaModel;
use App\Models\ProductoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MermaController extends Controller
{
    public function index()
    {
        $mermas = MermaModel::with(['producto.tipoProducto', 'usuario'])
            ->orderBy('id_merma', 'desc')
            ->get();

        $productos = ProductoModel::where('estado', 1)->where('stock', '>', 0)->orderBy('nombre', 'asc')->get();

        return view('merma', compact('mermas', 'productos'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'id_producto'       => 'required|exists:producto,id_producto',
            'cantidad'          => 'required|integer|min:1',
            'motivo'            => 'required|in:Dañado en importación,Rotura en almacén,Defecto de fábrica,Devolución de cliente,Obsolescencia / Otro',
            'descripcion_dano'  => 'required|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            $producto = ProductoModel::lockForUpdate()->findOrFail($request->id_producto);

            if ($producto->stock < $request->cantidad) {
                return redirect()->back()->withInput()->with('error', "No puede dar de baja {$request->cantidad} unidades. Stock actual disponible: {$producto->stock}.");
            }

            // Descontar inventario disponible
            $producto->stock -= $request->cantidad;
            $producto->save();

            // Guardar registro de merma
            MermaModel::create([
                'id_producto'      => $producto->id_producto,
                'id_usuario'       => session('idusuario', 1),
                'cantidad'         => $request->cantidad,
                'motivo'           => $request->motivo,
                'descripcion_dano' => $request->descripcion_dano,
                'fecha'            => now()
            ]);

            DB::commit();

            return redirect()->route('mermas')
                ->with('mensaje', "Baja de {$request->cantidad} unidad(es) de '{$producto->nombre}' registrada con éxito.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar merma: ' . $e->getMessage());
        }
    }
}
