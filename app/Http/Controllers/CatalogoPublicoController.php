<?php

namespace App\Http\Controllers;

use App\Models\ProductoModel;
use App\Models\TipoProductoModel;
use App\Models\ReservaProductoModel;
use App\Models\ConfiguracionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CatalogoPublicoController extends Controller
{
    // HU24: CATÁLOGO PÚBLICO WEB PARA CLIENTES
    public function index(Request $request)
    {
        $query = ProductoModel::with(['tipoProducto', 'proveedor'])
            ->where('estado', 1);

        if ($request->filled('tipo')) {
            $query->where('id_tipo_producto', $request->tipo);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('marca', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $productos = $query->orderBy('nombre', 'asc')->get();
        $tipos = TipoProductoModel::all();
        $empresa = ConfiguracionModel::getEmpresa();

        return view('catalogo_publico', compact('productos', 'tipos', 'empresa'));
    }

    // HU25: SOLICITUD DE RESERVA DE PRODUCTO
    public function reservar(Request $request)
    {
        $request->validate([
            'productos'         => 'required|array|min:1',
            'productos.*.id_producto' => 'required|integer|distinct|exists:producto,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'nombre_cliente'    => 'required|string|max:150',
            'telefono_cliente'  => 'required|string|max:50',
        ]);

        $reservas = DB::transaction(function () use ($request) {
            $resultado = [];
            $grupoReserva = 'PEBIE-GRP-' . strtoupper(Str::random(8));
            foreach ($request->productos as $item) {
                $producto = ProductoModel::where('estado', 1)->whereKey($item['id_producto'])->lockForUpdate()->first();
                if (!$producto || $producto->stock < $item['cantidad']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'productos' => ['Stock insuficiente para ' . ($producto->nombre ?? 'uno de los productos') . '. Actualiza la cantidad e inténtalo nuevamente.']
                    ]);
                }

                $precioAplicado = ($item['cantidad'] >= $producto->cantidad_min_mayor) ? $producto->precio_mayor : $producto->precio_menor;
                $totalReserva = $precioAplicado * $item['cantidad'];
                $reserva = ReservaProductoModel::create([
                    'codigo_reserva'   => 'PEBIE-RES-' . strtoupper(Str::random(5)) . '-' . date('dmy'),
                    'grupo_reserva'    => $grupoReserva,
                    'id_producto'      => $producto->id_producto,
                    'nombre_cliente'   => $request->nombre_cliente,
                    'telefono_cliente' => $request->telefono_cliente,
                    'cantidad'         => $item['cantidad'],
                    'precio_unitario'  => $precioAplicado,
                    'total_reserva'    => $totalReserva,
                    'estado'           => 'Pendiente',
                    'fecha_limite'     => now()->addDays(2),
                    'fecha_creacion'   => now()
                ]);
                $resultado[] = [
                    'codigo' => $reserva->codigo_reserva,
                    'producto' => $producto->nombre,
                    'cantidad' => $reserva->cantidad,
                    'total' => number_format($reserva->total_reserva, 2),
                ];
            }
            return $resultado;
        });

        return response()->json([
            'success'       => true,
            'mensaje'       => '¡Reservas generadas con éxito!',
            'reservas'      => $reservas,
            'total'         => number_format(array_sum(array_map(fn ($r) => (float) $r['total'], $reservas)), 2),
            'fecha_limite'  => now()->addDays(2)->format('d/m/Y H:i')
        ]);
    }

    // LISTAR RESERVAS DESDE EL PANEL DE ADMINISTRACIÓN
    public function listaReservas()
    {
        $query = ReservaProductoModel::with('producto');
        if (request()->filled('desde')) {
            $query->whereDate('fecha_creacion', '>=', request('desde'));
        }
        if (request()->filled('hasta')) {
            $query->whereDate('fecha_creacion', '<=', request('hasta'));
        }
        if (request()->filled('estado')) {
            $query->where('estado', request('estado'));
        }

        $reservas = $query->orderBy('id_reserva', 'desc')->get()
            ->groupBy(fn ($reserva) => $reserva->grupo_reserva ?: 'legado-' . $reserva->id_reserva)
            ->map(function ($items, $grupo) {
                $primera = $items->first();
                return (object) [
                    'grupo' => $grupo,
                    'id_reserva' => $primera->id_reserva,
                    'codigo_reserva' => $items->count() > 1 ? $grupo : $primera->codigo_reserva,
                    'nombre_cliente' => $primera->nombre_cliente,
                    'telefono_cliente' => $primera->telefono_cliente,
                    'fecha_creacion' => $primera->fecha_creacion,
                    'fecha_limite' => $items->min('fecha_limite'),
                    'estado' => $primera->estado,
                    'productos' => $items,
                    'cantidad_total' => $items->sum('cantidad'),
                    'total_reserva' => $items->sum('total_reserva'),
                ];
            })->values();

        return view('reservas_admin', compact('reservas'));
    }

    public function cambiarEstadoReserva(Request $request, $grupo)
    {
        $request->validate(['estado' => 'required|in:Pendiente,Confirmada,Retirada / Vendida,Cancelada']);
        $resultado = DB::transaction(function () use ($grupo, $request) {
            $query = str_starts_with($grupo, 'legado-')
                ? ReservaProductoModel::where('id_reserva', substr($grupo, 7))
                : ReservaProductoModel::where('grupo_reserva', $grupo);
            $reservas = $query->lockForUpdate()->get();
            if ($reservas->isEmpty()) {
                return 'not_found';
            }
            if ($reservas->contains(fn ($reserva) => in_array($reserva->estado, ['Retirada / Vendida', 'Cancelada']))) {
                return 'closed';
            }
            $reservas->each(function ($reserva) use ($request) {
                $reserva->estado = $request->estado;
                $reserva->save();
            });
            return 'updated';
        });

        if ($resultado === 'not_found') {
            abort(404);
        }
        if ($resultado === 'closed') {
            return redirect()->back()->with('error', 'Una reserva finalizada no puede cambiar de estado.');
        }

        return redirect()->back()->with('mensaje', 'Estado del grupo de reserva actualizado.');
    }
}
