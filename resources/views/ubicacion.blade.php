@extends('adm_plantilla')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-geo-alt text-primary me-2"></i>Gestión de Almacenes y Sucursales
        </h3>
        <p class="text-muted small mb-0">Control de ubicaciones físicas y distribución de inventario por almacén</p>
    </div>
    @if(in_array((int) session('rol'), [1, 3]))<div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAsignarStock">
            <i class="bi bi-arrow-left-right me-1"></i> Asignar Stock a Ubicación
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaUbicacion">
            <i class="bi bi-plus-lg me-1"></i> + Nueva Ubicación
        </button>
    </div>@endif
</div>

@if(session('mensaje'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@php
    $rolActual = (int) session('rol');
    $solicitudesVisibles = $rolActual === 4
        ? $solicitudesTransferencia->where('estado', 'solicitada')
        : $solicitudesTransferencia->where('estado', 'transportando');
@endphp
@if($solicitudesVisibles->isNotEmpty())
<div class="alert {{ $rolActual === 4 ? 'alert-warning' : 'alert-info' }} border-0 shadow-sm mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-truck me-2"></i>{{ $rolActual === 4 ? 'Solicitudes de traslado pendientes' : 'Traslados en curso' }}</h5>
    @foreach($solicitudesVisibles as $solicitudVisible)
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top pt-2 mt-2">
        <div><strong>{{ $solicitudVisible->nombre_producto }}</strong> · {{ $solicitudVisible->cantidad }} un.<br><small>Desde {{ $solicitudVisible->nombre_origen }} hacia {{ $solicitudVisible->nombre_destino }} · {{ $solicitudVisible->mensaje }}</small></div>
        @if($rolActual === 4)
        <form method="POST" action="{{ route('cargando.transferencia.ubicacion', $solicitudVisible->id_solicitud) }}">@csrf<button class="btn btn-warning">Transportando producto</button></form>
        @else
        <form method="POST" action="{{ route('completar.transferencia.ubicacion', $solicitudVisible->id_solicitud) }}">@csrf<button class="btn btn-success">Terminar traslado</button></form>
        @endif
    </div>
    @endforeach
</div>
@endif

@if($errors->any() && !$errors->asignarStock->any() && !$errors->transferirStock->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
    <strong class="d-block mb-1">Por favor revise los siguientes errores:</strong>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    @forelse($ubicaciones as $u)
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-building me-2 text-primary"></i>{{ $u->nombre_ubicacion }}
                    </h5>
                    <small class="text-muted">{{ $u->direccion ?? 'Sin dirección especificada' }}</small>
                </div>
                @if(in_array((int) session('rol'), [1, 3]))<div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarUbicacion{{ $u->id_ubicacion }}" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <a href="{{ route('eliminar.ubicacion', $u->id_ubicacion) }}" class="btn btn-outline-danger" onclick="return confirm('¿Desea eliminar esta ubicación y sus registros de stock?')" title="Eliminar">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>@endif
            </div>

            <div class="card-body p-3">
                <h6 class="fw-bold small text-uppercase text-muted mb-2">Inventario en esta Ubicación:</h6>
                <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Stock actual / mínimo</th>
                                <th class="text-center">Mensaje</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($u->detalles as $det)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark small">{{ $det->producto->nombre ?? 'Producto no encontrado' }}</div>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $det->producto->marca ?? '' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $det->producto && $det->cantidad <= $det->producto->stock_minimo ? 'bg-danger' : 'bg-primary bg-opacity-10 text-primary border border-primary' }} px-2 py-1">
                                        {{ $det->cantidad }} / {{ $det->producto->stock_minimo ?? 0 }} un.
                                    </span>
                                </td>
                                <td class="text-center small {{ isset($solicitudes[$det->id_detalle_ubicacion]) && $solicitudes[$det->id_detalle_ubicacion]->estado !== 'completada' ? 'text-warning fw-semibold' : 'text-muted' }}">
                                    {{ $solicitudes[$det->id_detalle_ubicacion]->mensaje ?? '—' }}
                                </td>
                                <td class="text-end">
                                    @php
                                        $solicitud = $solicitudes[$det->id_detalle_ubicacion] ?? null;
                                    @endphp
                                    @if(in_array((int) session('rol'), [1, 3]) && (!$solicitud || $solicitud->estado === 'completada'))
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSolicitar{{ $det->id_detalle_ubicacion }}"><i class="bi bi-truck me-1"></i>Solicitar transferencia</button>
                                    @elseif($solicitud && $solicitud->estado !== 'completada')
                                    <span class="badge {{ $solicitud->estado === 'transportando' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $solicitud->mensaje }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted small">
                                    No hay productos asignados a este almacén aún.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php
                    $recibidas = $solicitudes->where('id_ubicacion_origen', $u->id_ubicacion)->whereIn('estado', ['solicitada', 'transportando']);
                @endphp
                @if($recibidas->isNotEmpty())
                <div class="mt-3 border-top pt-3">
                    <h6 class="fw-bold small text-uppercase text-muted mb-2"><i class="bi bi-truck me-1"></i>Solicitudes recibidas para este almacén</h6>
                    @foreach($recibidas as $recibida)
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2 mb-2">
                        <div class="small"><strong>{{ $recibida->nombre_producto }}</strong> · {{ $recibida->cantidad }} un.<br><span class="text-muted">Destino: {{ $recibida->nombre_destino }} · {{ $recibida->mensaje }}</span></div>
                        @if((int) session('rol') === 4 && $recibida->estado === 'solicitada')
                        <form method="POST" action="{{ route('cargando.transferencia.ubicacion', $recibida->id_solicitud) }}">@csrf<button class="btn btn-sm btn-warning">Transportando producto</button></form>
                        @elseif(in_array((int) session('rol'), [1, 3]) && $recibida->estado === 'transportando')
                        <form method="POST" action="{{ route('completar.transferencia.ubicacion', $recibida->id_solicitud) }}">@csrf<button class="btn btn-sm btn-success">Terminar traslado</button></form>
                        @else
                        <span class="badge {{ $recibida->estado === 'transportando' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $recibida->mensaje }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR UBICACIÓN -->
    <div class="modal fade" id="modalEditarUbicacion{{ $u->id_ubicacion }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('actualizar.ubicacion') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_ubicacion" value="{{ $u->id_ubicacion }}">
                    <div class="modal-header">
                        <h5 class="modal-title fs-6 fw-bold">Actualizar Ubicación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de la Ubicación / Sucursal</label>
                            <input type="text" name="nombre_ubicacion" class="form-control" value="{{ $u->nombre_ubicacion }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Dirección / Referencia</label>
                            <input type="text" name="direccion" class="form-control" value="{{ $u->direccion }}">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-geo-alt fs-1 d-block text-secondary mb-2"></i>
        No hay almacenes o sucursales registradas. Registre una nueva ubicación para comenzar.
    </div>
    @endforelse
</div>

@if(in_array((int) session('rol'), [1, 3]))
@foreach($ubicaciones as $u)
    @foreach($u->detalles as $det)
        @if(!isset($solicitudes[$det->id_detalle_ubicacion]) || $solicitudes[$det->id_detalle_ubicacion]->estado === 'completada')
        <div class="modal fade" id="modalSolicitar{{ $det->id_detalle_ubicacion }}" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form method="POST" action="{{ route('solicitar.transferencia.ubicacion') }}">@csrf
                <input type="hidden" name="id_detalle_destino" value="{{ $det->id_detalle_ubicacion }}">
                <div class="modal-header"><h5 class="modal-title fs-6 fw-bold">Solicitar transporte de producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p class="mb-3"><strong>{{ $det->producto->nombre ?? 'Producto' }}</strong> para {{ $u->nombre_ubicacion }} (stock: {{ $det->cantidad }})</p>
                    <label class="form-label">Almacén que enviará el producto</label><select name="id_ubicacion_origen" class="form-select mb-3" required><option value="" selected disabled>Seleccione almacén...</option>@foreach($ubicaciones as $origen)@if($origen->id_ubicacion != $u->id_ubicacion)<option value="{{ $origen->id_ubicacion }}">{{ $origen->nombre_ubicacion }}</option>@endif @endforeach</select>
                    <label class="form-label">Cantidad solicitada</label><input class="form-control" type="number" name="cantidad" min="1" required>
                </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary">Enviar solicitud</button></div>
            </form>
        </div></div></div>
        @endif
    @endforeach
@endforeach
@endif

<!-- MODAL NUEVA UBICACIÓN -->
@if(in_array((int) session('rol'), [1, 3]))
<div class="modal fade" id="modalNuevaUbicacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('guardar.ubicacion') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold">Registrar Nueva Ubicación / Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($errors->asignarStock->any())
                    <div class="alert alert-danger py-2 small">
                        <strong>No se pudo asignar el stock:</strong>
                        <ul class="mb-0 ps-3">@foreach($errors->asignarStock->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de Ubicación / Sucursal</label>
                        <input type="text" name="nombre_ubicacion" class="form-control" placeholder="Ej. Almacén Central, Sucursal Norte" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Dirección / Referencia</label>
                        <input type="text" name="direccion" class="form-control" placeholder="Ej. Av. Principal #123">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Ubicación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ASIGNAR STOCK -->
<div class="modal fade" id="modalAsignarStock" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('asignar.stock.ubicacion') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold">Asignar / Distribuir Stock a Ubicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ubicación Destino</label>
                        <select name="id_ubicacion" class="form-select" required>
                            <option value="" disabled {{ old('id_ubicacion') ? '' : 'selected' }}>Seleccione una ubicación...</option>
                            @foreach($ubicaciones as $u)
                            <option value="{{ $u->id_ubicacion }}" {{ old('id_ubicacion') == $u->id_ubicacion ? 'selected' : '' }}>{{ $u->nombre_ubicacion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Producto</label>
                        <select name="id_producto" class="form-select" required>
                            <option value="" disabled {{ old('id_producto') ? '' : 'selected' }}>Seleccione un producto...</option>
                            @foreach($productos as $p)
                            <option value="{{ $p->id_producto }}" {{ old('id_producto') == $p->id_producto ? 'selected' : '' }}>{{ $p->nombre }} (Stock Total: {{ $p->stock }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cantidad a Asignar en esta Ubicación</label>
                        <input type="number" name="cantidad" min="0" value="{{ old('cantidad') }}" class="form-control" placeholder="Ej. 25" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->asignarStock->any())
<script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsignarStock')).show());</script>
@endif
@endif
@endsection
