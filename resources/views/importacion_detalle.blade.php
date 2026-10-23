@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('importaciones') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver a Importaciones</a>
                <h3 class="fw-bold mb-0"><i class="bi bi-file-earmark-text text-primary me-2"></i>Orden de Embarque: {{ $importacion->codigo_embarque }}</h3>
            </div>
            <p class="text-muted small mb-0">Detalle de costeo prorrateado de internación, aduanas y trazabilidad cronológica de la carga</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
                <i class="bi bi-printer me-1"></i> Imprimir Ficha
            </button>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGastos">
                <i class="bi bi-pencil-square me-1"></i> Ajustar Gastos Aduana / Internación
            </button>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session('mensaje'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tarjeta Resumen Financiero y Logístico -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small text-uppercase fw-semibold">Proveedor & Origen</div>
                <div class="fw-bold text-dark fs-5 mt-1">{{ $importacion->proveedor->nombre_empresa ?? 'Sin proveedor' }}</div>
                <div class="text-secondary small">{{ $importacion->pais_origen }}</div>
                <div class="text-muted small mt-2">Fecha: {{ date('d/m/Y', strtotime($importacion->fecha_pedido)) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small text-uppercase fw-semibold">Monto FOB Total</div>
                <div class="fw-bold text-primary fs-4 mt-1">$us {{ number_format($importacion->monto_fob_usd, 2) }}</div>
                <div class="text-muted small">T/C: {{ number_format($importacion->tipo_cambio, 2) }} Bs/$us</div>
                <div class="text-dark small mt-1">Eq: <strong>Bs. {{ number_format($importacion->monto_fob_usd * $importacion->tipo_cambio, 2) }}</strong></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="text-muted small text-uppercase fw-semibold">Gastos Internación (Bs.)</div>
                <div class="fw-bold text-warning-emphasis fs-4 mt-1">Bs. {{ number_format($importacion->total_gastos_internacion, 2) }}</div>
                <div class="small text-muted">Flete, Aduana, Aranceles y Seguros</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-dark text-white h-100">
                <div class="text-white-50 small text-uppercase fw-semibold">Costo Real Inversión (Bs.)</div>
                <div class="fw-bold text-info fs-4 mt-1">Bs. {{ number_format($importacion->total_inversion_bs, 2) }}</div>
                <div class="mt-2">
                    <span class="badge {{ $importacion->estado_logistico === 'Recibido' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $importacion->estado_logistico }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Desglose de Gastos de Internación -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt-cutoff me-2"></i>Desglose de Gastos Económicos de Internación y Aduana (HU8)</h6>
        </div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block">Transporte & Flete Logístico</span>
                    <strong class="fs-5 text-dark">Bs. {{ number_format($importacion->costo_flete, 2) }}</strong>
                </div>
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block">Gravamen Aduanero & Aranceles</span>
                    <strong class="fs-5 text-dark">Bs. {{ number_format($importacion->costo_aduana_aranceles, 2) }}</strong>
                </div>
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block">Seguro de Transporte</span>
                    <strong class="fs-5 text-dark">Bs. {{ number_format($importacion->costo_seguro, 2) }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small d-block">Otros Gastos de Despacho</span>
                    <strong class="fs-5 text-dark">Bs. {{ number_format($importacion->otros_gastos, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos con Prorrateo de Costo Real -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-calculator me-2"></i>Cálculo de Costo Real Prorrateado por Producto (HU8, HU9)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Producto</th>
                        <th class="text-center">Cant. Importada</th>
                        <th class="text-end">Precio FOB ($us)</th>
                        <th class="text-end">FOB en Bs.</th>
                        <th class="text-end text-warning-emphasis">Gasto Internación Unit. (Bs.)</th>
                        <th class="text-end bg-primary-subtle text-primary fw-bold">Costo Real Unitario (Bs.)</th>
                        <th class="text-end pe-3">Subtotal Inversión (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($importacion->detalles as $det)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark">{{ $det->producto->nombre ?? 'Producto' }}</div>
                            <small class="text-muted">{{ $det->producto->marca ?? '' }}</small>
                        </td>
                        <td class="text-center fw-semibold">{{ $det->cantidad }} un.</td>
                        <td class="text-end">$us {{ number_format($det->precio_unitario_fob_usd, 2) }}</td>
                        <td class="text-end">Bs. {{ number_format($det->precio_unitario_fob_usd * $importacion->tipo_cambio, 2) }}</td>
                        <td class="text-end text-warning-emphasis fw-semibold">+ Bs. {{ number_format($det->gasto_internacion_unitario_bs, 2) }}</td>
                        <td class="text-end bg-primary-subtle fw-bold text-primary fs-6">
                            Bs. {{ number_format($det->costo_final_unitario_bs, 2) }}
                        </td>
                        <td class="text-end pe-3 fw-bold">
                            Bs. {{ number_format($det->cantidad * $det->costo_final_unitario_bs, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Trazabilidad Logística Cronológica (HU10) -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-geo-alt me-2"></i>Seguimiento Cronológico de la Carga (HU10)</h6>
            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalSeguimiento">
                <i class="bi bi-plus-lg"></i> + Actualizar Ubicación / Estado
            </button>
        </div>
        <div class="card-body p-4">
            <div class="timeline">
                @forelse($importacion->seguimientos as $seg)
                <div class="d-flex gap-3 mb-3 border-bottom pb-3">
                    <div class="text-center" style="min-width: 90px;">
                        <span class="badge bg-dark small">{{ date('d/m/Y', strtotime($seg->fecha_hora)) }}</span>
                        <small class="text-muted d-block">{{ date('H:i', strtotime($seg->fecha_hora)) }}</small>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $seg->estado === 'Recibido' ? 'bg-success' : ($seg->estado === 'En Aduana' ? 'bg-warning text-dark' : 'bg-primary') }}">
                                {{ $seg->estado }}
                            </span>
                            <strong class="text-dark">{{ $seg->ubicacion }}</strong>
                        </div>
                        <p class="text-muted small mb-0 mt-1">{{ $seg->detalle }}</p>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No se han registrado hitos logísticos aún.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

<!-- MODAL ACTUALIZAR GASTOS DE INTERNACIÓN -->
<div class="modal fade" id="modalGastos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Actualizar Gastos de Internación y Aduana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('importaciones.gastos', $importacion->id_importacion) }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tipo Cambio (Bs/$us)</label>
                            <input type="number" step="1" min="1" name="tipo_cambio" class="form-control" value="{{ $importacion->tipo_cambio }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Transporte & Flete (Bs.)</label>
                            <input type="number" step="1" min="0" name="costo_flete" class="form-control" value="{{ $importacion->costo_flete }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Aduanas & Aranceles (Bs.)</label>
                            <input type="number" step="1" min="0" name="costo_aduana_aranceles" class="form-control" value="{{ $importacion->costo_aduana_aranceles }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Seguro (Bs.)</label>
                            <input type="number" step="1" min="0" name="costo_seguro" class="form-control" value="{{ $importacion->costo_seguro }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Otros Gastos (Bs.)</label>
                            <input type="number" step="1" min="0" name="otros_gastos" class="form-control" value="{{ $importacion->otros_gastos }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Recalcular y Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR SEGUIMIENTO / ACTUALIZAR ESTADO -->
<div class="modal fade" id="modalSeguimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Actualizar Estado / Seguimiento de la Carga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('importaciones.seguimiento', $importacion->id_importacion) }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Estado Logístico *</label>
                            <select name="estado" class="form-select" required>
                                <option value="En tránsito" {{ $importacion->estado_logistico === 'En tránsito' ? 'selected' : '' }}>En tránsito (En ruta hacia Bolivia)</option>
                                <option value="En Aduana" {{ $importacion->estado_logistico === 'En Aduana' ? 'selected' : '' }}>En Aduana (Nacionalización / Tramitación)</option>
                                <option value="Recibido" {{ $importacion->estado_logistico === 'Recibido' ? 'selected' : '' }}>Recibido en Almacén (Ingreso automático a stock)</option>
                            </select>
                            <small class="text-primary mt-1 d-block"><i class="bi bi-info-circle"></i> Al seleccionar "Recibido en Almacén", las unidades se sumarán automáticamente al inventario físico disponible.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Ubicación Actual *</label>
                            <input type="text" name="ubicacion" class="form-control" placeholder="Ej. Aduana Interior Oruro / Almacén Central PEBIE" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Detalles / Novedades</label>
                            <textarea name="detalle" class="form-control" rows="2" placeholder="Observaciones de la carga, precinto de seguridad, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">Guardar Seguimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
