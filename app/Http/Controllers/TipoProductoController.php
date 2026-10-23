<?php

namespace App\Http\Controllers;

use App\Models\TipoProductoModel;
use Illuminate\Http\Request;

class TipoProductoController extends Controller
{
     // LISTAR TIPOS DE PRODUCTO

    public function index()
    {
        $tipos = TipoProductoModel::all();

        return view('tipo_producto', compact('tipos'));
    }


    // GUARDAR TIPO DE PRODUCTO

    public function guardar(Request $request)
    {
        $request->validateWithBag('crearTipo', [

            'nombre_tipo' => 'required|string|max:100|unique:tipo_producto,nombre_tipo',
            'descripcion' => 'nullable|string|max:255'

        ]);


        TipoProductoModel::create([

            'nombre_tipo' => $request->nombre_tipo,

            'descripcion' => $request->descripcion

        ]);


        return redirect()
            ->back()
            ->with(
                'mensaje',
                'Tipo de producto registrado correctamente'
            );
    }


    // ACTUALIZAR TIPO DE PRODUCTO

    public function actualizar(Request $request)
    {
        $request->validate([
            'id_tipo_producto' => 'required|exists:tipo_producto,id_tipo_producto',
            'nombre_tipo' => 'required|string|max:100|unique:tipo_producto,nombre_tipo,' . $request->id_tipo_producto . ',id_tipo_producto',
            'descripcion' => 'nullable|string|max:255'
        ]);
        $tipo = TipoProductoModel::findOrFail($request->id_tipo_producto);


        $tipo->nombre_tipo =
            $request->nombre_tipo;

        $tipo->descripcion =
            $request->descripcion;


        $tipo->save();


        return redirect()
            ->back()
            ->with(
                'mensaje',
                'Tipo de producto actualizado correctamente'
            );
    }


    // ELIMINAR TIPO DE PRODUCTO

    public function eliminar($id)
    {
        $tipo = TipoProductoModel::findOrFail($id);
        if ($tipo->productos()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar una categoría que tiene productos asociados.');
        }
        $tipo->delete();


        return redirect()
            ->back()
            ->with(
                'mensaje',
                'Tipo de producto eliminado correctamente'
            );
    }
}
