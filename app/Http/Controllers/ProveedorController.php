<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProveedorModel;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = ProveedorModel::withCount('productos')
            ->orderBy('id_proveedor', 'desc')
            ->get();

        return view('proveedor', compact('proveedores'));
    }

    // GUARDAR PROVEEDOR (FORMULARIO ESTÁNDAR)
    public function guardar(Request $request)
    {
        $request->validateWithBag('crearProveedor', [
            'nombre_empresa' => 'required|string|max:150|unique:proveedor,nombre_empresa',
            'contacto'       => 'nullable|string|max:100',
            'telefono'       => 'required|string|max:30|regex:/^[0-9+\-\s()]{7,30}$/',
            'email'          => 'nullable|email|max:150',
            'direccion'      => 'nullable|string|max:255',
            'pais'           => 'required|string|max:100',
            'calificacion'   => 'nullable|integer|min:1|max:5',
            'notas'          => 'nullable|string'
        ]);

        ProveedorModel::create([
            'nombre_empresa' => $request->nombre_empresa,
            'contacto'       => $request->contacto ?? 'Sin contacto asignado',
            'telefono'       => $request->telefono,
            'email'          => $request->email,
            'direccion'      => $request->direccion,
            'pais'           => $request->pais,
            'calificacion'   => $request->calificacion ?? 5,
            'notas'          => $request->notas,
            'estado'         => 1
        ]);

        return redirect()
            ->back()
            ->with('mensaje', 'Proveedor registrado correctamente.');
    }

    // GUARDAR PROVEEDOR RÁPIDO (AJAX DESDE MODAL DE NUEVO PRODUCTO)
    public function guardarRapido(Request $request)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:150|unique:proveedor,nombre_empresa',
            'telefono'       => 'required|string|max:30|regex:/^[0-9+\-\s()]{7,30}$/',
            'pais'           => 'required|string|max:100',
            'contacto'       => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:150',
            'calificacion'   => 'nullable|integer|min:1|max:5'
        ]);

        try {
            $proveedor = ProveedorModel::create([
                'nombre_empresa' => $request->nombre_empresa,
                'contacto'       => $request->contacto ?? 'Principal',
                'telefono'       => $request->telefono,
                'email'          => $request->email,
                'direccion'      => $request->direccion ?? 'Internacional / Central',
                'pais'           => $request->pais,
                'calificacion'   => $request->calificacion ?? 5,
                'notas'          => $request->notas ?? 'Registrado desde formulario de producto',
                'estado'         => 1
            ]);

            return response()->json([
                'success'   => true,
                'mensaje'   => '¡Proveedor registrado exitosamente!',
                'proveedor' => [
                    'id_proveedor'   => $proveedor->id_proveedor,
                    'nombre_empresa' => $proveedor->nombre_empresa,
                    'pais'           => $proveedor->pais,
                    'telefono'       => $proveedor->telefono
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al guardar el proveedor: ' . $e->getMessage()
            ], 422);
        }
    }

    // ACTUALIZAR PROVEEDOR
    public function actualizar(Request $request)
    {
        $request->validate([
            'id_proveedor'   => 'required|exists:proveedor,id_proveedor',
            'nombre_empresa' => 'required|string|max:150|unique:proveedor,nombre_empresa,' . $request->id_proveedor . ',id_proveedor',
            'telefono'       => 'required|string|max:30|regex:/^[0-9+\-\s()]{7,30}$/',
            'pais'           => 'required|string|max:100'
        ]);

        $proveedor = ProveedorModel::findOrFail($request->id_proveedor);

        $proveedor->nombre_empresa = $request->nombre_empresa;
        $proveedor->contacto       = $request->contacto;
        $proveedor->telefono       = $request->telefono;
        $proveedor->email          = $request->email;
        $proveedor->direccion      = $request->direccion;
        $proveedor->pais           = $request->pais;
        $proveedor->calificacion   = $request->calificacion ?? $proveedor->calificacion;
        $proveedor->notas          = $request->notas;
        $proveedor->estado         = $request->estado ?? 1;

        $proveedor->save();

        return redirect()
            ->back()
            ->with('mensaje', 'Proveedor actualizado correctamente.');
    }

    // ELIMINAR / DESACTIVAR PROVEEDOR
    public function eliminar($id)
    {
        $proveedor = ProveedorModel::findOrFail($id);
        $proveedor->estado = $proveedor->estado == 1 ? 0 : 1;
        $proveedor->save();

        $accion = $proveedor->estado == 1 ? 'reactivado' : 'desactivado';
        return redirect()
            ->back()
            ->with('mensaje', "Proveedor {$accion} correctamente.");
    }
}
