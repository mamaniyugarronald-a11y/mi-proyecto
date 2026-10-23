@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-truck text-primary me-2"></i>Gestión de Proveedores Internacionales</h3>
            <p class="text-muted small mb-0">Directorio de proveedores del exterior, calificación de cumplimiento y canales directos</p>
        </div>
        <button class="btn btn-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalProveedor">
            <i class="bi bi-plus-circle-fill"></i> + Nuevo Proveedor
        </button>
    </div>

    <!-- Alertas Flash -->
    @if(session('mensaje'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Por favor corrige los errores:</strong>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-building me-2"></i>Lista de Proveedores Registrados ({{ count($proveedores) }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Empresa / Proveedor</th>
                        <th>Contacto</th>
                        <th>País de Origen</th>
                        <th>Teléfono / WhatsApp</th>
                        <th>Calificación</th>
                        <th class="text-center">Productos</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $proveedor)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border">#{{ $proveedor->id_proveedor }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $proveedor->nombre_empresa }}</div>
                            <small class="text-muted d-block">{{ $proveedor->email ?? 'Sin correo' }}</small>
                            @if($proveedor->direccion)
                                <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                    <i class="bi bi-geo-alt"></i> {{ $proveedor->direccion }}
                                </small>
                            @endif
                        </td>
                        <td class="text-secondary fw-medium">
                            <i class="bi bi-person me-1"></i> {{ $proveedor->contacto ?? 'General' }}
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-globe me-1"></i> {{ $proveedor->pais }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $proveedor->telefono }}</div>
                            @php
                                $limpioTel = preg_replace('/[^0-9]/', '', $proveedor->telefono);
                            @endphp
                            @if($limpioTel)
                            <a href="https://api.whatsapp.com/send?phone={{ $limpioTel }}&text=Hola%20estimados%20de%20{{ urlencode($proveedor->nombre_empresa) }},%20nos%20comunicamos%20desde%20Importadora%20PEBIE." 
                               target="_blank" 
                               class="badge bg-success text-decoration-none mt-1 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                            @endif
                        </td>
                        <td>
                            <div class="text-warning small" title="{{ $proveedor->calificacion }} de 5 estrellas">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $proveedor->calificacion)
                                        <i class="bi bi-star-fill"></i>
                                    @else
                                        <i class="bi bi-star text-muted opacity-25"></i>
                                    @endif
                                @endfor
                                <span class="text-dark fw-bold ms-1">({{ $proveedor->calificacion }}/5)</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                {{ $proveedor->productos_count ?? 0 }} productos asociados
                            </span>
                        </td>
                        <td>
                            @if($proveedor->estado == 1)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editar{{ $proveedor->id_proveedor }}" title="Editar Proveedor">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <a href="{{ route('eliminarproveedor', $proveedor->id_proveedor) }}"
                                   class="btn {{ $proveedor->estado == 1 ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                   onclick="return confirm('¿Está seguro de cambiar el estado de este proveedor?')">
                                    {{ $proveedor->estado == 1 ? 'Desactivar' : 'Activar' }}
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- MODAL EDITAR PROVEEDOR -->
                    <div class="modal fade" id="editar{{ $proveedor->id_proveedor }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Actualizar Proveedor #{{ $proveedor->id_proveedor }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form action="{{ route('actualizarproveedor') }}" method="POST">
                                    @csrf
                                    <div class="modal-body py-3">
                                        <input type="hidden" name="id_proveedor" value="{{ $proveedor->id_proveedor }}">

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Nombre de la Empresa *</label>
                                                <input type="text" name="nombre_empresa" class="form-control" value="{{ $proveedor->nombre_empresa }}" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Contacto</label>
                                                <input type="text" name="contacto" class="form-control" value="{{ $proveedor->contacto }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">País de Origen *</label>
                                                <input type="text" name="pais" class="form-control" value="{{ $proveedor->pais }}" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Teléfono / WhatsApp *</label>
                                                <input type="text" name="telefono" class="form-control" value="{{ $proveedor->telefono }}" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ $proveedor->email }}">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Dirección</label>
                                                <input type="text" name="direccion" class="form-control" value="{{ $proveedor->direccion }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Calificación de Cumplimiento</label>
                                                <select name="calificacion" class="form-select">
                                                    <option value="5" {{ $proveedor->calificacion == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 - Excelente)</option>
                                                    <option value="4" {{ $proveedor->calificacion == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (4 - Muy Bueno)</option>
                                                    <option value="3" {{ $proveedor->calificacion == 3 ? 'selected' : '' }}>⭐⭐⭐ (3 - Regular)</option>
                                                    <option value="2" {{ $proveedor->calificacion == 2 ? 'selected' : '' }}>⭐⭐ (2 - Bajo)</option>
                                                    <option value="1" {{ $proveedor->calificacion == 1 ? 'selected' : '' }}>⭐ (1 - Deficiente)</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Estado</label>
                                                <select name="estado" class="form-select">
                                                    <option value="1" {{ $proveedor->estado == 1 ? 'selected' : '' }}>Activo</option>
                                                    <option value="0" {{ $proveedor->estado == 0 ? 'selected' : '' }}>Inactivo</option>
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Notas y Observaciones</label>
                                                <textarea name="notas" class="form-control" rows="2">{{ $proveedor->notas }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary px-4">Actualizar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-truck fs-1 d-block mb-2 text-secondary"></i>
                            No hay proveedores registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL NUEVO PROVEEDOR -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('guardarproveedor') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    @if($errors->crearProveedor->any())
                    <div class="alert alert-danger py-2 small">
                        <strong>No se pudo registrar el proveedor:</strong>
                        <ul class="mb-0 ps-3">@foreach($errors->crearProveedor->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre de la Empresa *</label>
                            <input type="text" name="nombre_empresa" class="form-control" value="{{ old('nombre_empresa') }}" placeholder="Ej. Yiwu Import & Export Ltd." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contacto</label>
                            <input type="text" name="contacto" class="form-control" value="{{ old('contacto') }}" placeholder="Ej. Chen Wei">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">País de Origen *</label>
                            <input type="text" name="pais" class="form-control" value="{{ old('pais') }}" placeholder="Ej. China, Panamá, Brasil" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono / WhatsApp *</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej. +86 138 0000 0000" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="contacto@empresa.com">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección</label>
                            <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}" placeholder="Ciudad, dirección física">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Calificación Inicial</label>
                            <select name="calificacion" class="form-select">
                                <option value="5" @selected(old('calificacion', 5) == 5)>⭐⭐⭐⭐⭐ (5 - Excelente Cumplimiento)</option>
                                <option value="4" @selected(old('calificacion') == 4)>⭐⭐⭐⭐ (4 - Muy Bueno)</option>
                                <option value="3" @selected(old('calificacion') == 3)>⭐⭐⭐ (3 - Regular)</option>
                                <option value="2" @selected(old('calificacion') == 2)>⭐⭐ (2 - Bajo)</option>
                                <option value="1" @selected(old('calificacion') == 1)>⭐ (1 - Deficiente)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas / Observaciones</label>
                            <textarea name="notas" class="form-control" rows="2" placeholder="Condiciones de pago, tiempos de entrega en puerto, etc.">{{ old('notas') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Registrar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->crearProveedor->any())
<script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('modalProveedor')).show());</script>
@endif

@endsection
