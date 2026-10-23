@extends('adm_plantilla')

@section('content')

<style>.modal-holder,.modal-holder>td{height:0!important;padding:0!important;border:0!important}</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-bag-plus text-primary me-2"></i>Gestión de Compras y Reabastecimiento
        </h3>
        <p class="text-muted small mb-0">{{ count($compras) }} compra(s) encontrada(s) en el período mostrado.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('compras.imprimir.listado', request()->query()) }}" target="_blank" class="btn btn-outline-dark shadow-sm"><i class="bi bi-printer me-1"></i>Imprimir compras del período</a>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCompra">
            <i class="bi bi-plus-lg me-1"></i> + Nueva Compra
        </button>
    </div>
</div>

@if(session('mensaje'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- FILTROS DE COMPRAS -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light"><div class="card-body p-3">
    <form method="GET" action="{{ route('compras') }}" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small fw-semibold text-muted mb-1">Proveedor</label><select name="id_proveedor" class="form-select form-select-sm"><option value="">Todos los proveedores</option>@foreach($proveedores as $proveedor)<option value="{{ $proveedor->id_proveedor }}" {{ request('id_proveedor') == $proveedor->id_proveedor ? 'selected' : '' }}>{{ $proveedor->nombre_empresa }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label small fw-semibold text-muted mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde', $fechaDesde) }}"></div>
        <div class="col-md-3"><label class="form-label small fw-semibold text-muted mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta', $fechaHasta) }}"></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Filtrar</button><a href="{{ route('compras') }}" class="btn btn-sm btn-outline-secondary" title="Compras de hoy"><i class="bi bi-arrow-clockwise"></i></a></div>
    </form>
</div></div>

<!-- ========================================== -->
<!-- TABLA DE COMPRAS -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Proveedor</th>
                    <th>Registrado por</th>
                    <th>Fecha</th>
                    <th>N° Comprobante</th>
                    <th>Total</th>
                    <th class="text-end pe-4">Detalle</th>
                </tr>
            </thead>
            <tbody>
            @forelse($compras as $compra)
                @php
                    $totalC = $compra->detalles->sum(function($d) { return $d->cantidad * $d->precio_compra; });
                @endphp
                <tr>
                    <td class="ps-4 fw-bold">
                        <span class="badge bg-light text-dark border">#{{ $compra->id_compra }}</span>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $compra->proveedor->nombre_empresa ?? 'Sin proveedor' }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-person me-1"></i>{{ $compra->usuario->nombre ?? 'Usuario' }}
                        </span>
                    </td>
                    <td class="small text-muted">
                        {{ date('d/m/Y H:i', strtotime($compra->fecha)) }}
                    </td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border">
                            {{ $compra->nro_comprobante }}
                        </span>
                    </td>
                    <td class="fw-bold text-dark">
                        Bs. {{ number_format($totalC, 2) }}
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm"><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detalleCModal{{ $compra->id_compra }}"><i class="bi bi-eye me-1"></i>Ítems ({{ count($compra->detalles) }})</button><a href="{{ route('compras.comprobante', $compra->id_compra) }}" target="_blank" class="btn btn-outline-dark" title="Imprimir factura de compra"><i class="bi bi-printer"></i></a></div>
                    </td>
                </tr>

                <!-- Modal dentro de una fila válida para no romper el listado de compras. -->
                <tr class="modal-holder"><td colspan="7">
                <!-- Modal Detalle Compra -->
                <div class="modal fade" id="detalleCModal{{ $compra->id_compra }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fs-6 fw-bold">
                                    <i class="bi bi-bag-check me-2"></i>Detalle de Compra #{{ $compra->id_compra }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-2 mb-3 small">
                                    <div class="col-sm-6">
                                        <strong>Proveedor:</strong> {{ $compra->proveedor->nombre_empresa ?? 'N/A' }}
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>N° Factura:</strong> {{ $compra->nro_comprobante }}
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Fecha:</strong> {{ date('d/m/Y H:i', strtotime($compra->fecha)) }}
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Usuario:</strong> {{ $compra->usuario->nombre ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-end">Precio Compra</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($compra->detalles as $det)
                                            <tr>
                                                <td>{{ $det->producto->nombre ?? 'Producto #' . $det->id_producto }}</td>
                                                <td class="text-center fw-bold">{{ $det->cantidad }} un.</td>
                                                <td class="text-end">Bs. {{ number_format($det->precio_compra, 2) }}</td>
                                                <td class="text-end fw-semibold">Bs. {{ number_format($det->cantidad * $det->precio_compra, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <th colspan="3" class="text-end fw-bold">TOTAL:</th>
                                                <th class="text-end fw-bold fs-6 text-primary">Bs. {{ number_format($totalC, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <a href="{{ route('compras.comprobante', $compra->id_compra) }}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-printer me-1"></i>Imprimir factura</a>
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                </td></tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        No hay compras registradas actualmente.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>


<!-- ========================================== -->
<!-- MODAL REGISTRAR COMPRA -->
<!-- ========================================== -->

<div class="modal fade"
     id="modalCompra"
     tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">


            <div class="modal-header">

                <h5 class="modal-title">
                    Registrar nueva compra
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>


            <form action="{{ route('guardarcompra') }}"
                  method="POST">

                @csrf


                <div class="modal-body">


                    <!-- ================================= -->
                    <!-- DATOS DE LA COMPRA -->
                    <!-- ================================= -->

                    <h5 class="mb-3">
                        Datos de la compra
                    </h5>


                    <div class="row">


                        <div class="col-md-6 mb-3">

                            <label>
                                Proveedor
                            </label>

                            <select name="id_proveedor"
                                    class="form-control"
                                    required>

                                <option value="">
                                    Seleccionar proveedor
                                </option>

                                @foreach($proveedores as $proveedor)

                                    <option
                                        value="{{ $proveedor->id_proveedor }}">

                                        {{ $proveedor->nombre_empresa }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="col-md-3 mb-3">

                            <label>
                                Fecha
                            </label>

                            <input type="date"
                                   name="fecha"
                                   class="form-control"
                                   value="{{ date('Y-m-d') }}"
                                   required>

                        </div>


                        <div class="col-md-3 mb-3">

                            <label>
                                N° Comprobante
                            </label>

                            <input type="text" class="form-control" value="Generado automáticamente" disabled>

                        </div>

                    </div>


                    <hr>


                    <!-- ================================= -->
                    <!-- DETALLE DE PRODUCTOS -->
                    <!-- ================================= -->

                    <h5 class="mb-3">
                        Productos de la compra
                    </h5>


                    <div id="productos">


                        <div class="row producto">


                            <div class="col-md-5 mb-3">

                                <label>
                                    Producto
                                </label>

                                <select
                                    name="productos[0][id_producto]"
                                    class="form-control"
                                    required>

                                    <option value="">
                                        Seleccionar producto
                                    </option>

                                    @foreach($productos as $producto)

                                        <option
                                            value="{{ $producto->id_producto }}">

                                            {{ $producto->nombre }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            <div class="col-md-3 mb-3">

                                <label>
                                    Cantidad
                                </label>

                                <input
                                    type="number"
                                    name="productos[0][cantidad]"
                                    class="form-control"
                                    min="1"
                                    required>

                            </div>


                            <div class="col-md-3 mb-3">

                                <label>Datos del producto</label>
                                <input type="hidden" name="productos[0][precio_compra]" value="0">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="abrirModalProducto(this)">Actualizar producto</button>

                            </div>


                            <div class="col-md-1 d-flex align-items-end mb-3">

                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    onclick="eliminarProducto(this)">

                                    X

                                </button>

                            </div>


                        </div>

                    </div>


                    <!-- ================================= -->
                    <!-- BOTÓN AGREGAR PRODUCTO -->
                    <!-- ================================= -->

                    <button type="button"
                            class="btn btn-success"
                            onclick="agregarProducto()">

                        + Agregar producto

                    </button>
                    <a href="{{ route('productos') }}" target="_blank" class="btn btn-outline-primary">+ Nuevo producto</a>


                </div>


                <div class="modal-footer">


                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Cancelar

                    </button>


                    <button type="submit"
                            class="btn btn-primary">

                        Registrar compra

                    </button>


                </div>

            </form>

        </div>

    </div>

</div>


<div class="modal fade" id="modalActualizarProductoCompra" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form id="formActualizarProductoCompra"><div class="modal-header"><h5 class="modal-title">Actualizar producto para la compra</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="id_producto" id="editProductoId"><div class="mb-2"><label class="form-label small">Nombre</label><input name="nombre" id="editProductoNombre" class="form-control" required></div><div class="mb-2"><label class="form-label small">Marca</label><input name="marca" id="editProductoMarca" class="form-control"></div><div class="row g-2"><div class="col-6"><label class="form-label small">Costo de compra</label><input type="number" step="1" min="0.01" name="costo_compra" id="editProductoCosto" class="form-control" required></div><div class="col-6"><label class="form-label small">Mín. por mayor</label><input type="number" min="2" name="cantidad_min_mayor" id="editProductoMinimo" class="form-control" required></div><div class="col-6"><label class="form-label small">Precio por mayor</label><input type="number" step="1" min="0.01" name="precio_mayor" id="editProductoMayor" class="form-control" required></div><div class="col-6"><label class="form-label small">Precio por menor</label><input type="number" step="1" min="0.01" name="precio_menor" id="editProductoMenor" class="form-control" required></div></div><div id="errorActualizarProducto" class="text-danger small mt-3"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary">Guardar cambios</button></div></form></div></div></div>

<!-- ========================================== -->
<!-- JAVASCRIPT -->
<!-- ========================================== -->

<script>

let contador = 1;
const productosCompra = @json($productos->keyBy('id_producto'));
let filaProductoActiva = null;

function abrirModalProducto(boton) {
    filaProductoActiva = boton.closest('.producto');
    const id = filaProductoActiva.querySelector('select[name*="[id_producto]"]').value;
    const producto = productosCompra[id];
    if (!producto) { alert('Primero seleccione el producto que desea actualizar.'); return; }
    document.getElementById('editProductoId').value = producto.id_producto;
    document.getElementById('editProductoNombre').value = producto.nombre || '';
    document.getElementById('editProductoMarca').value = producto.marca || '';
    document.getElementById('editProductoCosto').value = producto.costo_compra || '';
    document.getElementById('editProductoMinimo').value = producto.cantidad_min_mayor || 2;
    document.getElementById('editProductoMayor').value = producto.precio_mayor || '';
    document.getElementById('editProductoMenor').value = producto.precio_menor || '';
    document.getElementById('errorActualizarProducto').textContent = '';
    new bootstrap.Modal(document.getElementById('modalActualizarProductoCompra')).show();
}

document.getElementById('formActualizarProductoCompra').addEventListener('submit', async function (event) {
    event.preventDefault();
    const error = document.getElementById('errorActualizarProducto'); error.textContent = '';
    const response = await fetch("{{ route('compras.actualizar.producto') }}", {
        method: 'POST', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: new FormData(this)
    });
    const data = await response.json();
    if (!response.ok) { error.textContent = data.message || 'No se pudo actualizar el producto.'; return; }
    productosCompra[data.producto.id_producto] = data.producto;
    bootstrap.Modal.getInstance(document.getElementById('modalActualizarProductoCompra')).hide();
});


function agregarProducto()
{

    let contenedor =
        document.getElementById('productos');


    let html = `

        <div class="row producto">


            <div class="col-md-5 mb-3">

                <label>
                    Producto
                </label>

                <select
                    name="productos[${contador}][id_producto]"
                    class="form-control"
                    required>

                    <option value="">
                        Seleccionar producto
                    </option>

                    @foreach($productos as $producto)

                        <option
                            value="{{ $producto->id_producto }}">

                            {{ $producto->nombre }}

                        </option>

                    @endforeach

                </select>

            </div>


            <div class="col-md-3 mb-3">

                <label>
                    Cantidad
                </label>

                <input
                    type="number"
                    name="productos[${contador}][cantidad]"
                    class="form-control"
                    min="1"
                    required>

            </div>


            <div class="col-md-3 mb-3">

                <label>Datos del producto</label>
                <input type="hidden" name="productos[${contador}][precio_compra]" value="0">
                <button type="button" class="btn btn-outline-primary w-100" onclick="abrirModalProducto(this)">Actualizar producto</button>

            </div>


            <div class="col-md-1 d-flex align-items-end mb-3">

                <button
                    type="button"
                    class="btn btn-danger"
                    onclick="eliminarProducto(this)">

                    X

                </button>

            </div>


        </div>

    `;


    contenedor.insertAdjacentHTML(
        'beforeend',
        html
    );


    contador++;

}


function eliminarProducto(boton)
{

    let producto =
        boton.closest('.producto');


    producto.remove();

}

</script>

@endsection
