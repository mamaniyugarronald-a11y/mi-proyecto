@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado Principal -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-boxes text-primary me-2"></i>Gestión de Productos & Ficha Técnica</h3>
            <p class="text-muted small mb-0">Administra el catálogo de artículos para el hogar, stock mínimo, fotos y proveedores</p>
        </div>
        @if(session('rol') == 1)<div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalProducto">
                <i class="bi bi-plus-circle-fill"></i> + Nuevo Producto
            </button>
        </div>
        @endif
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
        <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Corrige los siguientes errores:</strong>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Banner Toast para Inserción Rápida de Proveedor -->
    <div id="toastProveedorRapido" class="alert alert-success d-none shadow-sm mb-3 align-items-center justify-content-between" role="alert">
        <div>
            <i class="bi bi-check2-circle fs-5 me-2"></i>
            <span id="toastProveedorTexto">Proveedor registrado y seleccionado exitosamente.</span>
        </div>
        <button type="button" class="btn-close" onclick="document.getElementById('toastProveedorRapido').classList.add('d-none')"></button>
    </div>

    <!-- Tabla Principal -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-list-columns-reverse me-2"></i>Inventario General ({{ count($productos) }} artículos)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 70px;">Foto</th>
                        <th>Producto / Descripción</th>
                        <th>Marca & Tipo</th>
                        <th>Proveedor</th>
                        <th>Precios</th>
                        <th class="text-center">Cant. Mayor</th>
                        <th class="text-center">Stock</th>
                        <th>Estado</th>
                        @if(session('rol') == 1)<th class="text-end pe-3">Acciones</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <td class="ps-3">
                            @if($producto->imagen_url)
                                <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="rounded border shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light text-muted rounded border d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-secondary fs-4"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $producto->nombre }}</div>
                            <small class="text-muted d-block text-truncate" style="max-width: 220px;">
                                {{ $producto->descripcion }}
                            </small>
                            <span class="badge bg-light text-muted border mt-1">#{{ $producto->id_producto }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-secondary d-block">{{ $producto->marca }}</span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                {{ $producto->tipoProducto->nombre_tipo ?? 'Sin tipo' }}
                            </span>
                        </td>
                        <td>
                            @if($producto->proveedor)
                                <div class="small fw-semibold text-dark">{{ $producto->proveedor->nombre_empresa }}</div>
                                <span class="badge bg-secondary-subtle text-secondary small">{{ $producto->proveedor->pais }}</span>
                            @else
                                <span class="text-muted small italic">Sin asignar</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <span class="text-muted">Menor:</span> <strong class="text-dark">Bs. {{ number_format($producto->precio_menor, 2) }}</strong>
                            </div>
                            <div class="small">
                                <span class="text-muted">Mayor:</span> <strong class="text-success">Bs. {{ number_format($producto->precio_mayor, 2) }}</strong>
                            </div>
                            @if($producto->costo_compra > 0)
                            <div class="small text-muted">
                                Costo: Bs. {{ number_format($producto->costo_compra, 2) }}
                            </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $producto->cantidad_min_mayor }} un.</span>
                        </td>
                        <td class="text-center">
                            @if($producto->stock <= 0)
                                <span class="badge bg-danger">Agotado (0)</span>
                            @elseif($producto->stock <= $producto->stock_minimo)
                                <span class="badge bg-warning text-dark border" title="Alerta: Stock igual o menor al mínimo ({{ $producto->stock_minimo }})">
                                    <i class="bi bi-exclamation-circle-fill text-danger me-1"></i> {{ $producto->stock }} un. (Bajo)
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                                    {{ $producto->stock }} un.
                                </span>
                            @endif
                            <small class="d-block text-muted" style="font-size: 0.72rem;">Mín: {{ $producto->stock_minimo }}</small>
                        </td>
                        <td>
                            @if($producto->estado == 1)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        @if(session('rol') == 1)<td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <!-- Kardex HU14 -->
                                <a href="{{ route('productos.kardex', $producto->id_producto) }}" class="btn btn-outline-info" title="Ver Kardex y Movimientos">
                                    <i class="bi bi-clock-history"></i> Kardex
                                </a>
                                <!-- Editar -->
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editar{{ $producto->id_producto }}" title="Editar Producto">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Eliminar -->
                                <a href="{{ route('eliminar.producto', $producto->id_producto) }}" 
                                   class="btn btn-outline-danger" 
                                   onclick="return confirm('¿Está seguro de eliminar este producto?')"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>@endif
                    </tr>

                    @if(session('rol') == 1)
                    <!-- MODAL EDITAR PRODUCTO -->
                    <div class="modal fade" id="editar{{ $producto->id_producto }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Actualizar Ficha de Producto #{{ $producto->id_producto }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form action="{{ route('actualizar.producto') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body py-3">
                                        <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">
                                        <div class="alert alert-danger d-none js-error-alert" role="alert"></div>

                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <label class="form-label fw-semibold">Nombre del producto *</label>
                                                <input type="text" name="nombre" class="form-control" value="{{ $producto->nombre }}" required>
                                            </div>

                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold">Marca *</label>
                                                <input type="text" name="marca" class="form-control" value="{{ $producto->marca }}" required>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Descripción detallada *</label>
                                                <textarea name="descripcion" class="form-control" rows="2" required>{{ $producto->descripcion }}</textarea>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Precio Menor (Bs.) *</label>
                                                <input type="number" step="1" min="0.01" name="precio_menor" class="form-control" value="{{ $producto->precio_menor }}" required>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Precio Mayor (Bs.) *</label>
                                                <input type="number" step="1" min="0.01" name="precio_mayor" class="form-control" value="{{ $producto->precio_mayor }}" required>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Costo Compra/Internación (Bs.)</label>
                                                <input type="number" step="1" min="0" name="costo_compra" class="form-control" value="{{ $producto->costo_compra }}">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Cant. Mínima Mayor *</label>
                                                <input type="number" min="2" name="cantidad_min_mayor" class="form-control" value="{{ $producto->cantidad_min_mayor }}" required>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Stock Actual *</label>
                                                <input type="number" min="0" name="stock" class="form-control" value="{{ $producto->stock }}" required>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Stock Mínimo Alerta *</label>
                                                <input type="number" min="1" name="stock_minimo" class="form-control" value="{{ $producto->stock_minimo }}" required>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Categoría / Tipo *</label>
                                                <select name="id_tipo_producto" class="form-select" required>
                                                    @foreach($tipos as $tipo)
                                                        <option value="{{ $tipo->id_tipo_producto }}" {{ $producto->id_tipo_producto == $tipo->id_tipo_producto ? 'selected' : '' }}>
                                                            {{ $tipo->nombre_tipo }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Proveedor Asignado</label>
                                                <select name="id_proveedor" class="form-select select-proveedor-lista">
                                                    <option value="">-- Sin proveedor --</option>
                                                    @foreach($proveedores as $prov)
                                                        <option value="{{ $prov->id_proveedor }}" {{ $producto->id_proveedor == $prov->id_proveedor ? 'selected' : '' }}>
                                                            {{ $prov->nombre_empresa }} ({{ $prov->pais }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Estado</label>
                                                <select name="estado" class="form-select" required>
                                                    <option value="1" {{ $producto->estado == 1 ? 'selected' : '' }}>Activo</option>
                                                    <option value="0" {{ $producto->estado == 0 ? 'selected' : '' }}>Inactivo</option>
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Cambiar Foto del Producto (Opcional)</label>
                                                <input type="file" name="imagen" class="form-control" accept="image/*">
                                                @if($producto->imagen)
                                                    <small class="text-success mt-1 d-block"><i class="bi bi-image"></i> Imagen actual guardada: {{ $producto->imagen }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                            No existen productos registrados actualmente.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ======================================================= -->
<!-- MODAL REGISTRAR NUEVO PRODUCTO (CON INSERTAR PROVEEDOR) -->
<!-- ======================================================= -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Producto para el Hogar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formNuevoProducto" action="{{ route('guardar.producto') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-4">

                    <!-- Alerta Dinámica JS en Modal -->
                    <div class="alert alert-danger d-none js-error-alert" role="alert"></div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Nombre del producto *</label>
                            <input type="text" name="nombre" id="prodNombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej. Juego de Vajilla Porcelana 24 piezas" required>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Marca *</label>
                            <input type="text" name="marca" id="prodMarca" class="form-control" value="{{ old('marca') }}" placeholder="Ej. Haier, Tramontina, Royal" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción detallada *</label>
                            <textarea name="descripcion" id="prodDescripcion" class="form-control" rows="2" placeholder="Material, especificaciones técnicas, dimensiones y uso recomendado..." required>{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Precio Menor (Bs.) *</label>
                            <input type="number" step="1" min="0.01" name="precio_menor" id="prodPrecioMenor" class="form-control" value="{{ old('precio_menor') }}" placeholder="0.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Precio Mayor (Bs.) *</label>
                            <input type="number" step="1" min="0.01" name="precio_mayor" id="prodPrecioMayor" class="form-control" value="{{ old('precio_mayor') }}" placeholder="0.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Costo Compra / Internación (Bs.)</label>
                            <input type="number" step="1" min="0" name="costo_compra" id="prodCostoCompra" class="form-control" value="{{ old('costo_compra', 0) }}" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cant. Mínima Mayor *</label>
                            <input type="number" min="2" name="cantidad_min_mayor" id="prodCantMinMayor" class="form-control" value="{{ old('cantidad_min_mayor', 3) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Inicial *</label>
                            <input type="number" min="0" name="stock" id="prodStock" class="form-control" value="{{ old('stock', 10) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Mínimo (Alerta) *</label>
                            <input type="number" min="1" name="stock_minimo" id="prodStockMinimo" class="form-control" value="{{ old('stock_minimo', 5) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo / Categoría *</label>
                            <select name="id_tipo_producto" id="prodTipo" class="form-select" required>
                                <option value="" disabled {{ old('id_tipo_producto') ? '' : 'selected' }}>-- Seleccione categoría --</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_producto }}" {{ old('id_tipo_producto') == $tipo->id_tipo_producto ? 'selected' : '' }}>
                                        {{ $tipo->nombre_tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- PROVEEDOR CON MODAL AJAX RÁPIDO (REQUERIMIENTO CLAVE) -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Proveedor Asignado</label>
                            <div class="input-group">
                                <select name="id_proveedor" id="selectProveedorNuevo" class="form-select select-proveedor-lista">
                                    <option value="">-- Sin proveedor / General --</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id_proveedor }}" {{ old('id_proveedor') == $prov->id_proveedor ? 'selected' : '' }}>
                                            {{ $prov->nombre_empresa }} ({{ $prov->pais }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary fw-semibold" id="btnAbrirModalProveedorRapido" title="Registrar nuevo proveedor al instante sin perder datos">
                                    <i class="bi bi-plus-lg"></i> + Proveedor
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Foto / Imagen Fotográfica</label>
                            <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                            <small class="text-muted">Obligatoria. Se guardará en <code>public/img/productos</code>. Formato JPG, PNG o WEBP (máx. 5 MB).</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="estado" class="form-select" required>
                                <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Registrar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- SUB-MODAL AJAX: REGISTRO RÁPIDO DE PROVEEDOR           -->
<!-- ======================================================= -->
<div class="modal fade" id="modalProveedorRapido" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-truck me-2"></i>Registrar Proveedor Rápido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formProveedorRapido">
                <div class="modal-body py-3">
                    <div id="alertProveedorRapido" class="alert alert-danger d-none py-2 small"></div>

                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle text-primary me-1"></i> Este proveedor se guardará al instante y quedará seleccionado automáticamente en el producto que estás creando, sin borrar tus datos escritos.
                    </p>

                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Nombre de la Empresa *</label>
                            <input type="text" id="rapido_empresa" class="form-control form-control-sm" placeholder="Ej. Yiwu Home Appliances Co." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">País de Origen *</label>
                            <input type="text" id="rapido_pais" class="form-control form-control-sm" placeholder="Ej. China, Brasil, Panamá" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Teléfono / WhatsApp *</label>
                            <input type="text" id="rapido_telefono" class="form-control form-control-sm" placeholder="Ej. +86 138 0000 0000" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nombre Contacto</label>
                            <input type="text" id="rapido_contacto" class="form-control form-control-sm" placeholder="Ej. Li Wei / Ventas">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Correo Electrónico</label>
                            <input type="email" id="rapido_email" class="form-control form-control-sm" placeholder="ventas@empresa.com">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Calificación Inicial (1 a 5 estrellas)</label>
                            <select id="rapido_calificacion" class="form-select form-select-sm">
                                <option value="5" selected>⭐⭐⭐⭐⭐ (Excelente - 5 Estrellas)</option>
                                <option value="4">⭐⭐⭐⭐ (Muy Bueno - 4 Estrellas)</option>
                                <option value="3">⭐⭐⭐ (Bueno / Regular - 3 Estrellas)</option>
                                <option value="2">⭐⭐ (Bajo Cumplimiento - 2 Estrellas)</option>
                                <option value="1">⭐ (Deficiente - 1 Estrella)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarProveedorRapido" class="btn btn-primary btn-sm px-3">
                        <span id="spinnerProveedorRapido" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                        <i class="bi bi-check2 me-1"></i> Guardar y Seleccionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // 1. VALIDACIÓN EN TIEMPO REAL: PRECIO MENOR > PRECIO MAYOR
    function validarPreciosFormulario(form) {
        const pMenorInput = form.querySelector('[name="precio_menor"]');
        const pMayorInput = form.querySelector('[name="precio_mayor"]');
        const alertBox = form.querySelector('.js-error-alert');

        if (!pMenorInput || !pMayorInput) return true;

        const precioMenor = parseFloat(pMenorInput.value) || 0;
        const precioMayor = parseFloat(pMayorInput.value) || 0;

        if (precioMenor <= precioMayor) {
            if (alertBox) {
                alertBox.textContent = 'El precio al por menor debe ser estrictamente mayor que el precio al por mayor.';
                alertBox.classList.remove('d-none');
            } else {
                alert('El precio al por menor debe ser estrictamente mayor que el precio al por mayor.');
            }
            pMenorInput.focus();
            return false;
        }

        if (alertBox) {
            alertBox.classList.add('d-none');
        }
        return true;
    }

    const formCrear = document.querySelector('#modalProducto form');
    if (formCrear) {
        formCrear.addEventListener('submit', function (e) {
            if (!validarPreciosFormulario(this)) {
                e.preventDefault();
            }
        });
    }

    const formsEditar = document.querySelectorAll('[id^="editar"] form');
    formsEditar.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!validarPreciosFormulario(this)) {
                e.preventDefault();
            }
        });
    });

    // 2. MODAL RÁPIDO DE PROVEEDOR VÍA AJAX (SIN PERDER DATOS DEL PRODUCTO)
    const btnAbrirRapido = document.getElementById('btnAbrirModalProveedorRapido');
    const modalRapidoEl = document.getElementById('modalProveedorRapido');
    const modalProductoEl = document.getElementById('modalProducto');
    let modalRapidoBS = null;
    if (modalRapidoEl) {
        modalRapidoBS = new bootstrap.Modal(modalRapidoEl);
    }

    if (btnAbrirRapido && modalRapidoBS) {
        btnAbrirRapido.addEventListener('click', function () {
            document.getElementById('formProveedorRapido').reset();
            document.getElementById('alertProveedorRapido').classList.add('d-none');
            modalRapidoBS.show();
        });
    }

    const btnGuardarRapido = document.getElementById('btnGuardarProveedorRapido');
    if (btnGuardarRapido) {
        btnGuardarRapido.addEventListener('click', function () {
            const empresa = document.getElementById('rapido_empresa').value.trim();
            const pais = document.getElementById('rapido_pais').value.trim();
            const telefono = document.getElementById('rapido_telefono').value.trim();
            const contacto = document.getElementById('rapido_contacto').value.trim();
            const email = document.getElementById('rapido_email').value.trim();
            const calificacion = document.getElementById('rapido_calificacion').value;
            const alertBox = document.getElementById('alertProveedorRapido');
            const spinner = document.getElementById('spinnerProveedorRapido');

            if (!empresa || !pais || !telefono) {
                alertBox.textContent = 'Por favor completa los campos obligatorios (Empresa, País y Teléfono).';
                alertBox.classList.remove('d-none');
                return;
            }

            // Iniciar petición AJAX
            btnGuardarRapido.disabled = true;
            spinner.classList.remove('d-none');
            alertBox.classList.add('d-none');

            fetch("{{ route('guardarproveedor.rapido') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    nombre_empresa: empresa,
                    pais: pais,
                    telefono: telefono,
                    contacto: contacto,
                    email: email,
                    calificacion: calificacion
                })
            })
            .then(res => res.json())
            .then(data => {
                btnGuardarRapido.disabled = false;
                spinner.classList.add('d-none');

                if (data.success && data.proveedor) {
                    const prov = data.proveedor;

                    // Agregar y seleccionar la nueva opción en los selectores de proveedor
                    const selectores = document.querySelectorAll('.select-proveedor-lista');
                    selectores.forEach(sel => {
                        const nuevaOpcion = new Option(`${prov.nombre_empresa} (${prov.pais})`, prov.id_proveedor, true, true);
                        sel.add(nuevaOpcion);
                    });

                    // Cerrar el modal rápido
                    modalRapidoBS.hide();

                    // Mostrar toast de éxito
                    const toast = document.getElementById('toastProveedorRapido');
                    const toastTexto = document.getElementById('toastProveedorTexto');
                    if (toast && toastTexto) {
                        toastTexto.innerHTML = `<strong>¡Proveedor "${prov.nombre_empresa}" añadido!</strong> Ha sido seleccionado automáticamente en tu producto sin perder tus datos escritos.`;
                        toast.classList.remove('d-none');
                        setTimeout(() => toast.classList.add('d-none'), 8000);
                    }
                } else {
                    alertBox.textContent = data.mensaje || 'Error al guardar el proveedor.';
                    alertBox.classList.remove('d-none');
                }
            })
            .catch(err => {
                btnGuardarRapido.disabled = false;
                spinner.classList.add('d-none');
                alertBox.textContent = 'Error de conexión al servidor: ' + err.message;
                alertBox.classList.remove('d-none');
            });
        });
    }

});
</script>
@endsection
