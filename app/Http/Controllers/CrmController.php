<?php

namespace App\Http\Controllers;

use App\Models\ConsultaClienteModel;
use App\Models\ClienteModel;
use App\Models\ProductoModel;
use App\Models\VentaModel;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    // HU17: GESTIÓN DE CONSULTAS Y COTIZACIONES
    public function index(Request $request)
    {
        $query = ConsultaClienteModel::with('producto')->orderBy('id_consulta', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('canal')) {
            $query->where('canal', $request->canal);
        }

        $consultas = $query->get();
        $productos = ProductoModel::where('estado', 1)->orderBy('nombre', 'asc')->get();

        return view('crm_consultas', compact('consultas', 'productos'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre_cliente'   => 'required|string|max:150',
            'telefono'         => 'required|string|max:50',
            'canal'            => 'required|in:WhatsApp,Facebook,Presencial,Llamada',
            'id_producto'      => 'nullable|exists:producto,id_producto',
            'mensaje_solicitud'=> 'required|string',
            'monto_cotizado'   => 'nullable|numeric|min:0'
        ]);

        ConsultaClienteModel::create([
            'nombre_cliente'    => $request->nombre_cliente,
            'telefono'          => $request->telefono,
            'canal'             => $request->canal,
            'id_producto'       => $request->id_producto,
            'mensaje_solicitud' => $request->mensaje_solicitud,
            'monto_cotizado'    => $request->monto_cotizado,
            'estado'            => 'Pendiente',
            'fecha'             => now()
        ]);

        return redirect()->back()->with('mensaje', 'Consulta comercial / cotización registrada con éxito.');
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Cotizado,Venta Concretada,Descartado'
        ]);

        $consulta = ConsultaClienteModel::findOrFail($id);
        $consulta->estado = $request->estado;
        $consulta->save();

        return redirect()->back()->with('mensaje', 'Estado de la solicitud actualizado.');
    }

    // HU18: HISTORIAL COMERCIAL POR CLIENTE
    public function historialCliente($id)
    {
        $cliente = ClienteModel::with(['unipersonal', 'institucional'])->findOrFail($id);

        $ventas = VentaModel::with(['detalles.producto', 'usuario'])
            ->where('id_cliente', $id)
            ->where('estado', '!=', 'Cancelado')
            ->orderBy('fecha', 'desc')
            ->get();

        $totalGastado = $ventas->sum('total');
        $totalComprasCount = $ventas->count();
        $ticketPromedio = $totalComprasCount > 0 ? ($totalGastado / $totalComprasCount) : 0;

        // Clasificación de fidelidad
        $nivelCliente = 'Cliente Nuevo';
        if ($totalGastado >= 5000) {
            $nivelCliente = 'Cliente VIP Mayorista ⭐⭐⭐';
        } elseif ($totalGastado >= 1500) {
            $nivelCliente = 'Cliente Frecuente ⭐⭐';
        } elseif ($totalComprasCount >= 2) {
            $nivelCliente = 'Cliente Regular ⭐';
        }

        return view('cliente_historial', compact('cliente', 'ventas', 'totalGastado', 'totalComprasCount', 'ticketPromedio', 'nivelCliente'));
    }
}
