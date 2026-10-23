@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-whatsapp text-success me-2"></i>CRM & Consultas de Redes Sociales</h3>
            <p class="text-muted small mb-0">Seguimiento de prospectos, cotizaciones y solicitudes de WhatsApp, Facebook y llamadas</p>
        </div>
        <button class="btn btn-success shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalConsulta">
            <i class="bi bi-plus-circle-fill"></i> + Nueva Consulta / Cotización
        </button>
    </div>

    <!-- Alertas Flash -->
    @if(session('mensaje'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-chat-left-dots me-2"></i>Historial de Solicitudes Registradas ({{ count($consultas) }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Fecha</th>
                        <th>Cliente / Contacto</th>
                        <th>Canal</th>
                        <th>Producto de Interés</th>
                        <th>Mensaje / Solicitud</th>
                        <th class="text-end">Cotización</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultas as $c)
                    <tr>
                        <td class="ps-3 small">
                            <span class="fw-semibold text-dark">{{ date('d/m/Y', strtotime($c->fecha)) }}</span>
                            <small class="text-muted d-block">{{ date('H:i', strtotime($c->fecha)) }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $c->nombre_cliente }}</div>
                            <small class="text-muted">{{ $c->telefono }}</small>
                            @php
                                $limpioTel = preg_replace('/[^0-9]/', '', $c->telefono);
                            @endphp
                            @if($limpioTel)
                                <a href="https://api.whatsapp.com/send?phone={{ $limpioTel }}&text=Hola%20{{ urlencode($c->nombre_cliente) }},%20te%20escribimos%20de%20Importadora%20PEBIE%20para%20darle%20seguimiento%20a%20su%20consulta." 
                                   target="_blank" 
                                   class="badge bg-success text-decoration-none d-inline-flex align-items-center gap-1 mt-1">
                                    <i class="bi bi-whatsapp"></i> Chatear
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($c->canal === 'WhatsApp')
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                            @elseif($c->canal === 'Facebook')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-facebook"></i> Facebook</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="bi bi-telephone"></i> {{ $c->canal }}</span>
                            @endif
                        </td>
                        <td>
                            @if($c->producto)
                                <strong class="text-dark">{{ $c->producto->nombre }}</strong>
                                <small class="text-muted d-block">Stock: {{ $c->producto->stock }} un.</small>
                            @else
                                <span class="text-muted">Consulta general</span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-dark">{{ $c->mensaje_solicitud }}</span>
                        </td>
                        <td class="text-end fw-bold text-success">
                            {{ $c->monto_cotizado ? 'Bs. ' . number_format($c->monto_cotizado, 2) : '-' }}
                        </td>
                        <td class="text-center">
                            @if($c->estado === 'Pendiente')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pendiente</span>
                            @elseif($c->estado === 'Cotizado')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Cotizado</span>
                            @elseif($c->estado === 'Venta Concretada')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Venta Concretada</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Descartado</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <form action="{{ route('crm.estado', $c->id_consulta) }}" method="POST" class="d-inline-flex gap-1">
                                @csrf
                                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 140px;">
                                    <option value="Pendiente" {{ $c->estado === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="Cotizado" {{ $c->estado === 'Cotizado' ? 'selected' : '' }}>Cotizado</option>
                                    <option value="Venta Concretada" {{ $c->estado === 'Venta Concretada' ? 'selected' : '' }}>Venta Concretada</option>
                                    <option value="Descartado" {{ $c->estado === 'Descartado' ? 'selected' : '' }}>Descartado</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-dots fs-1 d-block mb-2 text-secondary"></i>
                            No hay consultas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL NUEVA CONSULTA -->
<div class="modal fade" id="modalConsulta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-plus me-2"></i>Registrar Consulta o Cotización</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('guardar.consulta') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre del Cliente *</label>
                            <input type="text" name="nombre_cliente" class="form-control" placeholder="Ej. Maria Flores" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono / WhatsApp *</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej. +591 71234567" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Canal de Contacto *</label>
                            <select name="canal" class="form-select" required>
                                <option value="WhatsApp" selected>WhatsApp</option>
                                <option value="Facebook">Facebook Messenger / Post</option>
                                <option value="Presencial">Presencial en Tienda</option>
                                <option value="Llamada">Llamada Telefónica</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Artículo de Interés (Opcional)</label>
                            <select name="id_producto" class="form-select">
                                <option value="">-- Consulta general / Varios productos --</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id_producto }}">{{ $p->nombre }} (Precio menor: Bs. {{ number_format($p->precio_menor, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Mensaje / Detalle de la Solicitud *</label>
                            <textarea name="mensaje_solicitud" class="form-control" rows="3" placeholder="Pregunta por disponibilidad de color, precio por mayor, etc." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Monto Cotizado (Bs.)</label>
                            <input type="number" step="1" min="0" name="monto_cotizado" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Guardar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
