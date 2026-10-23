<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Importadora PEBIE - Sistema de Gestión Comercial</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{asset('datatables/datatables.min.css')}}">

    <style>
        :root {
            --primary-dark: #0f172a;
            --sidebar-bg: #1e293b;
            --brand-color: #2563eb;
            --brand-hover: #1d4ed8;
            --bg-light: #f8fafc;
            --card-border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            min-height: 100vh;
        }

        /* TOP NAVBAR */
        .navbar-custom {
            background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand-badge {
            background: rgba(37, 99, 235, 0.25);
            border: 1px solid rgba(37, 99, 235, 0.45);
            color: #93c5fd;
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 6px;
        }

        /* SIDEBAR */
        .sidebar {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 16px 12px;
            box-shadow: 0 4px 12px -2px rgba(0,0,0,0.03);
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .sidebar-title {
            font-size: 0.70rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 6px;
            margin-top: 14px;
            padding-left: 8px;
        }

        .sidebar-title:first-child {
            margin-top: 0;
        }
        .menu-group-toggle { width: 100%; border: 0; background: transparent; text-align: left; cursor: pointer; }
        .menu-group-toggle::after { content: '\f282'; font-family: bootstrap-icons; float: right; }
        .menu-group-toggle[aria-expanded="false"]::after { content: '\f285'; }

        .btn-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            text-decoration: none;
            color: #475569;
            padding: 8px 11px;
            margin-bottom: 2px;
            border-radius: 8px;
            transition: all 0.18s ease;
            font-weight: 500;
            font-size: 0.86rem;
        }

        .btn-menu i {
            font-size: 1.05rem;
            color: #64748b;
            transition: color 0.18s ease;
        }

        .btn-menu:hover {
            background: #f1f5f9;
            color: var(--brand-color);
            transform: translateX(2px);
        }

        .btn-menu:hover i {
            color: var(--brand-color);
        }

        .btn-menu.active {
            background: #eff6ff;
            color: var(--brand-color);
            font-weight: 600;
            border-left: 3px solid var(--brand-color);
        }

        .btn-menu.active i {
            color: var(--brand-color);
        }

        /* MAIN CONTENT AREA */
        .content-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 24px;
            min-height: 780px;
            box-shadow: 0 4px 12px -2px rgba(0,0,0,0.03);
        }

        .footer {
            margin-top: 30px;
            color: var(--text-muted);
            font-size: 0.82rem;
            text-align: center;
            padding: 12px 0;
        }

        /* USER BADGE */
        .user-pill {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .badge-role {
            font-size: 0.72rem;
            padding: 2px 7px;
            border-radius: 4px;
        }
    </style>
    @yield('styles')
</head>
<body>

<!-- NAVBAR PRINCIPAL -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4 py-2 sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <i class="bi bi-box-seam text-primary fs-4"></i>
            <span>IMPORTADORA PEBIE</span>
            <span class="brand-badge ms-1">v2.5</span>
        </a>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <!-- Catálogo Público -->
            <a href="{{ route('catalogo.publico') }}" target="_blank" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-shop"></i>
                <span class="d-none d-md-inline">Ver Catálogo Público</span>
            </a>

            <!-- POS (Solo Admin o Vendedor) -->
            @if(in_array(session('rol'), [1, 2]))
            <a href="{{ route('ventas.pos') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-cart-plus-fill"></i>
                <span class="d-none d-md-inline">Punto de Venta (POS)</span>
            </a>
            @endif

            <!-- Usuario Info -->
            <div class="user-pill d-none d-sm-flex">
                <i class="bi bi-person-circle text-info"></i>
                <span>{{ session('nom', 'Usuario') }}</span>
                @if(session('rol') == 1)
                    <span class="badge bg-danger badge-role">Administrador</span>
                @elseif(session('rol') == 2)
                    <span class="badge bg-primary badge-role">Vendedor</span>
                @elseif(session('rol') == 3)
                    <span class="badge bg-warning text-dark badge-role">Encargado de Almacén</span>
                @elseif(session('rol') == 4)
                    <span class="badge bg-info text-dark badge-role">Encargado de Despacho</span>
                @else
                    <span class="badge bg-secondary badge-role">{{ session('nombre_rol', 'Personal') }}</span>
                @endif
            </div>

            <!-- Salir -->
            <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm text-white border-white-50" onclick="return confirm('¿Desea cerrar la sesión actual?')">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<!-- CONTENEDOR CENTRAL -->
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row g-4">
        <!-- SIDEBAR DE NAVEGACIÓN SEGÚN ROL -->
        <div class="col-lg-3 col-xl-2">
            <div class="sidebar">
                @if(session('rol') != 4)<div class="sidebar-title">Principal</div>
                <a href="{{ route('dashboard') }}" class="btn-menu {{ request()->routeIs('dashboard', 'administrador') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                @endif

                <!-- SECCIÓN VENTAS & DESPACHO (Admin, Vendedor, Despacho) -->
                @if(in_array(session('rol'), [1, 2, 4]))
                <button type="button" class="sidebar-title menu-group-toggle" data-menu-target="menuVentas" aria-controls="menuVentas" aria-expanded="true">Ventas & Despacho</button><div id="menuVentas" class="collapse show">
                    @if(in_array(session('rol'), [1, 2]))
                    <a href="{{ route('ventas.pos') }}" class="btn-menu {{ request()->routeIs('ventas.pos') ? 'active' : '' }}">
                        <i class="bi bi-calculator"></i> Nueva Venta (POS)
                    </a>
                    @endif
                    <a href="{{ route('ventas.directas') }}" class="btn-menu {{ request()->routeIs('ventas.directas') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i> Venta directa
                    </a>
                    <a href="{{ route('ventas.reservas') }}" class="btn-menu {{ request()->routeIs('ventas.reservas') ? 'active' : '' }}">
                        <i class="bi bi-bookmark-check"></i> Reservas
                    </a>
                    @if(in_array(session('rol'), [1, 2]))
                    <a href="{{ route('clientes') }}" class="btn-menu {{ request()->routeIs('clientes*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                    @endif
                    <a href="{{ route('reservas.admin') }}" class="btn-menu {{ request()->routeIs('reservas*') ? 'active' : '' }}">
                        <i class="bi bi-ticket-perforated"></i> Reservas Web
                    </a>
                </div>
                @endif

                <!-- SECCIÓN INVENTARIO & STOCK (Admin, Almacén, Vendedor) -->
                @if(in_array(session('rol'), [1, 2, 3]))
                <button type="button" class="sidebar-title menu-group-toggle" data-menu-target="menuInventario" aria-controls="menuInventario" aria-expanded="true">Inventario & Stock</button><div id="menuInventario" class="collapse show">
                <a href="{{ route('productos') }}" class="btn-menu {{ request()->routeIs('productos*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i> Productos & Kardex
                </a>
                    @if(in_array(session('rol'), [1, 3]))
                    <a href="{{ route('mermas') }}" class="btn-menu {{ request()->routeIs('mermas*') ? 'active' : '' }}">
                        <i class="bi bi-slash-circle"></i> Registrar mermas
                    </a>
                    @endif
                    @if(session('rol') == 1)
                    <a href="{{ route('tipos.producto') }}" class="btn-menu {{ request()->routeIs('tipos.producto*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> Clasificación
                    </a>
                    @endif
                    @if(in_array(session('rol'), [1, 3, 4]))
                    <a href="{{ route('ubicaciones') }}" class="btn-menu {{ request()->routeIs('ubicaciones*') ? 'active' : '' }}">
                        <i class="bi bi-geo-alt"></i> Almacenes / Sucursales
                    </a>
                    @endif
                </div>
                @endif

                @if(session('rol') == 4)
                <a href="{{ route('ubicaciones') }}" class="btn-menu {{ request()->routeIs('ubicaciones*') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i> Solicitudes de traslado
                </a>
                @endif

                <!-- SECCIÓN ABASTECIMIENTO & EXTERIOR (Admin, Almacén) -->
                @if(session('rol') == 1)
                <button type="button" class="sidebar-title menu-group-toggle" data-menu-target="menuCompras" aria-controls="menuCompras" aria-expanded="true">Abastecimiento</button><div id="menuCompras" class="collapse show">
                <a href="{{ route('compras') }}" class="btn-menu {{ request()->routeIs('compras') ? 'active' : '' }}">
                    <i class="bi bi-bag-plus"></i> Compras
                </a>
                <a href="{{ route('proveedores') }}" class="btn-menu {{ request()->routeIs('proveedores*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i> Proveedores
                </a>
                </div>
                @endif

                <!-- FINANZAS Y REPORTES (vista según rol) -->
                @if(in_array(session('rol'), [1, 2, 3, 4]))
                <div class="sidebar-title">Finanzas y Reportes</div>
                <a href="{{ route('reportes') }}" class="btn-menu {{ request()->routeIs('reportes*') ? 'active' : '' }}"><i class="bi bi-bar-chart-line"></i> {{ session('rol') == 3 ? 'Reportes de inventario' : (session('rol') == 4 ? 'Reportes de despacho' : 'Finanzas y reportes') }}</a>
                @endif

                <!-- SECCIÓN ADMINISTRACIÓN (Solo Administrador) -->
                @if(session('rol') == 1)
                <div class="sidebar-title">Administración</div>
                <a href="{{ route('configuracion') }}" class="btn-menu {{ request()->routeIs('configuracion*') ? 'active' : '' }}">
                    <i class="bi bi-building-gear"></i> Identidad Corporativa
                </a>
                <a href="{{ route('usuarios') }}" class="btn-menu {{ request()->routeIs('usuarios*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> Gestión de Usuarios
                </a>
                <a href="{{ route('auditoria') }}" class="btn-menu {{ request()->routeIs('auditoria*') ? 'active' : '' }}">
                    <i class="bi bi-fingerprint"></i> Auditoría de Accesos
                </a>
                @endif
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-lg-9 col-xl-10">
            <div class="content-card">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <strong>Importadora PEBIE</strong> &copy; {{ date('Y') }} — Sistema Comercial y Control de Inventarios | Oruro - Bolivia
    </div>
</div>

<!-- SCRIPTS JS -->
<script src="{{asset('js/bootstrap.min.js')}}"></script>
<script src="{{asset('datatables/datatables.min.js')}}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.menu-group-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const group = document.getElementById(button.dataset.menuTarget);
            if (!group) return;
            const visible = group.classList.toggle('show');
            button.setAttribute('aria-expanded', visible ? 'true' : 'false');
        });
    });
});
</script>
@yield('scripts')
</body>
</html>
