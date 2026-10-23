<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $empresa->razon_social }} - Catálogo Virtual de Artículos para el Hogar</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand-primary: #0f172a;
            --brand-accent: #2563eb;
            --brand-green: #059669;
            --bg-canvas: #f8fafc;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
        }

        body {
            background-color: var(--bg-canvas);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        /* HEADER HERO */
        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e3a8a 100%);
            color: #ffffff;
            padding: 50px 20px;
            border-radius: 0 0 28px 28px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.15);
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.3) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* PRODUCT CARDS */
        .card-producto {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
            transition: all 0.25s ease;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-producto:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.12);
            border-color: #cbd5e1;
        }

        .img-container {
            height: 210px;
            background: #f1f5f9;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .card-producto:hover .img-container img {
            transform: scale(1.06);
        }

        .badge-cat {
            position: absolute;
            top: 12px;
            left: 12px;
            backdrop-filter: blur(8px);
            background: rgba(15, 23, 42, 0.75);
            color: #ffffff;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 30px;
            font-weight: 600;
        }

        .badge-stock {
            position: absolute;
            bottom: 12px;
            right: 12px;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 30px;
            font-weight: 600;
        }

        .precio-tag {
            color: #0f172a;
            font-size: 1.45rem;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
        }

        .precio-mayor-tag {
            color: #059669;
            font-size: 0.88rem;
            font-weight: 700;
        }

        .btn-whatsapp {
            background-color: #25d366;
            color: white;
            border: none;
            font-weight: 600;
            border-radius: 10px;
        }
        .btn-whatsapp:hover {
            background-color: #1ebc57;
            color: white;
        }

        .btn-reserva {
            background-color: #2563eb;
            color: white;
            border: none;
            font-weight: 600;
            border-radius: 10px;
        }
        .btn-reserva:hover {
            background-color: #1d4ed8;
            color: white;
        }

        /* FILTROS */
        .filter-pill {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            border-radius: 30px;
            padding: 7px 16px;
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
        }
        .filter-pill:hover, .filter-pill.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }
        .cart-summary { position: sticky; top: 76px; z-index: 5; }
    </style>
</head>
<body>

<!-- NAVBAR PÚBLICA -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 px-3 sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('inicio') }}">
            <img src="{{ asset('img/logo.jpeg') }}" alt="Logo {{ $empresa->razon_social }}" class="rounded-circle bg-white p-1" style="width: 38px; height: 38px; object-fit: contain;">
            <span>{{ $empresa->razon_social }}</span>
        </a>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">
                <i class="bi bi-person-lock me-1"></i> Acceso Personal
            </a>
        </div>
    </div>
</nav>

<!-- HERO HEADER -->
<div class="hero-banner text-center mb-5">
    <div class="container position-relative">
        <span class="badge bg-primary bg-opacity-50 border border-primary px-3 py-1 rounded-pill mb-3">Catálogo Virtual 2026</span>
        <h1 class="display-5 fw-bold mb-2">Artículos Modernos & Funcionales para el Hogar</h1>
        <p class="lead text-white-50 mx-auto mb-4" style="max-width: 650px;">
            Encuentra las mejores novedades importadas para tu cocina, sala y organización del hogar con precios al por mayor y menor en Oruro - Bolivia.
        </p>

        <!-- Buscador en Vivo -->
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <form method="GET" action="{{ route('catalogo.publico') }}" class="input-group shadow-lg rounded-pill overflow-hidden bg-white p-1">
                    <input type="text" name="buscar" class="form-control border-0 ps-4 py-2" placeholder="Buscar vajilla, organizador, electrodomésticos..." value="{{ request('buscar') }}">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CONTENEDOR PRINCIPAL -->
<div class="container mb-5">

    <div class="cart-summary d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-dark shadow" id="btnAbrirCarrito" disabled>
            <i class="bi bi-cart3 me-1"></i> Seleccionados <span class="badge bg-primary ms-1" id="contadorCarrito">0</span>
        </button>
    </div>

    <!-- Filtro por Categorías -->
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
        <a href="{{ route('catalogo.publico') }}" class="filter-pill {{ !request('tipo') ? 'active' : '' }}">
            <i class="bi bi-grid-fill me-1"></i> Todos los Productos ({{ count($productos) }})
        </a>
        @foreach($tipos as $t)
            <a href="{{ route('catalogo.publico', ['tipo' => $t->id_tipo_producto]) }}" class="filter-pill {{ request('tipo') == $t->id_tipo_producto ? 'active' : '' }}">
                {{ $t->nombre_tipo }}
            </a>
        @endforeach
    </div>

    <!-- Galería de Productos -->
    <div class="row g-4">
        @forelse($productos as $p)
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card-producto">
                <div class="img-container">
                    @if($p->imagen_url)
                        <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}">
                    @else
                        <div class="text-secondary text-center p-3">
                            <i class="bi bi-box-seam fs-1 text-primary"></i>
                            <div class="small fw-semibold mt-1">PEBIE Hogar</div>
                        </div>
                    @endif

                    <span class="badge-cat">{{ $p->tipoProducto->nombre_tipo ?? 'Hogar' }}</span>

                </div>

                <div class="p-3 d-flex flex-column flex-grow-1">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">{{ $p->marca }}</small>
                    <h5 class="fw-bold text-dark mb-1 text-truncate" title="{{ $p->nombre }}">{{ $p->nombre }}</h5>
                    <p class="text-muted small flex-grow-1 mb-2">{{ $p->descripcion }}</p>
                    <div class="small text-primary border-top pt-2"><i class="bi bi-card-text me-1"></i>Características técnicas del producto</div>

                    <div class="d-grid gap-2 mt-2">
                        <!-- Agregar al carrito de reserva -->
                        @if($p->stock > 0)
                        <button class="btn btn-reserva btn-sm py-2 btn-abrir-reserva" 
                                data-id="{{ $p->id_producto }}" 
                                data-nombre="{{ $p->nombre }}"
                                data-menor="{{ $p->precio_menor }}"
                                data-mayor="{{ $p->precio_mayor }}"
                                data-minmayor="{{ $p->cantidad_min_mayor }}"
                                data-stock="{{ $p->stock }}">
                            <i class="bi bi-cart-plus me-1"></i> Agregar a selección
                        </button>
                        @endif

                        <!-- Botón WhatsApp Consulta Directa -->
                        @php
                            $msgWhatsApp = "Hola Importadora PEBIE, deseo consultar disponibilidad y precio del producto: " . $p->nombre . " (Ref: #" . $p->id_producto . ") que vi en su catálogo virtual.";
                            $cleanTel = preg_replace('/[^0-9]/', '', $empresa->telefono);
                        @endphp
                        <a href="https://api.whatsapp.com/send?phone={{ $cleanTel }}&text={{ urlencode($msgWhatsApp) }}" target="_blank" class="btn btn-whatsapp btn-sm py-2">
                            <i class="bi bi-whatsapp me-1"></i> Consultar por WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
            <h4 class="fw-bold text-dark">No se encontraron productos en esta categoría</h4>
            <p class="text-muted">Intenta buscar con otros términos o seleccionar otra categoría.</p>
            <a href="{{ route('catalogo.publico') }}" class="btn btn-primary px-4">Ver Todos los Productos</a>
        </div>
        @endforelse
    </div>

</div>

<!-- FOOTER PÚBLICO -->
<footer class="bg-dark text-white py-4 mt-5 border-top border-secondary">
    <div class="container text-center">
        <h5 class="fw-bold mb-1">{{ $empresa->razon_social }}</h5>
        <p class="text-white-50 small mb-2">{{ $empresa->direccion }} — {{ $empresa->ciudad }} | Teléfono: {{ $empresa->telefono }}</p>
        <p class="text-white-50 small mb-0">{{ $empresa->mensaje_pie_pagina }}</p>
    </div>
</footer>

<!-- MODAL CARRITO Y SOLICITUD DE RESERVA -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-cart3 me-2"></i>Tu selección para reservar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="vistaFormReserva">
                <form id="formGenerarReserva">
                    <div class="modal-body py-3">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-clock-history me-1"></i> El ticket de reserva mantiene tu producto separado durante <strong>48 horas</strong> para que pases a recogerlo o coordines tu entrega.
                        </div>

                        <div id="listaCarrito" class="d-flex flex-column gap-2 mb-3"></div>

                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Tu Nombre Completo *</label>
                                <input type="text" id="res_nombre_cliente" class="form-control" placeholder="Ej. Juan Pérez" required>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label small fw-semibold">Teléfono / WhatsApp *</label>
                                <input type="text" id="res_telefono_cliente" class="form-control" placeholder="Ej. 72456789" required>
                            </div>

                            <div class="col-md-5"><div class="small text-muted pt-4">Revisa las cantidades de cada producto en la lista.</div></div>
                        </div>

                        <div id="alertReservaModal" class="alert alert-danger d-none py-2 mt-3 small"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <a href="#" id="btnConsultaWhatsApp" target="_blank" rel="noopener" class="btn btn-whatsapp"><i class="bi bi-whatsapp me-1"></i> Consultar por WhatsApp</a>
                        <button type="button" id="btnConfirmarReserva" class="btn btn-primary px-4">
                            <span id="spinnerReserva" class="spinner-border spinner-border-sm d-none me-1"></span>
                            Guardar y generar tickets
                        </button>
                    </div>
                </form>
            </div>

            <!-- VISTA TICKET CONFIRMADO -->
            <div id="vistaTicketConfirmado" class="d-none">
                <div class="modal-body text-center py-4">
                    <div class="text-success fs-1 mb-2"><i class="bi bi-check-circle-fill"></i></div>
                    <h4 class="fw-bold text-dark mb-1">¡Ticket de Reserva Generado!</h4>
                    <p class="text-muted small">Presenta tus códigos en tienda para consolidar la reserva.</p>

                    <div class="p-3 bg-light border border-2 border-primary rounded-3 my-3">
                        <div class="text-muted small text-uppercase">CÓDIGO DE RESERVA</div>
                        <h3 id="ticketCodigo" class="fw-bold text-primary my-1 brand-font"></h3>
                        <div id="ticketLista" class="small text-dark mt-2 text-start"></div>
                        <div class="small mt-2"><strong>Total:</strong> <span id="ticketTotal" class="fw-bold text-success"></span> Bs. | <strong>Válidos hasta:</strong> <span id="ticketVencimiento" class="text-danger fw-semibold"></span></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts Bootstrap -->
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalReservaEl = document.getElementById('modalReserva');
    const modalReservaBS = new bootstrap.Modal(modalReservaEl);
    const carrito = new Map();
    const whatsappDestino = '59168310512';
    const botonCarrito = document.getElementById('btnAbrirCarrito');
    const escapar = value => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));

    function actualizarCarrito() {
        document.getElementById('contadorCarrito').textContent = carrito.size;
        botonCarrito.disabled = carrito.size === 0;
        const lista = document.getElementById('listaCarrito');
        lista.innerHTML = [...carrito.values()].map(p => `
            <div class="bg-light border rounded-3 p-2" data-item="${p.id}">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div><strong>${escapar(p.nombre)}</strong><div class="small text-muted">Menor Bs. ${p.menor.toFixed(2)} · Mayor Bs. ${p.mayor.toFixed(2)} desde ${p.minmayor} un.</div></div>
                    <button type="button" class="btn btn-sm btn-outline-danger quitar-item" data-id="${p.id}" aria-label="Quitar ${escapar(p.nombre)}"><i class="bi bi-trash"></i></button>
                </div>
                <label class="small fw-semibold mt-2">Cantidad a reservar</label>
                <input class="form-control form-control-sm cantidad-carrito" type="number" min="1" max="${p.stock}" value="${p.cantidad}" data-id="${p.id}" aria-label="Cantidad de ${escapar(p.nombre)}">
                <div class="small text-danger d-none error-cantidad">La cantidad debe estar entre 1 y ${p.stock}.</div>
            </div>`).join('');
        lista.querySelectorAll('.quitar-item').forEach(b => b.addEventListener('click', () => { carrito.delete(b.dataset.id); actualizarCarrito(); }));
        lista.querySelectorAll('.cantidad-carrito').forEach(i => i.addEventListener('input', () => { const p=carrito.get(i.dataset.id); p.cantidad=parseInt(i.value,10)||0; i.nextElementSibling.classList.toggle('d-none', p.cantidad>=1 && p.cantidad<=p.stock); actualizarWhatsApp(); }));
        actualizarWhatsApp();
    }

    function actualizarWhatsApp() {
        const detalle = [...carrito.values()].map(p => `• ${p.nombre}: ${p.cantidad} unidad(es)`).join('\n');
        const msg = `Hola, quisiera consultar si tienen disponibles estos productos y sus precios:\n${detalle}\n¿Me pueden ayudar?`;
        document.getElementById('btnConsultaWhatsApp').href = `https://api.whatsapp.com/send?phone=${whatsappDestino}&text=${encodeURIComponent(msg)}`;
    }

    document.querySelectorAll('.btn-abrir-reserva').forEach(btn => {
        btn.addEventListener('click', function () {
            const p = this.dataset;
            if (!carrito.has(p.id)) carrito.set(p.id, {id:p.id, nombre:p.nombre, menor:parseFloat(p.menor), mayor:parseFloat(p.mayor), minmayor:parseInt(p.minmayor,10), stock:parseInt(p.stock,10), cantidad:1});
            actualizarCarrito();
            botonCarrito.classList.add('btn-success');
            setTimeout(() => botonCarrito.classList.remove('btn-success'), 600);
        });
    });

    botonCarrito.addEventListener('click', () => { document.getElementById('vistaFormReserva').classList.remove('d-none'); document.getElementById('vistaTicketConfirmado').classList.add('d-none'); document.getElementById('alertReservaModal').classList.add('d-none'); actualizarCarrito(); modalReservaBS.show(); });

    const btnConfirmar = document.getElementById('btnConfirmarReserva');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function () {
            const nombre = document.getElementById('res_nombre_cliente').value.trim();
            const tel = document.getElementById('res_telefono_cliente').value.trim();
            const alertBox = document.getElementById('alertReservaModal');
            const spinner = document.getElementById('spinnerReserva');

            if (!nombre || !tel || !carrito.size) {
                alertBox.textContent = 'Por favor ingresa tu nombre, teléfono y agrega al menos un producto.';
                alertBox.classList.remove('d-none');
                return;
            }
            if ([...carrito.values()].some(p => p.cantidad < 1 || p.cantidad > p.stock)) { alertBox.textContent = 'Revisa las cantidades: deben estar dentro del stock disponible.'; alertBox.classList.remove('d-none'); return; }

            btnConfirmar.disabled = true;
            spinner.classList.remove('d-none');
            alertBox.classList.add('d-none');

            fetch("{{ route('catalogo.reservar') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    productos: [...carrito.values()].map(p => ({id_producto:p.id, cantidad:p.cantidad})),
                    nombre_cliente: nombre,
                    telefono_cliente: tel
                })
            })
            .then(res => res.json())
            .then(data => {
                btnConfirmar.disabled = false;
                spinner.classList.add('d-none');

                if (data.success) {
                    document.getElementById('vistaFormReserva').classList.add('d-none');
                    document.getElementById('vistaTicketConfirmado').classList.remove('d-none');

                    document.getElementById('ticketCodigo').textContent = `${data.reservas.length} reserva(s)`;
                    document.getElementById('ticketLista').innerHTML = data.reservas.map(r => `<div class="border-bottom py-2"><strong>${escapar(r.producto)}</strong> × ${r.cantidad} · <span class="text-muted">${escapar(r.codigo)}</span> · Bs. ${r.total}</div>`).join('');
                    document.getElementById('ticketTotal').textContent = data.total;
                    document.getElementById('ticketVencimiento').textContent = data.fecha_limite;
                    carrito.clear(); actualizarCarrito();
                } else {
                    alertBox.textContent = data.mensaje || data.errors?.productos?.[0] || 'No se pudo procesar la reserva.';
                    alertBox.classList.remove('d-none');
                }
            })
            .catch(err => {
                btnConfirmar.disabled = false;
                spinner.classList.add('d-none');
                alertBox.textContent = 'Error de conexión: ' + err.message;
                alertBox.classList.remove('d-none');
            });
        });
    }
});
</script>
</body>
</html>
