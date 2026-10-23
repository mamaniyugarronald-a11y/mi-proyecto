@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('clientes') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver a Clientes</a>
                <h3 class="fw-bold mb-0"><i class="bi bi-person-badge text-primary me-2"></i>Historial Comercial del Cliente</h3>
            </div>
            <p class="text-muted small mb-0">Registro acumulado de compras, frecuencia de consumo y nivel de fidelización</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
            <i class="bi bi-printer me-1"></i> Imprimir Estado de Cuenta
        </button>
    </div>

    <!-- Ficha del Cliente -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <span class="badge bg-primary mb-1">{{ $cliente->tipo_registro }}</span>
                    <h4 class="fw-bold text-dark mb-1">
                        {{ $cliente->tipo_registro === 'Unipersonal' ? ($cliente->unipersonal->nombre . ' ' . $cliente->unipersonal->apellido) : ($cliente->institucional->razon_social ?? 'Empresa') }}
                    </h4>
                    <div class="small text-secondary">
                        <strong>Documento:</strong> {{ $cliente->tipo_registro === 'Unipersonal' ? ('CI: ' . ($cliente->unipersonal->ci ?? '-')) : ('NIT: ' . ($cliente->institucional->nit ?? '-')) }} | 
                        <strong>Teléfono:</strong> {{ $cliente->telefono ?? 'Sin teléfono' }} | 
                        <strong>Email:</strong> {{ $cliente->email ?? 'Sin correo' }}
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-warning-subtle text-dark border border-warning fs-6 px-3 py-2 mb-2 d-inline-block">
                        {{ $nivelCliente }}
                    </span>
                    <div class="row g-2 justify-content-md-end mt-1">
                        <div class="col-auto">
                            <div class="bg-white border rounded p-2 text-center" style="min-width: 110px;">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">TOTAL COMPRAS</small>
                                <strong class="fs-6 text-dark">{{ $totalComprasCount }}</strong>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-white border rounded p-2 text-center" style="min-width: 140px;">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">TOTAL ACUMULADO</small>
                                <strong class="fs-6 text-success">Bs. {{ number_format($totalGastado, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="bg-white border rounded p-2 text-center" style="min-width: 120px;">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">TICKET PROM.</small>
                                <strong class="fs-6 text-primary">Bs. {{ number_format($ticketPromedio, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Compras Realizadas -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-receipt me-2"></i>Historial Detallado de Transacciones</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">N° Venta</th>
                        <th>Fecha</th>
                        <th>Tipo Comprobante</th>
                        <th>Modalidad Venta</th>
                        <th>Artículos Comprados</th>
                        <th class="text-end pe-3">Total Pagado (Bs.)</th>
                        <th class="text-end pe-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $v)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border">#{{ $v->id_venta }}</span>
                        </td>
                        <td class="small">{{ date('d/m/Y H:i', strtotime($v->fecha)) }}</td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">{{ $v->tipo_comprobante }}</span>
                            <span class="badge bg-secondary-subtle text-secondary">{{ $v->tipo_cliente }}</span>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success">{{ $v->tipo_venta }}</span>
                        </td>
                        <td>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($v->detalles as $d)
                                    <li>• {{ $d->cantidad }}x {{ $d->producto->nombre ?? 'Producto' }} (Bs. {{ number_format($d->precio_aplicado, 2) }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-end pe-3 fw-bold text-dark fs-6">
                            Bs. {{ number_format($v->total, 2) }}
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('ventas.comprobante', $v->id_venta) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-file-earmark-pdf"></i> Comprobante
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Este cliente aún no ha registrado compras en la importadora.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
