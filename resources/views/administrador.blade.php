@extends('adm_plantilla')

@section('content')

@if(session('rol') == 3)
<div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom"><div><h3 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Alertas de stock mínimo</h3><p class="text-muted small mb-0">Productos que requieren reposición o revisión.</p></div><a href="{{ route('productos') }}" class="btn btn-outline-primary">Ver productos</a></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Producto</th><th>Categoría</th><th class="text-center">Stock actual</th><th class="text-center">Stock mínimo</th></tr></thead><tbody>@forelse($productosBajoStock as $producto)<tr><td class="fw-bold">{{ $producto->nombre }}</td><td>{{ $producto->tipoProducto->nombre_tipo ?? 'Sin categoría' }}</td><td class="text-center text-danger fw-bold">{{ $producto->stock }}</td><td class="text-center">{{ $producto->stock_minimo }}</td></tr>@empty<tr><td colspan="4" class="text-center text-success py-4">No hay productos con stock mínimo.</td></tr>@endforelse</tbody></table></div></div>
@else

<!-- ENCABEZADO DASHBOARD -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Panel de Control
        </h3>
        <p class="text-muted small mb-0">Resumen operativo, estadísticas comerciales y estado de inventario</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ventas.pos') }}" class="btn btn-primary shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-cart-plus"></i> + Nueva Venta (POS)
        </a>
        @if(session('rol') == 1)
        <a href="{{ route('compras') }}" class="btn btn-outline-dark d-flex align-items-center gap-2">
            <i class="bi bi-bag-plus"></i> + Registrar Compra
        </a>
        @endif
    </div>
</div>

@if(session('mensaje_bienvenida'))
<div class="alert alert-primary alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
    <i class="bi bi-emoji-smile-fill me-2"></i> {{ session('mensaje_bienvenida') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- RESUMEN DEL DASHBOARD -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 {{ session('rol') == 1 ? 'col-xl-3' : 'col-xl-6' }}"><div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-primary border-4"><div class="d-flex justify-content-between"><div><span class="text-muted small fw-semibold text-uppercase">{{ session('rol') == 2 ? 'Mis ventas completadas' : 'Ventas completadas' }}</span><h4 class="fw-bold text-primary mt-1 mb-1">Bs. {{ number_format($totalIngresosMes, 2) }}</h4><small>{{ $operacionesVentasMes }} operaciones · {{ $unidadesVendidasMes }} unidades</small></div><i class="bi bi-cash-coin fs-3 text-primary"></i></div><small class="text-muted mt-2">{{ date('F Y') }}</small></div></div>
    @if(session('rol') == 2)
    <div class="col-sm-6 col-xl-6"><div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-info border-4"><span class="text-muted small fw-semibold text-uppercase">Ticket promedio del mes</span><h4 class="fw-bold text-info mt-1 mb-1">Bs. {{ number_format($ticketPromedioMes, 2) }}</h4><small>Promedio por venta completada</small></div></div>
    @endif
    @if(session('rol') == 1)
    <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-warning border-4"><span class="text-muted small fw-semibold text-uppercase">Compras locales</span><h4 class="fw-bold mt-1 mb-1">Bs. {{ number_format($totalGastosMes, 2) }}</h4><small>{{ $comprasMes->count() }} operaciones · {{ date('F Y') }}</small></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-success border-4"><span class="text-muted small fw-semibold text-uppercase">Valor estimado de existencias</span><h4 class="fw-bold text-success mt-1 mb-1">Bs. {{ number_format($valorInventario, 2) }}</h4><small>{{ $totalProductosActivos }} productos activos a costo registrado</small></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start {{ $productosCriticos > 0 ? 'border-danger' : 'border-info' }} border-4"><span class="text-muted small fw-semibold text-uppercase">Productos críticos</span><h4 class="fw-bold {{ $productosCriticos > 0 ? 'text-danger' : 'text-info' }} mt-1 mb-1">{{ $productosCriticos }}</h4><small>Agotados o iguales/al mínimo</small></div></div>
    @endif
</div>

<!-- GRÁFICO DE VENTAS Y ESTADÍSTICAS -->
<div class="row g-4 mb-4">
    <!-- Gráfico Mensual -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Comportamiento de Ventas (Últimos 6 Meses)
                </h6>
                <span class="badge bg-light text-muted border">En Bolivianos (Bs.)</span>
            </div>
            <div style="height: 260px; position: relative;">
                <canvas id="graficoVentas"></canvas>
            </div>
        </div>
    </div>

    <!-- Resumen Rápido -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
            <h6 class="fw-bold mb-3 text-dark">
                <i class="bi bi-layers-fill text-primary me-2"></i>Resumen General del Sistema
            </h6>

            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary"></i>
                        <span class="text-muted small">Total Productos Activos</span>
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $totalProductos }}</span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-people text-success"></i>
                        <span class="text-muted small">Clientes Registrados</span>
                    </div>
                    <span class="badge bg-success rounded-pill">{{ $totalClientes }}</span>
                </div>

                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge text-info"></i>
                        <span class="text-muted small">Usuarios del Sistema</span>
                    </div>
                    <span class="badge bg-info rounded-pill">{{ $totalUsuarios }}</span>
                </div>
            </div>

            <hr class="my-3">

            <div class="d-grid gap-2">
                <a href="{{ route('ventas.pos') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-cart-plus me-1"></i> Abrir Terminal POS
                </a>
                <a href="{{ route('reportes') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Generar Reporte Detallado
                </a>
            </div>
        </div>
    </div>
</div>

<!-- TABLAS INFERIORES: ÚLTIMAS VENTAS Y PRODUCTOS EN ALERTA -->
<div class="row g-4">
    <!-- Últimas Ventas -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-receipt text-primary me-2"></i>Últimas Ventas Registradas
                </h6>
                <a href="{{ route('ventas.directas') }}" class="btn btn-sm btn-link text-decoration-none p-0">Ver todas</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-end pe-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasVentas as $v)
                        <tr>
                            <td class="fw-bold">#{{ $v->id_venta }}</td>
                            <td>
                                <div class="small fw-semibold text-truncate" style="max-width: 150px;">
                                    {{ $v->cliente ? $v->cliente->nombre_completo : 'Cliente no especificado' }}
                                </div>
                            </td>
                            <td class="small text-muted">{{ date('d/m/Y H:i', strtotime($v->fecha)) }}</td>
                            <td class="fw-bold text-dark">Bs. {{ number_format($v->total, 2) }}</td>
                            <td>
                                @if($v->estado == 'Completado')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Completado</span>
                                @elseif($v->estado == 'Pendiente')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pendiente</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Cancelado</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('ventas.comprobante', $v->id_venta) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Imprimir Comprobante">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">No hay ventas registradas aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Productos con Stock Crítico -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Alertas de Stock Crítico
                </h6>
                <a href="{{ route('compras') }}" class="btn btn-sm btn-danger py-1 px-2 text-white">Abastecer</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th class="text-center">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosBajoStock as $p)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $p->nombre }}</div>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $p->marca }}</span>
                            </td>
                            <td class="small text-muted">{{ $p->tipoProducto->nombre_tipo ?? 'General' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $p->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $p->stock }} un.
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted small">
                                <i class="bi bi-check-circle text-success me-1"></i> Todo el inventario tiene stock suficiente.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endif
@endsection

@section('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('graficoVentas');
    if (ctx) {
        const mesesData = @json($mesesData);
        const labels = mesesData.map(item => item.mes);
        const data = mesesData.map(item => item.total);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas Mensuales (Bs.)',
                    data: data,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(value) {
                                return 'Bs. ' + value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endsection
