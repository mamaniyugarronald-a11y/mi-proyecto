@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-3">

    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Clasificación de Productos</h3>
            <p class="text-muted small mb-0">Gestión de categorías y tipos de productos registrados</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTipo">
            <i class="bi bi-plus-circle me-1"></i> + Nuevo tipo
        </button>
    </div>

    <!-- Alertas de Estado -->
    @if(session('mensaje'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tabla Principal en Tarjeta -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre del tipo</th>
                        <th>Descripción</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tipos as $tipo)
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-light text-dark border">#{{ $tipo->id_tipo_producto }}</span>
                        </td>
                        <td class="fw-semibold text-dark">
                            {{ $tipo->nombre_tipo }}
                        </td>
                        <td class="text-secondary">
                            {{ $tipo->descripcion }}
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editar{{ $tipo->id_tipo_producto }}">
                                Editar
                            </button>
                            <a href="{{ route('eliminar.tipo.producto', $tipo->id_tipo_producto) }}" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('¿Desea eliminar este tipo de producto?')">
                                Eliminar
                            </a>
                        </td>
                    </tr>

                    <!-- ================================= -->
                    <!-- MODAL EDITAR TIPO -->
                    <!-- ================================= -->
                    <div class="modal fade" id="editar{{ $tipo->id_tipo_producto }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-bottom-0 pb-0">
                                    <h5 class="modal-title fw-bold">Actualizar tipo de producto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form action="{{ route('actualizar.tipo.producto') }}" method="POST">
                                    @csrf
                                    <div class="modal-body py-4">
                                        <input type="hidden" name="id_tipo_producto" value="{{ $tipo->id_tipo_producto }}">

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nombre del tipo</label>
                                            <input type="text" name="nombre_tipo" class="form-control" value="{{ $tipo->nombre_tipo }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Descripción</label>
                                            <textarea name="descripcion" class="form-control" rows="3" required>{{ $tipo->descripcion }}</textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary px-4">Actualizar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            No hay tipos de productos registrados actualmente.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ================================= -->
<!-- MODAL NUEVO TIPO -->
<!-- ================================= -->
<div class="modal fade" id="modalTipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Registrar tipo de producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('guardar.tipo.producto') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    @if($errors->crearTipo->any())
                    <div class="alert alert-danger py-2 small">
                        <strong>No se pudo registrar la categoría:</strong>
                        <ul class="mb-0 ps-3">@foreach($errors->crearTipo->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre del tipo</label>
                        <input type="text" name="nombre_tipo" class="form-control" value="{{ old('nombre_tipo') }}" placeholder="Ej: Electrónica, Ropa, etc." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Breve descripción de la categoría..." required>{{ old('descripcion') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->crearTipo->any())
<script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTipo')).show());</script>
@endif

@endsection
