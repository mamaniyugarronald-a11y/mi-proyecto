@extends('adm_plantilla')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-calculator text-primary me-2"></i>Terminal Punto de Venta (POS)
        </h3>
        <p class="text-muted small mb-0">Facturación rápida, tarifas mayoristas automáticas, control de descuentos y medios de pago</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ventas.directas') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-receipt me-1"></i> Historial de Ventas
        </a>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('guardar.venta') }}" method="POST" id="formVentaPOS">
    @csrf

    <div class="row g-3">
        <!-- COLUMNA IZQUIERDA: CATÁLOGO Y BUSCADOR DE PRODUCTOS -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-boxes text-primary me-2"></i>Catálogo Disponible (HU16)
                        </h6>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ count($productos) }} artículos
                        </span>
                    </div>
                    <!-- Buscador en tiempo real -->
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscarProducto" class="form-control border-start-0" placeholder="Buscar por nombre, código, categoría o marca...">
                    </div>
                </div>

                <div class="card-body p-2" style="max-height: 560px; overflow-y: auto;">
                    <div class="row g-2" id="listaProductos">
                        @forelse($productos as $prod)
                        <div class="col-sm-6 item-producto" 
                             data-nombre="{{ strtolower($prod->nombre) }}" 
                             data-marca="{{ strtolower($prod->marca) }}"
                             data-categoria="{{ strtolower($prod->tipoProducto->nombre_tipo ?? '') }}"
                             data-id="{{ $prod->id_producto }}"
                             data-stock="{{ $prod->stock }}"
                             data-pmenor="{{ $prod->precio_menor }}"
                             data-pmayor="{{ $prod->precio_mayor }}"
                             data-minmayor="{{ $prod->cantidad_min_mayor }}">
                            <div class="card h-100 border rounded-3 p-2 position-relative hover-shadow transition">
                                <div class="d-flex gap-2">
                                    @if($prod->imagen_url)
                                        <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre }}" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="bi bi-box-seam text-secondary fs-5"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 130px;" title="{{ $prod->nombre }}">
                                                {{ $prod->nombre }}
                                            </h6>
                                            <span class="badge {{ $prod->stock > 5 ? 'bg-success-subtle text-success' : ($prod->stock > 0 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }} border" style="font-size: 0.68rem;">
                                                Stock: {{ $prod->stock }}
                                            </span>
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.72rem;">
                                            {{ $prod->marca }} &bull; {{ $prod->tipoProducto->nombre_tipo ?? 'General' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-baseline mt-2 pt-2 border-top">
                                    <div>
                                        <div class="small fw-bold text-primary">Bs. {{ number_format($prod->precio_menor, 2) }} <span class="text-muted fw-normal" style="font-size: 0.68rem;">(Menor)</span></div>
                                        <div class="text-success fw-semibold" style="font-size: 0.72rem;">Bs. {{ number_format($prod->precio_mayor, 2) }} (≥{{ $prod->cantidad_min_mayor }} un.)</div>
                                    </div>
                                    <button type="button" 
                                            class="btn btn-sm btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center btn-agregar-item" 
                                            style="width: 30px; height: 30px;"
                                            data-id="{{ $prod->id_producto }}"
                                            data-nombre="{{ $prod->nombre }}"
                                            data-stock="{{ $prod->stock }}"
                                            data-pmenor="{{ $prod->precio_menor }}"
                                            data-pmayor="{{ $prod->precio_mayor }}"
                                            data-minmayor="{{ $prod->cantidad_min_mayor }}"
                                            {{ $prod->stock <= 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-4 text-muted">No hay productos disponibles en inventario.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: DATOS DE VENTA, CARRITO Y TOTALES -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-receipt-cutoff text-primary me-2"></i>Detalle de la Transacción & Comprobante
                    </h6>
                </div>

                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <!-- Seleccionar Cliente -->
                        <div class="col-12">
                            <label class="form-label small fw-bold mb-1">Cliente <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <select name="id_cliente" id="selectCliente" class="form-select" required>
                                    <option value="" disabled selected>Seleccione cliente...</option>
                                    @foreach($clientes as $c)
                                    <option value="{{ $c->id_cliente }}" data-tipo="{{ $c->tipo_registro }}">
                                        {{ $c->nombre_completo }} ({{ $c->tipo_registro }})
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalClienteRapido" title="Registrar nuevo cliente">
                                    <i class="bi bi-person-plus-fill"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tipo de Venta -->
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold mb-1">Modalidad</label>
                            <select name="tipo_venta" id="tipoVenta" class="form-select form-select-sm">
                                <option value="Venta Directa" selected>Venta Directa</option>
                                <option value="Reserva">Reserva</option>
                            </select>
                            <small class="text-muted d-block mt-1" id="ayudaModalidad">La venta directa se completa y contabiliza inmediatamente.</small>
                        </div>


                        <!-- Método de Pago -->
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold mb-1"><i class="bi bi-cash me-1 text-success"></i>Medio de Pago</label>
                            <select name="metodo_pago" class="form-select form-select-sm">
                                <option value="Efectivo" selected>Efectivo (Caja Física)</option>
                                <option value="Transferencia / QR">Transferencia / QR Simple</option>
                                <option value="Mixto">Pago Mixto</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label small fw-bold mb-1">Promoción</label>
                            <select name="promocion" id="promocion" class="form-select form-select-sm">
                                <option value="Ninguna">Ninguna</option><option value="Transporte">Transporte</option><option value="Precio">Precio</option>
                            </select>
                        </div>

                        <div class="col-sm-6" id="grupoDespacho">
                            <label class="form-label small fw-bold mb-1"><i class="bi bi-truck me-1 text-primary"></i>Estado de despacho</label>
                            <select name="estado_despacho" id="estadoDespacho" class="form-select form-select-sm">
                                <option value="Pendiente" selected>Pendiente</option>
                                <option value="Despachado">Despachando</option>
                                <option value="Entregado">Entregado al cliente</option>
                            </select>
                        </div>

                    </div>

                    <!-- TABLA DINÁMICA DE PRODUCTOS AGREGADOS -->
                    <div class="table-responsive mb-2 border rounded-3" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" id="tablaCarrito">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Producto</th>
                                    <th style="width: 80px;">Cant.</th>
                                    <th style="width: 95px;">P. Unit.</th>
                                    <th style="width: 90px;" class="text-end">Subtotal</th>
                                    <th style="width: 30px;"></th>
                                </tr>
                            </thead>
                            <tbody id="carritoBody">
                                <tr id="filaVacia">
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        <i class="bi bi-cart3 fs-3 d-block text-secondary mb-1"></i>
                                        Haga clic en "+" en los productos del catálogo para agregarlos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- DESCUENTOS Y TOTALES (HU21) -->
                    <div class="card bg-light border-0 p-3 rounded-3 mb-3">
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-6">
                                <span class="text-muted small">Subtotal Artículos:</span>
                            </div>
                            <div class="col-6 text-end">
                                <strong class="text-dark" id="txtSubtotal">Bs. 0.00</strong>
                            </div>
                        </div>

                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-0 fw-semibold text-danger"><i class="bi bi-tag-fill me-1"></i>Descuento (Bs.):</label>
                            </div>
                            <div class="col-6 text-end">
                                <input type="number" step="1" min="0" name="descuento" id="inpDescuento" class="form-control form-control-sm text-end fw-semibold text-danger d-inline-block" style="max-width: 120px;" value="0.00">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="fs-5 fw-bold text-dark">TOTAL A COBRAR:</span>
                            <span class="fs-4 fw-bold text-primary" id="txtTotalPagar">Bs. 0.00</span>
                        </div>
                    </div>

                    <!-- BOTÓN DE FINALIZACIÓN -->
                    <button type="submit" id="btnConfirmarVenta" class="btn btn-success w-100 py-2 fs-6 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" disabled>
                        <i class="bi bi-check2-circle fs-5"></i> Procesar Venta / Emitir Comprobante
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modalClienteRapido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form id="formClienteRapido">
            <div class="modal-header"><h5 class="modal-title">Registrar nuevo cliente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label small fw-bold">Tipo de cliente</label><select name="tipo_registro" id="tipoClienteRapido" class="form-select" required><option value="Unipersonal">Unipersonal</option><option value="Institucional">Institucional</option></select></div>
                <div class="col-md-6"><label class="form-label small fw-bold">Teléfono</label><input required name="telefono" class="form-control" maxlength="20"></div>
                <div class="col-md-6"><label class="form-label small fw-bold">Correo electrónico</label><input required type="email" name="email" class="form-control"></div>
                <div class="col-md-6"><label class="form-label small fw-bold">Dirección</label><input required name="direccion" class="form-control"></div>
            </div>
            <div id="camposUnipersonal" class="row g-3 mt-1"><div class="col-md-4"><label class="form-label small fw-bold">Nombre</label><input required name="nombre" class="form-control campo-uni"></div><div class="col-md-4"><label class="form-label small fw-bold">Apellido</label><input required name="apellido" class="form-control campo-uni"></div><div class="col-md-4"><label class="form-label small fw-bold">C.I.</label><input required name="ci" class="form-control campo-uni"></div></div>
            <div id="camposInstitucional" class="row g-3 mt-1 d-none"><div class="col-md-4"><label class="form-label small fw-bold">Razón social</label><input name="razon_social" class="form-control campo-ins"></div><div class="col-md-4"><label class="form-label small fw-bold">NIT</label><input name="nit" class="form-control campo-ins"></div><div class="col-md-4"><label class="form-label small fw-bold">Nombre de contacto</label><input name="contacto_nombre" class="form-control campo-ins"></div></div>
            <div id="clienteRapidoError" class="text-danger small mt-3"></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary">Guardar y seleccionar</button></div>
        </form>
    </div></div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const carrito = [];
    const carritoBody = document.getElementById('carritoBody');
    const txtSubtotal = document.getElementById('txtSubtotal');
    const txtTotalPagar = document.getElementById('txtTotalPagar');
    const inpDescuento = document.getElementById('inpDescuento');
    const btnConfirmar = document.getElementById('btnConfirmarVenta');
    const promocion = document.getElementById('promocion');
    const tipoVenta = document.getElementById('tipoVenta');
    const grupoDespacho = document.getElementById('grupoDespacho');
    const estadoDespacho = document.getElementById('estadoDespacho');
    const ayudaModalidad = document.getElementById('ayudaModalidad');
    const selectCliente = document.getElementById('selectCliente');

    // 1. Filtrar productos en vivo
    const buscarInput = document.getElementById('buscarProducto');
    buscarInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.item-producto').forEach(item => {
            const nombre = item.getAttribute('data-nombre');
            const marca = item.getAttribute('data-marca');
            const cat = item.getAttribute('data-categoria');
            if (nombre.includes(query) || marca.includes(query) || cat.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    function actualizarModalidad() {
        const esReserva = tipoVenta.value === 'Reserva';
        grupoDespacho.style.display = promocion.value === 'Transporte' ? '' : 'none';
        // En venta directa el despacho comienza pendiente y se finaliza desde el historial.
        estadoDespacho.disabled = !esReserva;
        if (!esReserva) estadoDespacho.value = 'Pendiente';
        ayudaModalidad.textContent = esReserva
            ? (promocion.value === 'Transporte'
                ? 'La reserva inicia Pendiente; debe confirmarse, despacharse y completarse para contabilizarse.'
                : 'La reserva inicia Pendiente; debe confirmarse y completarse para contabilizarse.')
            : (promocion.value === 'Transporte'
                ? 'La venta directa queda pendiente y se contabiliza al confirmar la entrega desde el historial.'
                : 'La venta directa se completa y contabiliza inmediatamente.');
    }
    promocion.addEventListener('change', actualizarModalidad);
    tipoVenta.addEventListener('change', actualizarModalidad);
    actualizarModalidad();
    inpDescuento.addEventListener('input', recalcularCarrito);

    // 3. Botones Agregar Producto
    document.querySelectorAll('.btn-agregar-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = parseInt(this.getAttribute('data-id'));
            const nombre = this.getAttribute('data-nombre');
            const stock = parseInt(this.getAttribute('data-stock'));
            const pmenor = parseFloat(this.getAttribute('data-pmenor'));
            const pmayor = parseFloat(this.getAttribute('data-pmayor'));
            const minmayor = parseInt(this.getAttribute('data-minmayor'));

            const existe = carrito.find(item => item.id === id);

            if (existe) {
                if (existe.cantidad + 1 > stock) {
                    alert('No hay más unidades en stock disponibles para este producto (' + stock + ' máx).');
                    return;
                }
                existe.cantidad++;
            } else {
                if (stock <= 0) {
                    alert('Este producto no cuenta con stock disponible.');
                    return;
                }
                carrito.push({
                    id: id,
                    nombre: nombre,
                    stock: stock,
                    pmenor: pmenor,
                    pmayor: pmayor,
                    minmayor: minmayor,
                    cantidad: 1,
                    precio: pmenor
                });
            }

            renderCarrito();
        });
    });

    // 4. Renderizar Carrito y calcular precios dinámicos
    function renderCarrito() {
        if (carrito.length === 0) {
            carritoBody.innerHTML = `
                <tr id="filaVacia">
                    <td colspan="5" class="text-center py-4 text-muted small">
                        <i class="bi bi-cart3 fs-3 d-block text-secondary mb-1"></i>
                        Haga clic en "+" en los productos del catálogo para agregarlos.
                    </td>
                </tr>
            `;
            txtSubtotal.textContent = 'Bs. 0.00';
            txtTotalPagar.textContent = 'Bs. 0.00';
            btnConfirmar.disabled = true;
            return;
        }

        let html = '';
        let subtotalAcum = 0;
        const esTarifaMayorista = false;

        carrito.forEach((item, index) => {
            if (esTarifaMayorista || item.cantidad >= item.minmayor) {
                item.precio = item.pmayor;
            } else {
                item.precio = item.pmenor;
            }

            const itemSub = item.cantidad * item.precio;
            subtotalAcum += itemSub;

            html += `
                <tr>
                    <td>
                        <div class="fw-semibold text-dark small text-truncate" style="max-width: 130px;">${item.nombre}</div>
                        <input type="hidden" name="productos[${index}][id_producto]" value="${item.id}">
                        <input type="hidden" name="productos[${index}][precio]" value="${item.precio}">
                        ${item.precio === item.pmayor ? '<span class="badge bg-success" style="font-size: 0.65rem;">Mayor</span>' : ''}
                    </td>
                    <td>
                        <input type="number" 
                               name="productos[${index}][cantidad]" 
                               class="form-control form-control-sm text-center input-cant" 
                               data-index="${index}" 
                               min="1" 
                               max="${item.stock}" 
                               value="${item.cantidad}" 
                               required>
                    </td>
                    <td class="small fw-semibold text-dark">
                        Bs. ${item.precio.toFixed(2)}
                    </td>
                    <td class="text-end small fw-bold text-primary">
                        Bs. ${itemSub.toFixed(2)}
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-eliminar-item" data-index="${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
    });

        carritoBody.innerHTML = html;
        txtSubtotal.textContent = 'Bs. ' + subtotalAcum.toFixed(2);

        const desc = parseFloat(inpDescuento.value) || 0;
        let totalFinal = subtotalAcum - desc;
        if (totalFinal < 0) totalFinal = 0;

        txtTotalPagar.textContent = 'Bs. ' + totalFinal.toFixed(2);
        btnConfirmar.disabled = false;

        // Escuchadores de inputs de cantidad
        document.querySelectorAll('.input-cant').forEach(input => {
            input.addEventListener('change', function() {
                const idx = parseInt(this.getAttribute('data-index'));
                let val = parseInt(this.value) || 1;
                if (val < 1) val = 1;
                if (val > carrito[idx].stock) {
                    alert('La cantidad supera el stock disponible (' + carrito[idx].stock + ' máx).');
                    val = carrito[idx].stock;
                }
                carrito[idx].cantidad = val;
                renderCarrito();
            });
        });

        // Escuchadores de botones eliminar
        document.querySelectorAll('.btn-eliminar-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.getAttribute('data-index'));
                carrito.splice(idx, 1);
                renderCarrito();
            });
        });
    }

    function recalcularCarrito() {
        renderCarrito();
    }

    const tipoClienteRapido = document.getElementById('tipoClienteRapido');
    function cambiarTipoClienteRapido() {
        const institucional = tipoClienteRapido.value === 'Institucional';
        document.getElementById('camposUnipersonal').classList.toggle('d-none', institucional);
        document.getElementById('camposInstitucional').classList.toggle('d-none', !institucional);
        document.querySelectorAll('.campo-uni').forEach(input => input.required = !institucional);
        document.querySelectorAll('.campo-ins').forEach(input => input.required = institucional);
    }
    tipoClienteRapido.addEventListener('change', cambiarTipoClienteRapido);
    cambiarTipoClienteRapido();

    document.getElementById('formClienteRapido').addEventListener('submit', async function(e) {
        e.preventDefault(); const error = document.getElementById('clienteRapidoError'); error.textContent = '';
        const response = await fetch("{{ route('guardarcliente') }}", {method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('input[name=_token]').value}, body:new FormData(this)});
        const data = await response.json(); if (!response.ok) { error.textContent = data.message || 'No se pudo registrar el cliente.'; return; }
        selectCliente.add(new Option(data.nombre + ' (' + data.tipo + ')', data.id, true, true));
        bootstrap.Modal.getInstance(document.getElementById('modalClienteRapido')).hide(); this.reset(); cambiarTipoClienteRapido(); recalcularCarrito();
    });
});
</script>
@endsection
