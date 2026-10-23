@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-slash-circle text-danger me-2"></i>Registro de Mermas, Daños & Devoluciones</h3>
            <p class="text-muted small mb-0">Control de artículos rotos, defectuosos de importación o devoluciones de clientes con deducción automática de stock</p>
        </div>
        <button class="btn btn-danger shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalMerma">
            <i class="bi bi-plus-circle-fill"></i> + Registrar Merma / Baja
        </button>
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
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-list-check me-2"></i>Historial de Bajas Registradas ({{ count($mermas) }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Fecha</th>
                        <th>Producto Afectado</th>
                        <th>Categoría</th>
                        <th class="text-center">Cant. Descontada</th>
                        <th>Motivo de la Baja</th>
                        <th>Descripción del Daño / Justificación</th>
                        <th class="text-end pe-3">Registrado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mermas as $merma)
                    <tr>
                        <td class="ps-3 small">
                            <span class="fw-semibold text-dark">{{ date('d/m/Y', strtotime($merma->fecha)) }}</span>
                            <small class="text-muted d-block">{{ date('H:i', strtotime($merma->fecha)) }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $merma->producto->nombre ?? 'Producto no encontrado' }}</div>
                            <small class="text-muted">Marca: {{ $merma->producto->marca ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary">
                                {{ $merma->producto->tipoProducto->nombre_tipo ?? 'General' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6 fw-bold">
                                -{{ $merma->cantidad }} un.
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                {{ $merma->motivo }}
                            </span>
                        </td>
                        <td>
                            <span class="small text-dark">{{ $merma->descripcion_dano }}</span>
                        </td>
                        <td class="text-end pe-3 small text-muted">
                            <i class="bi bi-person me-1"></i> {{ $merma->usuario->nombre ?? 'Administrador' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                            No existen registros de mermas o pérdidas en el sistema.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL REGISTRAR MERMA -->
<div class="modal fade" id="modalMerma" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-slash-circle me-2"></i>Registrar Baja / Merma de Inventario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('guardar.merma') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Producto con Daño / Pérdida *</label>
                            <select name="id_producto" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione producto --</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id_producto }}">{{ $p->nombre }} (Stock disponible: {{ $p->stock }} un.)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cantidad a Dar de Baja *</label>
                            <input type="number" min="1" name="cantidad" class="form-control" value="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Motivo *</label>
                            <select name="motivo" class="form-select" required>
                                <option value="Dañado en importación">Dañado en importación</option>
                                <option value="Rotura en almacén" selected>Rotura en almacén</option>
                                <option value="Defecto de fábrica">Defecto de fábrica</option>
                                <option value="Devolución de cliente">Devolución de cliente</option>
                                <option value="Obsolescencia / Otro">Obsolescencia / Otro</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción del Daño / Justificación *</label>
                            <textarea name="descripcion_dano" class="form-control" rows="3" placeholder="Detalle qué ocurrió con el producto y el estado físico del mismo..." required></textarea>
                            <small class="text-danger mt-1 d-block"><i class="bi bi-info-circle"></i> Al confirmar, la cantidad se descontará inmediatamente del inventario disponible.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4"><i class="bi bi-check-lg me-1"></i>Confirmar Merma</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
