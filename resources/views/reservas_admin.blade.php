@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-ticket-perforated text-primary me-2"></i>Control de Reservas Web de Clientes</h3>
            <p class="text-muted small mb-0">Gestión de tickets de reserva generados desde el catálogo virtual público</p>
        </div>
        <a href="{{ route('catalogo.publico') }}" target="_blank" class="btn btn-outline-primary shadow-sm">
            <i class="bi bi-shop me-1"></i> Ver Catálogo Público
        </a>
    </div>

    <!-- Alertas Flash -->
    @if(session('mensaje'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="GET" action="{{ route('reservas.admin') }}" class="card card-body border-0 shadow-sm rounded-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-sm-6 col-md-3"><label class="form-label small fw-semibold">Desde</label><input type="date" name="desde" class="form-control" value="{{ request('desde') }}"></div>
            <div class="col-sm-6 col-md-3"><label class="form-label small fw-semibold">Hasta</label><input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}"></div>
            <div class="col-sm-8 col-md-3"><label class="form-label small fw-semibold">Estado</label><select name="estado" class="form-select"><option value="">Todos los estados</option>@foreach(['Pendiente','Confirmada','Retirada / Vendida','Cancelada'] as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select></div>
            <div class="col-sm-4 col-md-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button><a href="{{ route('reservas.admin') }}" class="btn btn-outline-secondary">Limpiar</a></div>
        </div>
    </form>

    <!-- Tabla de Reservas -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-list-stars me-2"></i>Grupos de reserva ({{ count($reservas) }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Código Reserva</th>
                        <th>Cliente / Contacto</th>
                        <th>Productos solicitados</th>
                        <th class="text-center">Unidades</th>
                        <th class="text-end">Total a Cobrar (Bs.)</th>
                        <th>Vigencia / Fecha Límite</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-3">Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $res)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-dark fs-6">{{ $res->codigo_reserva }}</span>
                            <small class="text-muted d-block mt-1">{{ date('d/m/Y H:i', strtotime($res->fecha_creacion)) }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $res->nombre_cliente }}</div>
                            <small class="text-muted">{{ $res->telefono_cliente }}</small>
                            @php
                                $limpioTel = preg_replace('/[^0-9]/', '', $res->telefono_cliente);
                            @endphp
                            @if($limpioTel)
                                <a href="https://api.whatsapp.com/send?phone={{ $limpioTel }}&text=Hola%20{{ urlencode($res->nombre_cliente) }},%20te%20escribimos%20de%20Importadora%20PEBIE%20por%20tu%20reserva%20{{ urlencode($res->codigo_reserva) }}." 
                                   target="_blank" 
                                   class="badge bg-success text-decoration-none d-inline-flex align-items-center gap-1 ms-1">
                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                </a>
                            @endif
                        </td>
                        <td>
                            @foreach($res->productos as $productoReserva)
                                <div class="mb-1"><strong class="text-dark">{{ $productoReserva->producto->nombre ?? 'Producto' }}</strong> <span class="text-muted">× {{ $productoReserva->cantidad }}</span><small class="text-muted d-block">Ticket: {{ $productoReserva->codigo_reserva }} · Bs. {{ number_format($productoReserva->total_reserva, 2) }}</small></div>
                            @endforeach
                        </td>
                        <td class="text-center fw-bold">{{ $res->cantidad_total }} un.</td>
                        <td class="text-end fw-bold text-success fs-6">Bs. {{ number_format($res->total_reserva, 2) }}</td>
                        <td class="small">
                            <span class="text-danger fw-semibold">{{ date('d/m/Y H:i', strtotime($res->fecha_limite)) }}</span>
                        </td>
                        <td class="text-center">
                            @if($res->estado === 'Pendiente')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pendiente</span>
                            @elseif($res->estado === 'Confirmada')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Confirmada</span>
                            @elseif($res->estado === 'Retirada / Vendida')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Retirada / Vendida</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Cancelada</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <form action="{{ route('reservas.estado', $res->grupo) }}" method="POST" class="d-inline-flex gap-1">
                                @csrf
                                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 155px;">
                                    <option value="Pendiente" {{ $res->estado === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="Confirmada" {{ $res->estado === 'Confirmada' ? 'selected' : '' }}>Confirmada</option>
                                    <option value="Retirada / Vendida" {{ $res->estado === 'Retirada / Vendida' ? 'selected' : '' }}>Retirada / Vendida</option>
                                    <option value="Cancelada" {{ $res->estado === 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-ticket fs-1 d-block mb-2 text-secondary"></i>
                            No hay reservas registradas actualmente.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
