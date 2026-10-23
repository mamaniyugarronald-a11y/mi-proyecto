@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('productos') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver a Productos</a>
                <h3 class="fw-bold mb-0"><i class="bi bi-clock-history text-info me-2"></i>Kardex Físico & Movimientos</h3>
            </div>
            <p class="text-muted small mb-0">Auditoría cronológica completa de entradas, salidas, mermas y balance de stock</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
                <i class="bi bi-printer me-1"></i> Imprimir Kardex
            </button>
        </div>
    </div>

    <!-- Ficha Resumen del Producto -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light" style="position: sticky; top: 76px; z-index: 1010;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    @if($producto->imagen_url)
                        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                        <div class="bg-white rounded border d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                            <i class="bi bi-box-seam text-primary fs-2"></i>
                        </div>
                    @endif
                </div>
                <div class="col">
                    <div class="small text-primary fw-semibold mb-1">Tipo de producto: {{ $producto->tipoProducto->nombre_tipo ?? 'General' }}</div>
                    <h4 class="fw-bold text-dark mb-1">{{ $producto->nombre }}</h4>
                    <p class="text-muted small mb-0">{{ $producto->descripcion }}</p>
                    <div class="small text-secondary mt-1">
                        <strong>Marca:</strong> {{ $producto->marca }} | 
                        <strong>Proveedor:</strong> {{ $producto->proveedor->nombre_empresa ?? 'No especificado' }} |
                        <strong>Stock Mínimo Alerta:</strong> {{ $producto->stock_minimo }} un.
                    </div>
                </div>
                <div class="col-md-auto text-md-end border-start ps-md-4">
                    <div class="text-muted small text-uppercase">Stock Físico Actual</div>
                    <div class="fs-2 fw-bold {{ $producto->stock <= $producto->stock_minimo ? 'text-danger' : 'text-success' }}">
                        {{ $producto->stock }} <span class="fs-6 text-muted">unidades</span>
                    </div>
                    <div class="small">
                        <span class="text-muted">Precio Venta Menor:</span> <strong>Bs. {{ number_format($producto->precio_menor, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('productos.kardex', $producto->id_producto) }}" class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end g-3">
                <div class="col-sm-4 col-md-3">
                    <label for="desde" class="form-label small fw-semibold">Desde</label>
                    <input id="desde" name="desde" type="date" class="form-control" value="{{ request('desde') }}">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label for="hasta" class="form-label small fw-semibold">Hasta</label>
                    <input id="hasta" name="hasta" type="date" class="form-control" value="{{ request('hasta') }}">
                </div>
                <div class="col-sm-4 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                    @if(request()->filled('desde') || request()->filled('hasta'))
                    <a href="{{ route('productos.kardex', $producto->id_producto) }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <!-- Tabla de Movimientos del Kardex -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-journal-text me-2"></i>Historial Cronológico de Movimientos</h6>
            <span class="badge bg-secondary-subtle text-secondary">{{ count($kardexFilas) }} Registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Concepto / Operación</th>
                        <th class="ps-3">Fecha & Hora</th>
                        <th>Tipo Movimiento</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio Unit. (Bs.)</th>
                        <th class="text-end">Total Operación (Bs.)</th>
                        <th class="text-center pe-3 bg-light-subtle">Saldo Resultante</th>
                        <th>Registrado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kardexFilas as $fila)
                    <tr>
                        <td>
                            <span class="fw-medium text-dark">{{ $fila['operacion'] }}</span>
                        </td>
                        <td class="ps-3 small">
                            <span class="fw-semibold text-dark">{{ date('d/m/Y', strtotime($fila['fecha'])) }}</span>
                            <small class="text-muted d-block">{{ date('H:i', strtotime($fila['fecha'])) }}</small>
                        </td>
                        <td>
                            @if($fila['tipo'] === 'ENTRADA')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-arrow-down-left me-1"></i> ENTRADA (+)
                                </span>
                            @elseif($fila['tipo'] === 'SALIDA')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-arrow-up-right me-1"></i> SALIDA (-)
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                    <i class="bi bi-slash-circle me-1"></i> MERMA / BAJA (-)
                                </span>
                            @endif
                        </td>
                        <td class="text-center fw-bold {{ $fila['clase'] }}">
                            {{ $fila['tipo'] === 'ENTRADA' ? '+' : '-' }}{{ $fila['cantidad'] }} un.
                        </td>
                        <td class="text-end small">
                            {{ $fila['precio_unit'] > 0 ? 'Bs. ' . number_format($fila['precio_unit'], 2) : '-' }}
                        </td>
                        <td class="text-end small fw-semibold">
                            {{ $fila['total'] > 0 ? 'Bs. ' . number_format($fila['total'], 2) : '-' }}
                        </td>
                        <td class="text-center pe-3 bg-light-subtle">
                            <span class="badge bg-dark fs-6 px-3 py-1">
                                {{ $fila['saldo_resultante'] }} un.
                            </span>
                        </td>
                        <td class="small text-muted">
                            <i class="bi bi-person me-1"></i> {{ $fila['usuario'] }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            No se han registrado transacciones comerciales aún para este producto.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
