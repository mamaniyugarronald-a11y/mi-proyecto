@extends('adm_plantilla')

@section('content')
<style>
    .report-chart-block { background:#fff; border:1px solid #e5e9f0; border-radius:.75rem; padding:1rem 1.1rem; }
    .report-bars { display:flex; flex-direction:column; gap:.65rem; max-width:1000px; }
    .report-bar-row { display:grid; grid-template-columns:minmax(130px, 240px) 1fr minmax(90px, 135px); align-items:center; gap:.8rem; font-size:.84rem; }
    .report-bar-label { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .report-bar-track { height:16px; background:repeating-linear-gradient(90deg,#f0f3f8 0,#f0f3f8 calc(25% - 1px),#dce2eb calc(25% - 1px),#dce2eb 25%); border-radius:20px; overflow:hidden; }
    .report-bar-fill { height:100%; border-radius:20px; min-width:2px; box-shadow:0 1px 3px #0002; }
    .report-bar-fill.bg-primary { background:linear-gradient(90deg,#0d6efd,#55a3ff) !important; }
    .report-bar-fill.bg-danger { background:linear-gradient(90deg,#dc3545,#ff7884) !important; }
    .report-bar-value { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
    .report-donut-layout { display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap; }
    .report-donut { width:190px; height:190px; flex:0 0 190px; border-radius:50%; display:grid; place-items:center; position:relative; }
    .report-donut::before { content:""; position:absolute; width:105px; height:105px; border-radius:50%; background:white; }
    .report-donut span { z-index:1; font-size:1.15rem; font-weight:700; }
    .report-donut-legend { display:grid; gap:.45rem; min-width:260px; }
    .report-legend-item { display:grid; grid-template-columns:14px minmax(100px,1fr) auto; gap:.5rem; align-items:center; font-size:.83rem; }
    .report-legend-swatch { width:12px; height:12px; border-radius:3px; }
    .print-only { display:none; }
    @media print {
        @page { size:landscape; margin:12mm; }
        body { background:#fff !important; font-size:10pt; }
        nav, .sidebar, .footer, .no-print, .nav-tabs, .report-summary { display:none !important; }
        .container-fluid { width:100% !important; max-width:none !important; padding:0 !important; margin:0 !important; }
        .col-lg-3, .col-xl-2 { display:none !important; }
        .col-lg-9, .col-xl-10 { flex:0 0 100% !important; width:100% !important; max-width:100% !important; }
        .content-card { border:0 !important; box-shadow:none !important; padding:0 !important; }
        .print-only { display:block !important; }
        .tab-content { border:0 !important; padding:0 !important; }
        .tab-pane, .tab-pane.fade, .tab-pane:not(.active) { display:none !important; visibility:hidden !important; }
        .tab-pane.active { display:block !important; visibility:visible !important; opacity:1 !important; height:auto !important; border:0 !important; padding:0 !important; margin:0 !important; }
        .report-chart-block, .report-donut-layout, .card, table, tr { break-inside:avoid; }
        .report-bar-track, .report-bar-fill { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
        .report-donut, .report-donut::before, .report-legend-swatch { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
        .table { font-size:8.5pt; }
        .alert { border:1px solid #aaa !important; }
        button, a.btn { display:none !important; }
    }
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom no-print">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-bar-chart-line text-primary me-2"></i>Finanzas y Reportes</h3>
        <p class="text-muted small mb-0">
            @if($rol === 1)Resumen financiero, comercial, inventario y operación.@elseif($rol === 2)Reporte de tus ventas completadas.@elseif($rol === 3)Existencias y alertas para la gestión de almacén.@else Estado de ventas que requieren transporte.@endif
        </p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-dark btn-sm no-print"><i class="bi bi-printer me-1"></i>Imprimir</button>
</div>

<div class="print-only mb-3"><h2 id="reportePrintTitle">Reporte seleccionado</h2><div>Período: @if($agrupacion === 'mes'){{ \Carbon\Carbon::createFromFormat('Y-m', $fechaDesde)->format('m/Y') }} – {{ \Carbon\Carbon::createFromFormat('Y-m', $fechaHasta)->format('m/Y') }}@else{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}@endif</div><small>Generado el {{ now()->format('d/m/Y H:i') }}</small></div>

<div class="card border-0 shadow-sm bg-light mb-4 no-print">
    <div class="card-body">
        <form id="formFiltrosReportes" class="row g-3 align-items-end" method="GET" action="{{ route('reportes') }}">
            <div class="col-sm-6 col-md-2"><label for="agrupacion" class="form-label small fw-semibold">Mostrar por</label><select id="agrupacion" name="agrupacion" class="form-select"><option value="dia" @selected($agrupacion === 'dia')>Día</option><option value="mes" @selected($agrupacion === 'mes')>Mes</option></select></div>
            <div class="col-sm-5 col-md-2"><label for="fecha_desde" class="form-label small fw-semibold">Desde</label><input id="fecha_desde" type="{{ $agrupacion === 'mes' ? 'month' : 'date' }}" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}" required></div>
            <div class="col-sm-5 col-md-2"><label for="fecha_hasta" class="form-label small fw-semibold">Hasta</label><input id="fecha_hasta" type="{{ $agrupacion === 'mes' ? 'month' : 'date' }}" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}" required></div>
            <div class="col-sm-4 col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button></div>
            <div class="col-md-4 small text-muted">Período: @if($agrupacion === 'mes'){{ \Carbon\Carbon::createFromFormat('Y-m', $fechaDesde)->format('m/Y') }} – {{ \Carbon\Carbon::createFromFormat('Y-m', $fechaHasta)->format('m/Y') }}@else{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}@endif</div>
        </form>
    </div>
</div>

@if($rol === 1 || $rol === 2)
<div class="row g-3 mb-4 report-summary">
    <div class="col-md-{{ $rol === 1 ? '3' : '4' }}"><div class="card h-100 border-start border-primary border-4 shadow-sm"><div class="card-body"><small class="text-muted">{{ $rol === 1 ? 'Ventas completadas' : 'Mis ventas completadas' }}</small><h4 class="text-primary mb-1">Bs. {{ number_format($totalVentasMonto, 2) }}</h4><small>{{ $ventasCompletadas->count() }} operaciones · {{ $unidadesVendidas }} unidades</small></div></div></div>
    @if($rol === 1)
    <div class="col-md-3"><div class="card h-100 border-start border-warning border-4 shadow-sm"><div class="card-body"><small class="text-muted">Compras locales</small><h4 class="mb-1">Bs. {{ number_format($totalComprasMonto, 2) }}</h4><small>{{ $compras->count() }} operaciones</small></div></div></div>
    <div class="col-md-3"><div class="card h-100 border-start border-success border-4 shadow-sm"><div class="card-body"><small class="text-muted">Valor estimado de existencias</small><h4 class="text-success mb-1">Bs. {{ number_format($valorInventario, 2) }}</h4><small>{{ $productos->count() }} productos activos a costo registrado</small></div></div></div>
    <div class="col-md-3"><div class="card h-100 border-start border-danger border-4 shadow-sm"><div class="card-body"><small class="text-muted">Productos críticos</small><h4 class="text-danger mb-1">{{ $productosCriticos->count() }}</h4><small>Agotados o iguales/al mínimo</small></div></div></div>
    @else
    <div class="col-md-4"><div class="card h-100 border-start border-info border-4 shadow-sm"><div class="card-body"><small class="text-muted">Unidades vendidas</small><h4 class="text-info mb-1">{{ $unidadesVendidas }}</h4><small>En el período seleccionado</small></div></div></div>
    <div class="col-md-4"><div class="card h-100 border-start border-secondary border-4 shadow-sm"><div class="card-body"><small class="text-muted">Período de consulta</small><h4 class="mb-1">{{ $ventasCompletadas->count() }} ventas</h4><small>Solo tus operaciones completadas</small></div></div></div>
    @endif
</div>
@elseif($rol === 3)
<div class="row g-3 mb-4 report-summary"><div class="col-md-6"><div class="card border-start border-primary border-4"><div class="card-body"><small class="text-muted">Productos activos</small><h4>{{ $productos->count() }}</h4></div></div></div><div class="col-md-6"><div class="card border-start border-danger border-4"><div class="card-body"><small class="text-muted">Stock crítico</small><h4 class="text-danger">{{ $productosCriticos->count() }}</h4></div></div></div></div>
@else
<div class="row g-3 mb-4 report-summary"><div class="col-md-4"><div class="card border-start border-warning border-4"><div class="card-body"><small class="text-muted">Envíos pendientes</small><h4>{{ $ventas->where('estado_despacho', 'Pendiente')->count() }}</h4></div></div></div><div class="col-md-4"><div class="card border-start border-primary border-4"><div class="card-body"><small class="text-muted">En despacho</small><h4>{{ $ventas->where('estado_despacho', 'Despachado')->count() }}</h4></div></div></div><div class="col-md-4"><div class="card border-start border-success border-4"><div class="card-body"><small class="text-muted">Entregados en el período</small><h4>{{ $ventas->where('estado_despacho', 'Entregado')->count() }}</h4></div></div></div></div>
@endif

@if($rol === 1)
<ul class="nav nav-tabs flex-nowrap overflow-auto no-print" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabVentas" type="button">Ventas</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCompras" type="button">Compras</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabInventario" type="button">Inventario</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabActividad" type="button">Actividad del sistema</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDespacho" type="button">Despachos</button></li>
</ul>
<div class="tab-content border border-top-0 rounded-bottom p-3 bg-white">
    <div class="tab-pane fade show active" id="tabVentas">
        <h5 class="fw-bold">Ventas por {{ $agrupacion === 'mes' ? 'mes' : 'día' }}</h5>
        @include('partials.reporte_grafico_barras', ['chartTitle' => 'Total vendido por ' . ($agrupacion === 'mes' ? 'mes' : 'día') . ' (Bs.)', 'chartItems' => $ventasPorDiaGrafica])
        <div class="table-responsive mb-4"><table class="table table-sm"><thead><tr><th>{{ $agrupacion === 'mes' ? 'Mes' : 'Fecha' }}</th><th>Operaciones</th><th class="text-end">Total (Bs.)</th></tr></thead><tbody>@forelse($ventasPorDia as $dia)<tr><td>{{ $agrupacion === 'mes' ? \Carbon\Carbon::createFromFormat('Y-m', $dia['fecha'])->format('m/Y') : \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td><td>{{ $dia['operaciones'] }}</td><td class="text-end">{{ number_format($dia['total'], 2) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted">Sin ventas en el período.</td></tr>@endforelse</tbody></table></div>
        <h5 class="fw-bold">Detalle de ventas completadas</h5>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Pago</th><th class="text-end">Total (Bs.)</th></tr></thead><tbody>@forelse($ventasCompletadas as $venta)<tr><td>#{{ $venta->id_venta }}</td><td>{{ date('d/m/Y H:i', strtotime($venta->fecha)) }}</td><td>{{ $venta->cliente->nombre_completo ?? 'Cliente' }}</td><td>{{ $venta->usuario->nombre ?? '—' }}</td><td>{{ $venta->metodo_pago ?? '—' }}</td><td class="text-end fw-semibold">{{ number_format($venta->total, 2) }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">Sin ventas completadas.</td></tr>@endforelse</tbody></table></div>
    </div>
    <div class="tab-pane fade" id="tabCompras"><h5 class="fw-bold">Compras por proveedor</h5>@include('partials.reporte_grafico_barras', ['chartTitle' => 'Monto comprado por proveedor (Bs.)', 'chartItems' => $comprasPorProveedor])<div class="table-responsive"><table class="table table-hover"><thead><tr><th>N°</th><th>Fecha</th><th>Proveedor</th><th>Registrado por</th><th class="text-end">Total (Bs.)</th></tr></thead><tbody>@forelse($compras as $compra)<tr><td>#{{ $compra->id_compra }}</td><td>{{ date('d/m/Y H:i', strtotime($compra->fecha)) }}</td><td>{{ $compra->proveedor->nombre_empresa ?? 'Proveedor' }}</td><td>{{ $compra->usuario->nombre ?? '—' }}</td><td class="text-end">{{ number_format($compra->detalles->sum(fn($d) => $d->cantidad * $d->precio_compra), 2) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">Sin compras locales en el período.</td></tr>@endforelse</tbody></table></div></div>
    <div class="tab-pane fade" id="tabInventario">
        <div class="alert alert-light small">Valoración estimada: stock actual × costo de compra registrado por producto. No representa un costo histórico contable.</div>
        @include('partials.reporte_grafico_anillo', ['chartTitle' => 'Valor actual del inventario por categoría (Bs.)', 'chartItems' => $inventarioPorCategoria])
        @include('partials.reporte_grafico_anillo', ['chartTitle' => 'Productos críticos y sobre el mínimo', 'chartItems' => $stockCriticoGrafica])
        <div class="table-responsive"><table class="table table-hover"><thead><tr><th>Producto</th><th>Categoría</th><th class="text-center">Stock</th><th class="text-center">Mínimo</th><th class="text-end">Costo unit. (Bs.)</th><th class="text-end">Valor (Bs.)</th><th>Estado</th></tr></thead><tbody>@forelse($productos as $producto)<tr><td>{{ $producto->nombre }}</td><td>{{ $producto->tipoProducto->nombre_tipo ?? 'General' }}</td><td class="text-center">{{ $producto->stock }}</td><td class="text-center">{{ $producto->stock_minimo }}</td><td class="text-end">{{ number_format($producto->costo_compra, 2) }}</td><td class="text-end">{{ number_format($producto->valor_inventario, 2) }}</td><td>@if($producto->stock <= $producto->stock_minimo)<span class="badge bg-danger">Crítico</span>@else<span class="badge bg-success">Normal</span>@endif</td></tr>@empty<tr><td colspan="7" class="text-center">Sin productos activos.</td></tr>@endforelse</tbody></table></div>
    </div>
    <div class="tab-pane fade" id="tabActividad"><div class="alert alert-light small">El sistema conserva inicios de sesión; no registra en un único historial anulaciones, ajustes ni todos los cambios críticos.</div>@include('partials.reporte_grafico_barras', ['chartTitle' => 'Inicios de sesión por usuario (máximo 100 registros del período)', 'chartItems' => $actividadPorUsuario])<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Fecha y hora</th><th>Usuario</th><th>Rol</th><th>Evento registrado</th></tr></thead><tbody>@forelse($actividadUsuarios as $actividad)<tr><td>{{ date('d/m/Y H:i', strtotime($actividad->fecha_hora)) }}</td><td>{{ $actividad->usuario->nombre ?? 'Usuario eliminado' }}</td><td>{{ $actividad->usuario->rol->nombre_rol ?? '—' }}</td><td>Inicio de sesión</td></tr>@empty<tr><td colspan="4" class="text-center">Sin accesos en el período.</td></tr>@endforelse</tbody></table></div></div>
    <div class="tab-pane fade" id="tabDespacho">@include('partials.reporte_grafico_anillo', ['chartTitle' => 'Envíos por estado de despacho', 'chartItems' => $despachosPorEstado])<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Venta</th><th>Fecha</th><th>Cliente</th><th>Estado de despacho</th><th>Vendedor</th></tr></thead><tbody>@forelse($ventasPendientesDespacho as $envio)<tr><td>#{{ $envio->id_venta }}</td><td>{{ date('d/m/Y H:i', strtotime($envio->fecha)) }}</td><td>{{ $envio->cliente->nombre_completo ?? 'Cliente' }}</td><td><span class="badge {{ $envio->estado_despacho === 'Entregado' ? 'bg-success' : ($envio->estado_despacho === 'Despachado' ? 'bg-primary' : 'bg-warning text-dark') }}">{{ $envio->estado_despacho }}</span></td><td>{{ $envio->usuario->nombre ?? '—' }}</td></tr>@empty<tr><td colspan="5" class="text-center">Sin despachos en el período.</td></tr>@endforelse</tbody></table></div></div>
</div>
@elseif($rol === 2)
@include('partials.reporte_grafico_barras', ['chartTitle' => 'Mis ventas por ' . ($agrupacion === 'mes' ? 'mes' : 'día') . ' (Bs.)', 'chartItems' => $ventasPorDiaGrafica])
<div class="row g-3 my-3 report-summary">
    <div class="col-md-6"><div class="card h-100 border-start border-info border-4 shadow-sm"><div class="card-body"><small class="text-muted">Ticket promedio</small><h4 class="text-info mb-1">Bs. {{ number_format($ticketPromedio, 2) }}</h4><small>Por venta completada en el período seleccionado</small></div></div></div>
    <div class="col-md-6"><div class="card h-100 border-start border-warning border-4 shadow-sm"><div class="card-body"><small class="text-muted">Descuentos aplicados</small><h4 class="text-warning mb-1">Bs. {{ number_format($totalDescuentos, 2) }}</h4><small>{{ $ventasConDescuento }} ventas con descuento</small></div></div></div>
</div>
@include('partials.reporte_grafico_barras', ['chartTitle' => 'Productos más vendidos (unidades)', 'chartItems' => $productosVendidos->take(10)->map(fn ($fila) => ['etiqueta' => $fila['producto'], 'valor' => $fila['unidades']])])
<div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-bold">Ventas por producto, marca y categoría</div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Producto</th><th>Marca</th><th>Categoría</th><th class="text-end">Unidades</th><th class="text-end">Importe (Bs.)</th></tr></thead><tbody>@forelse($productosVendidos as $fila)<tr><td>{{ $fila['producto'] }}</td><td>{{ $fila['marca'] }}</td><td>{{ $fila['categoria'] }}</td><td class="text-end">{{ $fila['unidades'] }}</td><td class="text-end">{{ number_format($fila['importe'], 2) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-3">Sin productos vendidos en el período.</td></tr>@endforelse</tbody></table></div></div>
<div class="row g-3 mb-3">
    <div class="col-lg-7"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white fw-bold">Historial de clientes y recurrencia</div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Cliente</th><th class="text-end">Compras</th><th class="text-end">Total (Bs.)</th><th>Última compra</th></tr></thead><tbody>@forelse($clientesFrecuentes as $fila)<tr><td>{{ $fila['cliente'] }}</td><td class="text-end">{{ $fila['operaciones'] }}</td><td class="text-end">{{ number_format($fila['total'], 2) }}</td><td>{{ $fila['ultima_compra'] ? date('d/m/Y', strtotime($fila['ultima_compra'])) : '—' }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-3">Sin compras asociadas a clientes registrados.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-lg-5"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white fw-bold">Productos comprados juntos</div><div class="card-body p-0"><ul class="list-group list-group-flush">@forelse($combinacionesProductos as $fila)<li class="list-group-item d-flex justify-content-between gap-2"><span>{{ $fila['combinacion'] }}</span><span class="badge bg-primary rounded-pill">{{ $fila['ventas'] }}</span></li>@empty<li class="list-group-item text-muted">No hay ventas con varios productos en el período.</li>@endforelse</ul></div></div></div>
</div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">Mis ventas detalladas</div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Productos</th><th>Pago</th><th class="text-end">Total (Bs.)</th></tr></thead><tbody>@forelse($ventasCompletadas as $venta)<tr><td>#{{ $venta->id_venta }}</td><td>{{ date('d/m/Y H:i', strtotime($venta->fecha)) }}</td><td>{{ $venta->cliente->nombre_completo ?? 'Cliente' }}</td><td>@foreach($venta->detalles as $detalle)<div>{{ $detalle->producto->nombre ?? 'Producto' }} × {{ $detalle->cantidad }}</div>@endforeach</td><td>{{ $venta->metodo_pago ?? '—' }}</td><td class="text-end fw-bold">{{ number_format($venta->total, 2) }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Sin ventas completadas en el período.</td></tr>@endforelse</tbody></table></div></div>
<div class="alert alert-light small mt-3">El sistema todavía no asigna cotizaciones a un vendedor ni registra motivos de oportunidades perdidas o comisiones; por eso esos indicadores no se calculan para evitar atribuciones incorrectas.</div>
@elseif($rol === 3)
@include('partials.reporte_grafico_anillo', ['chartTitle' => 'Unidades actuales por categoría', 'chartItems' => $inventarioPorCategoria])
@include('partials.reporte_grafico_anillo', ['chartTitle' => 'Productos críticos y sobre el mínimo (estado actual)', 'chartItems' => $stockCriticoGrafica])
<div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">Existencias y alerta de stock mínimo</div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Producto</th><th>Categoría</th><th class="text-center">Stock actual</th><th class="text-center">Mínimo</th><th>Alerta</th></tr></thead><tbody>@forelse($productos as $producto)<tr><td>{{ $producto->nombre }}</td><td>{{ $producto->tipoProducto->nombre_tipo ?? 'General' }}</td><td class="text-center">{{ $producto->stock }}</td><td class="text-center">{{ $producto->stock_minimo }}</td><td>@if($producto->stock <= $producto->stock_minimo)<span class="badge bg-danger">Reponer</span>@else<span class="badge bg-success">Normal</span>@endif</td></tr>@empty<tr><td colspan="5" class="text-center">Sin productos activos.</td></tr>@endforelse</tbody></table></div></div>
@else
@include('partials.reporte_grafico_anillo', ['chartTitle' => 'Envíos por estado de despacho en el período', 'chartItems' => $despachosPorEstado])
<div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">Seguimiento de despachos</div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Venta</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th>Guía</th></tr></thead><tbody>@forelse($ventas as $envio)<tr><td>#{{ $envio->id_venta }}</td><td>{{ date('d/m/Y H:i', strtotime($envio->fecha)) }}</td><td>{{ $envio->cliente->nombre_completo ?? 'Cliente' }}</td><td>{{ $envio->estado_despacho }}</td><td>{{ $envio->guia_despacho ?? '—' }}</td></tr>@empty<tr><td colspan="5" class="text-center">Sin envíos en el período.</td></tr>@endforelse</tbody></table></div></div>
@endif
<script>
    const agrupacionSelect = document.getElementById('agrupacion');
    agrupacionSelect?.addEventListener('change', () => {
        const modoMes = agrupacionSelect.value === 'mes';
        const tipoDestino = modoMes ? 'month' : 'date';
        ['fecha_desde', 'fecha_hasta'].forEach((id) => {
            const input = document.getElementById(id);
            const valorAnterior = input.value;
            if (input.type === tipoDestino) return;
            input.type = tipoDestino;
            input.value = modoMes
                ? valorAnterior.slice(0, 7)
                : (valorAnterior.length === 7 ? `${valorAnterior}-01` : valorAnterior);
        });
        document.getElementById('formFiltrosReportes')?.requestSubmit();
    });

    (() => {
        const updatePrintHeading = () => {
            const selectedTab = document.querySelector('.nav-tabs .nav-link.active');
            const roleTitle = @json($rol === 2 ? 'Mis ventas' : ($rol === 3 ? 'Reporte de inventario' : ($rol === 4 ? 'Reporte de despacho' : 'Finanzas y reportes')));
            const heading = document.getElementById('reportePrintTitle');
            if (heading) heading.textContent = selectedTab ? selectedTab.textContent.trim() : roleTitle;
        };
        document.querySelectorAll('.nav-tabs .nav-link').forEach(tab => tab.addEventListener('shown.bs.tab', updatePrintHeading));
        window.addEventListener('beforeprint', updatePrintHeading);
        updatePrintHeading();
    })();
</script>
@endsection
