<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ClienteModel;
use App\Models\ClienteUnipersonalModel;
use App\Models\ClienteInstitucionalModel;

class ClienteController extends Controller
{
     //==========================
    // LISTAR CLIENTES
    //==========================
    public function index()
    {

        $clientes = ClienteModel::with(['unipersonal','institucional'])->get();

        return view('cliente', compact('clientes'));

    }

    //==========================
    // GUARDAR CLIENTE
    //==========================
    //==========================
// GUARDAR CLIENTE
//==========================
public function guardar(Request $request)
{
    // Validaciones apuntando a las tablas en singular
    $rules = [
        'tipo_registro' => 'required|in:Unipersonal,Institucional',
        'telefono'      => 'required|string|max:20|regex:/^[0-9+\-\s()]{7,20}$/',
        'email'         => 'required|email|unique:cliente,email', // Se cambió 'clientes' por 'cliente'
        'direccion'     => 'required|string|max:255',
    ];

    if ($request->tipo_registro === 'Unipersonal') {
        $rules['nombre']   = 'required|string|max:100';
        $rules['apellido'] = 'required|string|max:100';
        $rules['ci']       = 'required|string|max:50|unique:cliente_unipersonal,ci';
    } elseif ($request->tipo_registro === 'Institucional') {
        $rules['nombre_empresa']  = 'required|string|max:150';
        $rules['nombre_empleado'] = 'required|string|max:150';
        $rules['cargo_area']      = 'required|string|max:150';
        $rules['nit']             = 'required|string|max:50|unique:cliente_institucional,nit';
    }

    $messages = [
        'email.unique' => 'El correo electrónico ya se encuentra registrado en el sistema.',
        'ci.unique'    => 'El número de C.I. ingresado ya corresponde a un cliente registrado.',
        'nit.unique'   => 'El NIT ingresado ya corresponde a una institución registrada.',
    ];

    $validator = \Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        if ($request->expectsJson()) return response()->json(['message' => $validator->errors()->first()], 422);
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('modal_error', 'modalNuevo');
    }

    // Insertar registros con Transacción
    DB::beginTransaction();

    try {
        // 1. Guardar en tabla principal 'cliente'
        $cliente = new ClienteModel();
        $cliente->tipo_registro = $request->tipo_registro;
        $cliente->telefono      = $request->telefono;
        $cliente->email         = $request->email;
        $cliente->direccion     = $request->direccion;
        $cliente->fecha_registro = now();
        $cliente->save();

        // 2. Guardar según tipo de cliente
        if ($request->tipo_registro === 'Unipersonal') {
            $uni = new ClienteUnipersonalModel();
            $uni->id_cliente = $cliente->id_cliente;
            $uni->nombre     = $request->nombre;
            $uni->apellido   = $request->apellido;
            $uni->ci         = $request->ci;
            $uni->save();
        } elseif ($request->tipo_registro === 'Institucional') {
            $ins = new ClienteInstitucionalModel();
            $ins->id_cliente      = $cliente->id_cliente;
            $ins->nombre_empresa  = $request->nombre_empresa;
            $ins->nombre_empleado = $request->nombre_empleado;
            $ins->cargo_area      = $request->cargo_area;
            $ins->razon_social    = $request->nombre_empresa;
            $ins->nit             = $request->nit;
            $ins->contacto_nombre = $request->nombre_empleado;
            $ins->save();
        }

        DB::commit();

        if ($request->expectsJson()) {
            $cliente->load(['unipersonal', 'institucional']);
            return response()->json(['id' => $cliente->id_cliente, 'nombre' => $cliente->nombre_completo, 'tipo' => $cliente->tipo_registro]);
        }
        return redirect()->back()->with('mensaje', 'Cliente registrado correctamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        if ($request->expectsJson()) return response()->json(['message' => $e->getMessage()], 422);
        return redirect()->back()->withInput()->with('mensaje', 'Error al guardar: ' . $e->getMessage());
    }
}
    //==========================
    // ACTUALIZAR
    //==========================
    public function actualizar(Request $request)
    {

        $request->validate([
            'id_cliente' => 'required|exists:cliente,id_cliente',
            'telefono' => 'required|string|max:20|regex:/^[0-9+\-\s()]{7,20}$/',
            'email' => 'required|email|unique:cliente,email,' . $request->id_cliente . ',id_cliente',
            'direccion' => 'required|string|max:255',
        ]);
        $clienteExistente = ClienteModel::findOrFail($request->id_cliente);
        if ($clienteExistente->tipo_registro === 'Institucional') {
            $request->validate([
                'nombre_empresa' => 'required|string|max:150',
                'nombre_empleado' => 'required|string|max:150',
                'cargo_area' => 'required|string|max:150',
                'nit' => 'required|string|max:50|unique:cliente_institucional,nit,' . $request->id_cliente . ',id_cliente',
            ]);
        } else {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'ci' => 'required|string|max:50|unique:cliente_unipersonal,ci,' . $request->id_cliente . ',id_cliente',
            ]);
        }

        DB::beginTransaction();

        try{

            $cliente = ClienteModel::find($request->id_cliente);

            $cliente->telefono=$request->telefono;
            $cliente->email=$request->email;
            $cliente->direccion=$request->direccion;

            $cliente->save();

            if($cliente->tipo_registro=="Unipersonal"){

                $uni=ClienteUnipersonalModel::find($cliente->id_cliente);

                $uni->nombre=$request->nombre;
                $uni->apellido=$request->apellido;
                $uni->ci=$request->ci;

                $uni->save();

            }

            if($cliente->tipo_registro=="Institucional"){
                $ins=ClienteInstitucionalModel::find($cliente->id_cliente);

                $ins->nombre_empresa=$request->nombre_empresa;
                $ins->nombre_empleado=$request->nombre_empleado;
                $ins->cargo_area=$request->cargo_area;
                $ins->razon_social=$request->nombre_empresa;
                $ins->nit=$request->nit;
                $ins->contacto_nombre=$request->nombre_empleado;

                $ins->save();

            }

            DB::commit();

            return redirect()->back()->with('mensaje','Cliente actualizado');

        }catch(\Exception $e){

            DB::rollBack();

            return redirect()->back()->with('mensaje',$e->getMessage());

        }

    }

    //==========================
    // ELIMINAR
    //==========================
    public function eliminar($id)
    {

        DB::beginTransaction();

        try{

            $cliente=ClienteModel::find($id);

            if($cliente->tipo_registro=="Unipersonal"){

                ClienteUnipersonalModel::where('id_cliente',$id)->delete();

            }

            if($cliente->tipo_registro=="Institucional"){

                ClienteInstitucionalModel::where('id_cliente',$id)->delete();

            }

            ClienteModel::destroy($id);

            DB::commit();

            return redirect()->back()->with('mensaje','Cliente eliminado');

        }catch(\Exception $e){

            DB::rollBack();

            return redirect()->back()->with('mensaje',$e->getMessage());

        }

    }

}
