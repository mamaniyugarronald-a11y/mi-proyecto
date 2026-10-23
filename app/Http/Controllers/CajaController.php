<?php

namespace App\Http\Controllers;

use App\Models\CierreCajaModel;
use App\Models\VentaModel;
use App\Models\ConfiguracionModel;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index(Request $request)
    {
        $fechaConsulta = $request->input('fecha', date('Y-m-d'));

        // Obtener ventas del día
        $ventasDia = VentaModel::with(['cliente.unipersonal', 'cliente.institucional', 'usuario', 'detalles'])
            ->whereDate('fecha', $fechaConsulta)
            ->where('estado', '!=', 'Cancelado')
            ->get();

        $totalEfectivo = $ventasDia->where('metodo_pago', 'Efectivo')->sum('total');
        $totalTransferenciaQR = $ventasDia->whereIn('metodo_pago', ['Transferencia / QR', 'Mixto'])->sum('total');
        $totalGeneral = $totalEfectivo + $totalTransferenciaQR;
        $totalVentasConteo = $ventasDia->count();

        // Verificar si ya fue cerrada
        $cierreExistente = CierreCajaModel::with('usuario')->whereDate('fecha_cierre', $fechaConsulta)->first();

        // Historial de cierres de caja anteriores
        $historialCierres = CierreCajaModel::with('usuario')
            ->orderBy('fecha_cierre', 'desc')
            ->take(15)
            ->get();

        return view('cierre_caja', compact(
            'fechaConsulta',
            'ventasDia',
            'totalEfectivo',
            'totalTransferenciaQR',
            'totalGeneral',
            'totalVentasConteo',
            'cierreExistente',
            'historialCierres'
        ));
    }

    public function cerrar(Request $request)
    {
        $request->validate([
            'fecha_cierre' => 'required|date',
            'observaciones'=> 'nullable|string'
        ]);

        $fecha = $request->fecha_cierre;

        $ventasDia = VentaModel::whereDate('fecha', $fecha)
            ->where('estado', '!=', 'Cancelado')
            ->get();

        $totalEfectivo = $ventasDia->where('metodo_pago', 'Efectivo')->sum('total');
        $totalTransferenciaQR = $ventasDia->whereIn('metodo_pago', ['Transferencia / QR', 'Mixto'])->sum('total');
        $totalGeneral = $totalEfectivo + $totalTransferenciaQR;
        $totalConteo = $ventasDia->count();

        $cierre = CierreCajaModel::updateOrCreate(
            ['fecha_cierre' => $fecha],
            [
                'id_usuario'            => session('idusuario', 1),
                'total_efectivo'        => $totalEfectivo,
                'total_transferencia_qr'=> $totalTransferenciaQR,
                'total_general'         => $totalGeneral,
                'total_ventas_conteo'   => $totalConteo,
                'observaciones'         => $request->observaciones,
                'fecha_registro'        => now()
            ]
        );

        return redirect()->route('caja', ['fecha' => $fecha])
            ->with('mensaje', 'Cierre de caja para el día ' . $fecha . ' guardado exitosamente.');
    }

    public function imprimir($id)
    {
        $cierre = CierreCajaModel::with('usuario')->findOrFail($id);
        $empresa = ConfiguracionModel::getEmpresa();

        $ventas = VentaModel::with(['cliente.unipersonal', 'cliente.institucional', 'usuario', 'detalles'])
            ->whereDate('fecha', $cierre->fecha_cierre)
            ->where('estado', '!=', 'Cancelado')
            ->get();

        return view('cierre_caja_imprimir', compact('cierre', 'empresa', 'ventas'));
    }
}
