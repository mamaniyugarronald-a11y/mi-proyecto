@extends('adm_plantilla')

@section('content')
<style>
    .modal-holder, .modal-holder > td { height: 0 !important; padding: 0 !important; border: 0 !important; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="{{ $tipoListado === 'reservas' ? 'bi bi-bookmark-check' : 'bi bi-cash-stack' }} text-primary me-2"></i>{{ $tipoListado === 'reservas' ? 'Reservas de venta' : 'Historial de venta directa' }}</h3>
        <p class="text-muted small mb-0">{{ $tipoListado === 'reservas' ? 'Confirma, despacha y completa las reservas desde aquí.' : 'Consulta ventas directas y gestiona su despacho.' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ventas.imprimir.listado', array_merge(request()->query(), ['tipo_venta' => $tipoListado === 'reservas' ? 'Reserva' : 'Venta Directa'])) }}" target="_blank" class="btn btn-outline-dark shadow-sm"><i class="bi bi-printer me-1"></i>Imprimir período</a>
        @if(in_array((int) session('rol'), [1, 2]))<a href="{{ route('ventas.pos') }}" class="btn btn-primary shadow-sm"><i class="bi bi-cart-plus-fill me-1"></i>Nueva venta</a>@endif
    </div>
</div>

@if(session('mensaje'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert"><i class="bi bi-check-circle-fill me-2"></i>{{ session('mensaje') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light"><div class="card-body p-3">
    <form method="GET" action="{{ $tipoListado === 'reservas' ? route('ventas.reservas') : route('ventas.directas') }}" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small fw-semibold text-muted mb-1">Desde</label><input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde', now()->format('Y-m-d')) }}"></div>
        <div class="col-md-3"><label class="form-label small fw-semibold text-muted mb-1">Hasta</label><input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta', now()->format('Y-m-d')) }}"></div>
        <div class="col-md-2"><label class="form-label small fw-semibold text-muted mb-1">Estado</label><select name="estado" class="form-select form-select-sm"><option value="">Todos</option><option value="Pendiente" @selected(request('estado') === 'Pendiente')>Pendiente</option><option value="Confirmado" @selected(request('estado') === 'Confirmado')>Confirmado</option><option value="Completado" @selected(request('estado') === 'Completado')>Completado</option><option value="Cancelado" @selected(request('estado') === 'Cancelado')>Cancelado</option></select></div>
        <div class="col-md-2"><label class="form-label small fw-semibold text-muted mb-1">Despacho</label><select name="estado_despacho" class="form-select form-select-sm"><option value="">Todos</option><option value="Pendiente" @selected(request('estado_despacho') === 'Pendiente')>Pendiente</option><option value="Despachado" @selected(request('estado_despacho') === 'Despachado')>Despachando</option><option value="Entregado" @selected(request('estado_despacho') === 'Entregado')>Entregado</option></select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Filtrar</button><a href="{{ $tipoListado === 'reservas' ? route('ventas.reservas') : route('ventas.directas') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros"><i class="bi bi-x-circle"></i></a></div>
    </form>
</div></div>

<div class="d-flex justify-content-end mb-3"><div class="btn-group btn-group-sm" role="group" aria-label="Tipo de operación"><a href="{{ route('ventas.directas', request()->query()) }}" class="btn {{ $tipoListado === 'directas' ? 'btn-primary' : 'btn-outline-primary' }}">Venta directa</a><a href="{{ route('ventas.reservas', request()->query()) }}" class="btn {{ $tipoListado === 'reservas' ? 'btn-primary' : 'btn-outline-primary' }}">Reservas</a></div></div>
@include('partials.ventas_tabla', ['ventas' => $ventas, 'esReserva' => $tipoListado === 'reservas', 'prefijo' => $tipoListado === 'reservas' ? 'reserva' : 'directa'])
@endsection
