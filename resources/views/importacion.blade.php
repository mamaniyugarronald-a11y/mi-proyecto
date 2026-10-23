@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-globe-americas text-primary me-2"></i>Gestión de Importaciones & Carga Internacional</h3>
            <p class="text-muted small mb-0">Órdenes de compra al exterior, seguimiento logístico en aduanas y costeo prorrateado de internación</p>
        </div>
        <button class="btn btn-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalImportacion">
            <i class="bi bi-plus-circle-fill"></i> + Nueva Orden de Importación
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

    <!-- Tabla de Importaciones -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-box-seam me-2"></i>Órdenes de Importación Registradas ({{ count($importaciones) }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Código Embarque</th>
                        <th>Proveedor / País</th>
                        <th>Fecha Pedido</th>
                        <th>Monto FOB ($us)</th>
                        <th>Gastos Internación (Bs.)</th>
                        <th>Total Inversión (Bs.)</th>
                        <th class="text-center">Estado Logístico</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($importaciones as $imp)
                    <tr>
                        <td class="ps-3">
                            <span class="fw-bold text-dark fs-6">{{ $imp->codigo_embarque }}</span>
                            <small class="text-muted d-block">ID #{{ $imp->id_importacion }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $imp->proveedor->nombre_empresa ?? 'Proveedor' }}</div>
                            <span class="badge bg-primary-subtle text-primary">{{ $imp->pais_origen }}</span>
                        </td>
                        <td class="small">
                            {{ date('d/m/Y', strtotime($imp->fecha_pedido)) }}
                            @if($imp->fecha_llegada_estimada)
                                <small class="text-muted d-block">Est: {{ date('d/m/Y', strtotime($imp->fecha_llegada_estimada)) }}</small>
                            @endif
                        </td>
                        <td>
                            <strong class="text-primary">$us {{ number_format($imp->monto_fob_usd, 2) }}</strong>
                            <small class="text-muted d-block">T/C: {{ number_format($imp->tipo_cambio, 2) }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold text-warning-emphasis">Bs. {{ number_format($imp->total_gastos_internacion, 2) }}</span>
                            <small class="text-muted d-block">Flete + Aduana + Aranceles</small>
                        </td>
                        <td>
                            <span class="badge bg-dark fs-6 fw-bold">
                                Bs. {{ number_format($imp->total_inversion_bs, 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($imp->estado_logistico === 'En tránsito')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                    <i class="bi bi-truck me-1"></i> En tránsito
                                </span>
                            @elseif($imp->estado_logistico === 'En Aduana')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                                    <i class="bi bi-shield-check me-1"></i> En Aduana
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <i class="bi bi-check-all me-1"></i> Recibido en Almacén
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('importaciones.detalle', $imp->id_importacion) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                <i class="bi bi-eye"></i> Costeo & Seguimiento
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-globe-americas fs-1 d-block mb-2 text-secondary"></i>
                            No hay órdenes de importación registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL REGISTRAR NUEVA ORDEN DE IMPORTACIÓN -->
<div class="modal fade" id="modalImportacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-globe-americas me-2"></i>Registrar Pedido de Importación al Exterior</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('guardar.importacion') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <!-- Datos Cabecera -->
                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Proveedor Extranjero *</label>
                            <select name="id_proveedor" class="form-select" required>
                                <option value="" disabled selected>-- Seleccione proveedor --</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_empresa }} ({{ $prov->pais }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Código / N° Contenedor *</label>
                            <input type="text" name="codigo_embarque" class="form-control" placeholder="Ej. CONT-CN-2026-09" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fecha de Pedido *</label>
                            <input type="date" name="fecha_pedido" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tipo Cambio (Bs/$us)</label>
                            <input type="number" step="1" min="1" name="tipo_cambio" id="impTipoCambio" class="form-control" value="6.96" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">País / Puerto de Origen *</label>
                            <input type="text" name="pais_origen" class="form-control" placeholder="Ej. Yiwu - China / Iquique" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha Estimada de Llegada</label>
                            <input type="date" name="fecha_llegada_estimada" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <input type="text" name="observaciones" class="form-control" placeholder="Incoterms FOB, agente naviero...">
                        </div>
                    </div>

                    <!-- Gastos de Internación Iniciales -->
                    <div class="row g-3 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-12">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calculator me-1"></i>Gastos Estimados de Internación (en Bs.)</h6>
                            <small class="text-muted">Estos costos se prorratearán entre las unidades de los productos importados para calcular el costo real</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Flete Internacional & Transporte (Bs.)</label>
                            <input type="number" step="1" min="0" name="costo_flete" class="form-control form-control-sm" value="0.00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Aduana & Gravamen Arancelario (Bs.)</label>
                            <input type="number" step="1" min="0" name="costo_aduana_aranceles" class="form-control form-control-sm" value="0.00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Seguro de Transporte (Bs.)</label>
                            <input type="number" step="1" min="0" name="costo_seguro" class="form-control form-control-sm" value="0.00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Otros Gastos / Almacenaje (Bs.)</label>
                            <input type="number" step="1" min="0" name="otros_gastos" class="form-control form-control-sm" value="0.00">
                        </div>
                    </div>

                    <!-- Lista de Productos a Importar -->
                    <div class="card border">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box me-1"></i>Artículos Solicitados</h6>
                            <button type="button" id="btnAgregarFilaImp" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-lg"></i> + Agregar Producto
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered align-middle mb-0" id="tablaProductosImp">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Producto</th>
                                        <th style="width: 140px;">Cantidad</th>
                                        <th style="width: 180px;">Precio Unit. FOB ($us)</th>
                                        <th style="width: 180px;">Subtotal ($us)</th>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="productos[0][id_producto]" class="form-select form-select-sm" required>
                                                <option value="" disabled selected>-- Seleccione producto --</option>
                                                @foreach($productos as $p)
                                                    <option value="{{ $p->id_producto }}">{{ $p->nombre }} (Stock: {{ $p->stock }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" min="1" name="productos[0][cantidad]" class="form-control form-control-sm inp-cant-imp" value="10" required>
                                        </td>
                                        <td>
                                            <input type="number" step="1" min="0.01" name="productos[0][precio_fob_usd]" class="form-control form-control-sm inp-precio-imp" value="5.00" required>
                                        </td>
                                        <td class="subtotal-imp fw-bold text-end pe-3">$us 50.00</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila-imp" disabled>
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Registrar Pedido de Importación</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let filaIndex = 1;
    const btnAgregar = document.getElementById('btnAgregarFilaImp');
    const tbody = document.querySelector('#tablaProductosImp tbody');

    function actualizarSubtotales() {
        const filas = tbody.querySelectorAll('tr');
        filas.forEach(fila => {
            const cant = parseFloat(fila.querySelector('.inp-cant-imp').value) || 0;
            const precio = parseFloat(fila.querySelector('.inp-precio-imp').value) || 0;
            const sub = cant * precio;
            fila.querySelector('.subtotal-imp').textContent = '$us ' + sub.toFixed(2);
        });
    }

    if (btnAgregar && tbody) {
        btnAgregar.addEventListener('click', function () {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select name="productos[${filaIndex}][id_producto]" class="form-select form-select-sm" required>
                        <option value="" disabled selected>-- Seleccione producto --</option>
                        @foreach($productos as $p)
                            <option value="{{ $p->id_producto }}">{{ $p->nombre }} (Stock: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" min="1" name="productos[${filaIndex}][cantidad]" class="form-control form-control-sm inp-cant-imp" value="10" required>
                </td>
                <td>
                    <input type="number" step="1" min="0.01" name="productos[${filaIndex}][precio_fob_usd]" class="form-control form-control-sm inp-precio-imp" value="5.00" required>
                </td>
                <td class="subtotal-imp fw-bold text-end pe-3">$us 50.00</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila-imp">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            filaIndex++;
            actualizarSubtotales();
        });

        tbody.addEventListener('input', function (e) {
            if (e.target.classList.contains('inp-cant-imp') || e.target.classList.contains('inp-precio-imp')) {
                actualizarSubtotales();
            }
        });

        tbody.addEventListener('click', function (e) {
            const btnEliminar = e.target.closest('.btn-eliminar-fila-imp');
            if (btnEliminar) {
                const fila = btnEliminar.closest('tr');
                if (tbody.querySelectorAll('tr').length > 1) {
                    fila.remove();
                    actualizarSubtotales();
                }
            }
        });
    }
});
</script>
@endsection
