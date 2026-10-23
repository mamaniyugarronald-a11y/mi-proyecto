<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} - {{ $empresa->razon_social }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        body { background:#f8fafc; padding:25px 15px; font-family:Arial,sans-serif; color:#1e293b; }
        .documento { max-width:1000px; margin:auto; background:#fff; padding:32px; border:1px solid #e2e8f0; border-radius:12px; }
        .sello { background:#0f172a; color:white; border-radius:5px; padding:6px 12px; font-weight:700; }
        .table th { background:#f1f5f9; font-size:.76rem; text-transform:uppercase; }
        @media print { body{background:#fff;padding:0}.no-print{display:none!important}.documento{border:0;padding:5px;max-width:100%} }
    </style>
</head>
<body>
<main class="documento">
    <div class="d-flex justify-content-between mb-4 no-print">
        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Volver</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()">Imprimir</button>
    </div>
    <div class="row border-bottom pb-3 mb-4">
        <div class="col-sm-8"><h3 class="fw-bold mb-1">{{ $empresa->razon_social }}</h3><p class="small text-muted mb-0">{{ $empresa->direccion }} · NIT: {{ $empresa->nit }}</p></div>
        <div class="col-sm-4 text-sm-end mt-3 mt-sm-0"><span class="sello">{{ strtoupper($titulo) }}</span><div class="small text-muted mt-2">Del {{ date('d/m/Y', strtotime($fechaDesde)) }} al {{ date('d/m/Y', strtotime($fechaHasta)) }}</div></div>
    </div>
    <div class="d-flex justify-content-between bg-light rounded p-3 mb-3"><span><strong>Operaciones:</strong> {{ $operaciones->count() }}</span><span><strong>Total del período:</strong> Bs. {{ number_format($total, 2) }}</span></div>
    <div class="table-responsive"><table class="table table-bordered table-sm align-middle"><thead><tr><th>N°</th><th>Fecha</th><th>{{ $titulo === 'Listado de ventas' ? 'Cliente' : 'Proveedor' }}</th><th>Detalle de productos</th><th class="text-end">Total</th></tr></thead><tbody>
        @forelse($operaciones as $operacion)
            @php
                $esVenta = !empty($operacion->id_venta);
                $detalles = $esVenta && in_array($operacion->tipo_venta, ['Pedido', 'Reserva']) && $operacion->detalles->isEmpty() ? $operacion->detallesPedido : $operacion->detalles;
                $totalOperacion = $esVenta ? $operacion->total : $detalles->sum(function ($detalle) { return $detalle->cantidad * $detalle->precio_compra; });
            @endphp
            <tr><td>#{{ $esVenta ? $operacion->id_venta : $operacion->id_compra }}</td><td>{{ date('d/m/Y H:i', strtotime($operacion->fecha)) }}</td><td>{{ $esVenta ? ($operacion->cliente->nombre_completo ?? 'Cliente') : ($operacion->proveedor->nombre_empresa ?? 'Proveedor') }}</td><td class="small">@foreach($detalles as $detalle)<div>{{ $detalle->producto->nombre ?? 'Producto' }} × {{ $detalle->cantidad }}</div>@endforeach</td><td class="text-end">Bs. {{ number_format($totalOperacion, 2) }}</td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No hay registros para el período seleccionado.</td></tr>
        @endforelse
    </tbody><tfoot><tr class="table-light"><th colspan="4" class="text-end">TOTAL DEL PERÍODO</th><th class="text-end text-primary">Bs. {{ number_format($total, 2) }}</th></tr></tfoot></table></div>
    <p class="small text-muted text-center border-top pt-3 mb-0">Documento emitido por el Sistema Comercial PEBIE · Oruro, Bolivia</p>
</main>
</body>
</html>
