@extends('adm_plantilla')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-journal-text text-primary me-2"></i>Historial de Compras y Abastecimiento
        </h3>
        <p class="text-muted small mb-0">Auditoría de compras a proveedores, costos de adquisición y detalles de mercadería</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('compras') }}" class="btn btn-primary shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-bag-plus"></i> + Registrar Nueva Compra
        </a>
    </div>
</div>

<!-- FILTROS DE HISTORIAL -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('historial.compras') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Proveedor</label>
                <select name="id_proveedor" class="form-select form-select-sm">
                    <option value="">Todos los proveedores</option>
                    @foreach($proveedores as $p)
                    <option value="{{ $p->id_proveedor }}" {{ request('id_proveedor') == $p->id_proveedor ? 'selected' : '' }}>
                        {{ $p->nombre_empresa }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-filter me-1"></i> Filtrar
                </button>
                <a href="{{ route('historial.compras') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar Filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- TABLA DE HISTORIAL -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">N° Compra</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>N° Comprobante</th>
                    <th>Registrado por</th>
                    <th>Total Invertido</th>
                    <th class="text-end pe-4">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $c)
                @php
                    $totalCompra = $c->detalles->sum(function($d) {
                        return $d->cantidad * $d->precio_compra;
                    });
                @endphp
                <tr>
                    <td class="ps-4 fw-bold">
                        <span class="badge bg-light text-dark border">#{{ $c->id_compra }}</span>
                    </td>
                    <td class="small text-muted">
                        {{ date('d/m/Y H:i', strtotime($c->fecha)) }}
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $c->proveedor->nombre_empresa ?? 'Sin proveedor' }}</div>
                        <small class="text-muted">{{ $c->proveedor->telefono ?? '' }}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border">
                            {{ $c->nro_comprobante }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-person me-1"></i>{{ $c->usuario->nombre ?? 'Sistema' }}
                        </span>
                    </td>
                    <td class="fw-bold text-dark fs-6">
                        Bs. {{ number_format($totalCompra, 2) }}
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detalleCompraModal{{ $c->id_compra }}">
                            <i class="bi bi-eye me-1"></i> Ver Ítems ({{ count($c->detalles) }})
                        </button>
                    </td>
                </tr>

                <!-- MODAL DETALLE DE COMPRA -->
                <div class="modal fade" id="detalleCompraModal{{ $c->id_compra }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fs-6 fw-bold">
                                    <i class="bi bi-bag-check me-2"></i>Detalle de Compra #{{ $c->id_compra }} - Factura: {{ $c->nro_comprobante }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3 mb-3 small">
                                    <div class="col-md-4">
                                        <strong class="text-muted d-block">Proveedor:</strong>
                                        <span>{{ $c->proveedor->nombre_empresa ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="text-muted d-block">Fecha:</strong>
                                        <span>{{ date('d/m/Y H:i', strtotime($c->fecha)) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="text-muted d-block">Usuario Operador:</strong>
                                        <span>{{ $c->usuario->nombre ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-2">Productos Adquiridos:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Cantidad Ingresada</th>
                                                <th class="text-end">Costo Unitario</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($c->detalles as $det)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $det->producto->nombre ?? 'Producto no encontrado' }}</div>
                                                    <small class="text-muted">{{ $det->producto->marca ?? '' }}</small>
                                                </td>
                                                <td class="text-center fw-bold">{{ $det->cantidad }} un.</td>
                                                <td class="text-end">Bs. {{ number_format($det->precio_compra, 2) }}</td>
                                                <td class="text-end fw-semibold text-dark">Bs. {{ number_format($det->cantidad * $det->precio_compra, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="3" class="text-end fw-bold">TOTAL COMPRA:</th>
                                                <th class="text-end fw-bold fs-6 text-primary">Bs. {{ number_format($totalCompra, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        No se encontraron registros de compras en el historial.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
