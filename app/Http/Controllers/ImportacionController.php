<?php

namespace App\Http\Controllers;

use App\Models\ImportacionModel;
use App\Models\DetalleImportacionModel;
use App\Models\SeguimientoImportacionModel;
use App\Models\ProveedorModel;
use App\Models\ProductoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportacionController extends Controller
{
    // LISTA DE IMPORTACIONES (HU7, HU10)
    public function index()
    {
        $importaciones = ImportacionModel::with(['proveedor', 'usuario', 'detalles.producto'])
            ->orderBy('id_importacion', 'desc')
            ->get();

        $proveedores = ProveedorModel::where('estado', 1)->orderBy('nombre_empresa', 'asc')->get();
        $productos = ProductoModel::where('estado', 1)->orderBy('nombre', 'asc')->get();

        return view('importacion', compact('importaciones', 'proveedores', 'productos'));
    }

    // REGISTRAR NUEVA ORDEN DE IMPORTACIÓN (HU7, HU8)
    public function guardar(Request $request)
    {
        $request->validate([
            'id_proveedor'              => 'required|exists:proveedor,id_proveedor',
            'codigo_embarque'           => 'required|string|max:50',
            'fecha_pedido'              => 'required|date',
            'pais_origen'               => 'required|string|max:100',
            'tipo_cambio'               => 'required|numeric|min:1',
            'costo_flete'               => 'nullable|numeric|min:0',
            'costo_aduana_aranceles'    => 'nullable|numeric|min:0',
            'costo_seguro'              => 'nullable|numeric|min:0',
            'otros_gastos'              => 'nullable|numeric|min:0',
            'productos'                 => 'required|array|min:1',
            'productos.*.id_producto'   => 'required|exists:producto,id_producto',
            'productos.*.cantidad'      => 'required|integer|min:1',
            'productos.*.precio_fob_usd'=> 'required|numeric|min:0.01'
        ]);

        DB::beginTransaction();

        try {
            $tipoCambio = (float)$request->tipo_cambio;
            $flete = (float)($request->costo_flete ?? 0);
            $aduana = (float)($request->costo_aduana_aranceles ?? 0);
            $seguro = (float)($request->costo_seguro ?? 0);
            $otros = (float)($request->otros_gastos ?? 0);
            $totalGastosInternacionBs = $flete + $aduana + $seguro + $otros;

            // Calcular monto FOB total en USD y cantidad total de unidades
            $montoFobUsd = 0;
            $cantidadTotalUnidades = 0;
            foreach ($request->productos as $p) {
                $montoFobUsd += ($p['cantidad'] * $p['precio_fob_usd']);
                $cantidadTotalUnidades += $p['cantidad'];
            }

            // Gasto de internación promedio por unidad en Bs.
            $gastoUnitarioBs = $cantidadTotalUnidades > 0 ? ($totalGastosInternacionBs / $cantidadTotalUnidades) : 0;

            $importacion = ImportacionModel::create([
                'id_proveedor'              => $request->id_proveedor,
                'id_usuario'                => session('idusuario', 1),
                'codigo_embarque'           => $request->codigo_embarque,
                'fecha_pedido'              => $request->fecha_pedido . ' ' . date('H:i:s'),
                'estado_logistico'          => 'En tránsito',
                'monto_fob_usd'             => $montoFobUsd,
                'costo_flete'               => $flete,
                'costo_aduana_aranceles'    => $aduana,
                'costo_seguro'              => $seguro,
                'otros_gastos'              => $otros,
                'total_gastos_internacion'  => $totalGastosInternacionBs,
                'tipo_cambio'               => $tipoCambio,
                'pais_origen'               => $request->pais_origen,
                'fecha_llegada_estimada'    => $request->fecha_llegada_estimada,
                'observaciones'             => $request->observaciones
            ]);

            // Guardar detalles de importación con costo prorrateado
            foreach ($request->productos as $p) {
                $fobBs = (float)$p['precio_fob_usd'] * $tipoCambio;
                $costoFinalBs = $fobBs + $gastoUnitarioBs;

                DetalleImportacionModel::create([
                    'id_importacion'                => $importacion->id_importacion,
                    'id_producto'                   => $p['id_producto'],
                    'cantidad'                      => $p['cantidad'],
                    'precio_unitario_fob_usd'       => $p['precio_fob_usd'],
                    'gasto_internacion_unitario_bs' => round($gastoUnitarioBs, 2),
                    'costo_final_unitario_bs'       => round($costoFinalBs, 2)
                ]);
            }

            // Registro inicial de trazabilidad
            SeguimientoImportacionModel::create([
                'id_importacion' => $importacion->id_importacion,
                'fecha_hora'     => now(),
                'ubicacion'      => 'Puerto de Embarque / Origen: ' . $request->pais_origen,
                'estado'         => 'En tránsito',
                'detalle'        => 'Pedido de importación formalizado con código de contenedor ' . $request->codigo_embarque
            ]);

            DB::commit();

            return redirect()->route('importaciones')
                ->with('mensaje', 'Orden de importación ' . $request->codigo_embarque . ' registrada con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al procesar la importación: ' . $e->getMessage());
        }
    }

    // VER DETALLE Y CALCULADORA DE INTERNACIÓN (HU8, HU10)
    public function detalle($id)
    {
        $importacion = ImportacionModel::with([
            'proveedor',
            'usuario',
            'detalles.producto',
            'seguimientos'
        ])->findOrFail($id);

        return view('importacion_detalle', compact('importacion'));
    }

    // ACTUALIZAR GASTOS DE INTERNACIÓN Y RECALCULAR PRORRATEO (HU8)
    public function actualizarGastos(Request $request, $id)
    {
        $request->validate([
            'costo_flete'            => 'nullable|numeric|min:0',
            'costo_aduana_aranceles' => 'nullable|numeric|min:0',
            'costo_seguro'           => 'nullable|numeric|min:0',
            'otros_gastos'           => 'nullable|numeric|min:0',
            'tipo_cambio'            => 'required|numeric|min:1'
        ]);

        DB::beginTransaction();

        try {
            $importacion = ImportacionModel::with('detalles')->findOrFail($id);

            $flete = (float)($request->costo_flete ?? 0);
            $aduana = (float)($request->costo_aduana_aranceles ?? 0);
            $seguro = (float)($request->costo_seguro ?? 0);
            $otros = (float)($request->otros_gastos ?? 0);
            $totalGastosBs = $flete + $aduana + $seguro + $otros;

            $importacion->costo_flete = $flete;
            $importacion->costo_aduana_aranceles = $aduana;
            $importacion->costo_seguro = $seguro;
            $importacion->otros_gastos = $otros;
            $importacion->total_gastos_internacion = $totalGastosBs;
            $importacion->tipo_cambio = (float)$request->tipo_cambio;
            $importacion->save();

            $cantidadTotal = $importacion->detalles->sum('cantidad');
            $gastoUnitarioBs = $cantidadTotal > 0 ? ($totalGastosBs / $cantidadTotal) : 0;

            foreach ($importacion->detalles as $det) {
                $fobBs = (float)$det->precio_unitario_fob_usd * (float)$importacion->tipo_cambio;
                $det->gasto_internacion_unitario_bs = round($gastoUnitarioBs, 2);
                $det->costo_final_unitario_bs = round($fobBs + $gastoUnitarioBs, 2);
                $det->save();
            }

            DB::commit();

            return redirect()->back()->with('mensaje', 'Gastos de internación y costos prorrateados actualizados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar gastos: ' . $e->getMessage());
        }
    }

    // AGREGAR SEGUIMIENTO CRONOLÓGICO / ACTUALIZAR ESTADO (HU9, HU10)
    public function agregarSeguimiento(Request $request, $id)
    {
        $request->validate([
            'ubicacion' => 'required|string|max:150',
            'estado'    => 'required|in:En tránsito,En Aduana,Recibido',
            'detalle'   => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $importacion = ImportacionModel::with('detalles.producto')->findOrFail($id);
            $estadoAnterior = $importacion->estado_logistico;
            $nuevoEstado = $request->estado;

            $importacion->estado_logistico = $nuevoEstado;

            // Si pasa a RECIBIDO (HU9), incrementar automáticamente el stock y actualizar costo_compra en almacén
            if ($nuevoEstado === 'Recibido' && $estadoAnterior !== 'Recibido') {
                $importacion->fecha_recibido = now();

                foreach ($importacion->detalles as $det) {
                    if ($det->producto) {
                        $det->producto->stock += $det->cantidad;
                        $det->producto->costo_compra = $det->costo_final_unitario_bs;
                        $det->producto->save();
                    }
                }
            }

            $importacion->save();

            // Guardar hito en el timeline
            SeguimientoImportacionModel::create([
                'id_importacion' => $importacion->id_importacion,
                'fecha_hora'     => now(),
                'ubicacion'      => $request->ubicacion,
                'estado'         => $nuevoEstado,
                'detalle'        => $request->detalle ?? ('Cambio de estado logístico a: ' . $nuevoEstado)
            ]);

            DB::commit();

            $msg = $nuevoEstado === 'Recibido' 
                ? '¡Carga recibida en almacén! El stock de los productos ha sido incrementado automáticamente.' 
                : 'Punto de seguimiento logístico registrado exitosamente.';

            return redirect()->back()->with('mensaje', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar seguimiento: ' . $e->getMessage());
        }
    }
}
