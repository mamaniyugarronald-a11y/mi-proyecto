@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>Arqueo & Cierre de Caja Diario</h3>
            <p class="text-muted small mb-0">Resumen automatizado de ingresos económicos por ventas en efectivo y transferencias/QR</p>
        </div>
        <div>
            <form method="GET" action="{{ route('caja') }}" class="d-flex align-items-center gap-2">
                <label class="form-label small fw-semibold mb-0 text-nowrap">Fecha de Arqueo:</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="{{ $fechaConsulta }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session('mensaje'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tarjetas de Arqueo del Día Seleccionado -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small text-uppercase fw-semibold"><i class="bi bi-cash me-1 text-success"></i>Total Efectivo</div>
                <div class="fw-bold text-success fs-3 mt-1">Bs. {{ number_format($totalEfectivo, 2) }}</div>
                <small class="text-muted">Cobrado en caja física</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small text-uppercase fw-semibold"><i class="bi bi-qr-code-scan me-1 text-primary"></i>Transferencias / QR</div>
                <div class="fw-bold text-primary fs-3 mt-1">Bs. {{ number_format($totalTransferenciaQR, 2) }}</div>
                <small class="text-muted">Banca digital / QR Simple</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-dark text-white h-100">
                <div class="text-white-50 small text-uppercase fw-semibold"><i class="bi bi-calculator me-1 text-info"></i>Total Recaudado Día</div>
                <div class="fw-bold text-white fs-3 mt-1">Bs. {{ number_format($totalGeneral, 2) }}</div>
                <small class="text-white-50">{{ $totalVentasConteo }} transacciones realizadas</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 d-flex flex-column justify-content-center">
                @if($cierreExistente)
                    <div class="text-center">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6 mb-2 d-inline-block">
                            <i class="bi bi-lock-fill me-1"></i> Caja Cerrada
                        </span>
                        <small class="text-muted d-block">Por: {{ $cierreExistente->usuario->nombre ?? 'Usuario' }}</small>
                        <a href="{{ route('caja.imprimir', $cierreExistente->id_cierre) }}" target="_blank" class="btn btn-sm btn-outline-dark mt-2">
                            <i class="bi bi-printer me-1"></i> Imprimir Comprobante Cierre
                        </a>
                    </div>
                @else
                    <button class="btn btn-success shadow-sm w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalCerrarCaja">
                        <i class="bi bi-check2-circle me-1"></i> Realizar Cierre de Caja del Día
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Detalle de Ventas del Día -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-receipt me-2"></i>Transacciones del Día ({{ date('d/m/Y', strtotime($fechaConsulta)) }})</h6>
            <span class="badge bg-light text-dark border">{{ count($ventasDia) }} Ventas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">N° Transacción</th>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Método Pago</th>
                        <th class="text-end pe-3">Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventasDia as $v)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border">#{{ $v->id_venta }}</span>
                            <span class="badge bg-primary-subtle text-primary small">{{ $v->tipo_comprobante }}</span>
                        </td>
                        <td class="small text-muted">{{ date('H:i', strtotime($v->fecha)) }}</td>
                        <td>
                            @if($v->cliente)
                                <div class="fw-semibold text-dark">
                                    {{ $v->cliente->tipo_registro === 'Unipersonal' ? ($v->cliente->unipersonal->nombre . ' ' . $v->cliente->unipersonal->apellido) : ($v->cliente->institucional->razon_social ?? 'Empresa') }}
                                </div>
                            @else
                                <span class="text-muted">Cliente General</span>
                            @endif
                        </td>
                        <td class="small text-secondary"><i class="bi bi-person me-1"></i>{{ $v->usuario->nombre ?? 'Vendedor' }}</td>
                        <td>
                            <span class="badge {{ $v->metodo_pago === 'Efectivo' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-info-subtle text-info border border-info-subtle' }}">
                                {{ $v->metodo_pago }}
                            </span>
                        </td>
                        <td class="text-end pe-3 fw-bold text-dark fs-6">
                            Bs. {{ number_format($v->total, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            No se registraron ventas en esta fecha.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Historial de Cierres de Caja Anteriores -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-journal-bookmark me-2"></i>Historial de Cierres Diarios Registrados</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Fecha Cierre</th>
                        <th>Usuario Responsable</th>
                        <th class="text-end">Total Efectivo</th>
                        <th class="text-end">Total Transferencias/QR</th>
                        <th class="text-end">Total General</th>
                        <th class="text-center">Transacciones</th>
                        <th>Observaciones</th>
                        <th class="text-end pe-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historialCierres as $hc)
                    <tr>
                        <td class="ps-3 fw-bold text-dark">{{ date('d/m/Y', strtotime($hc->fecha_cierre)) }}</td>
                        <td class="small"><i class="bi bi-shield-check text-primary me-1"></i>{{ $hc->usuario->nombre ?? 'Admin' }}</td>
                        <td class="text-end text-success fw-semibold">Bs. {{ number_format($hc->total_efectivo, 2) }}</td>
                        <td class="text-end text-primary fw-semibold">Bs. {{ number_format($hc->total_transferencia_qr, 2) }}</td>
                        <td class="text-end fw-bold text-dark">Bs. {{ number_format($hc->total_general, 2) }}</td>
                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $hc->total_ventas_conteo }} un.</span></td>
                        <td class="small text-muted text-truncate" style="max-width: 150px;">{{ $hc->observaciones ?? '-' }}</td>
                        <td class="text-end pe-3">
                            <a href="{{ route('caja.imprimir', $hc->id_cierre) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Imprimir Comprobante">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No existen cierres de caja archivados aún.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL CERRAR CAJA -->
<div class="modal fade" id="modalCerrarCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-lock me-2"></i>Confirmar Cierre de Caja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('guardar.cierre.caja') }}" method="POST">
                @csrf
                <input type="hidden" name="fecha_cierre" value="{{ $fechaConsulta }}">
                <div class="modal-body py-3">
                    <p class="text-muted mb-3">Se registrará el arqueo oficial con los siguientes totales de la jornada comercial:</p>

                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Fecha del Arqueo:</span>
                            <strong>{{ date('d/m/Y', strtotime($fechaConsulta)) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-success">
                            <span>Efectivo Físico:</span>
                            <strong>Bs. {{ number_format($totalEfectivo, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-primary">
                            <span>Transferencias / QR:</span>
                            <strong>Bs. {{ number_format($totalTransferenciaQR, 2) }}</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 fw-bold text-dark">
                            <span>Total General:</span>
                            <span>Bs. {{ number_format($totalGeneral, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Observaciones del Arqueo (Opcional)</label>
                        <textarea name="observaciones" class="form-control" rows="2" placeholder="Novedades de caja, billetes fraccionarios, etc."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Guardar Cierre de Caja</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
