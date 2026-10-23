<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Cierre de Caja - {{ $cierre->fecha_cierre }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff; color: #000; padding: 20px; }
        .ticket-cierre { max-width: 600px; margin: 0 auto; border: 1px dashed #666; padding: 25px; border-radius: 8px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .ticket-cierre { border: none; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-primary px-4 me-2">Imprimir Cierre</button>
    <button onclick="window.close()" class="btn btn-secondary px-4">Cerrar Ventana</button>
</div>

<div class="ticket-cierre">
    <div class="text-center mb-3">
        <h4 class="fw-bold mb-1">{{ $empresa->razon_social }}</h4>
        <div class="small text-muted">NIT: {{ $empresa->nit }} | Tel: {{ $empresa->telefono }}</div>
        <div class="small text-muted">{{ $empresa->direccion }} - {{ $empresa->ciudad }}</div>
        <hr class="my-2">
        <h5 class="fw-bold text-uppercase mt-2">Arqueo & Cierre de Caja Diario</h5>
        <div class="small"><strong>Fecha de Cierre:</strong> {{ date('d/m/Y', strtotime($cierre->fecha_cierre)) }}</div>
        <div class="small"><strong>Generado:</strong> {{ date('d/m/Y H:i:s', strtotime($cierre->fecha_registro)) }}</div>
        <div class="small"><strong>Responsable:</strong> {{ $cierre->usuario->nombre ?? 'Usuario' }} ({{ $cierre->usuario->usuario ?? '' }})</div>
    </div>

    <table class="table table-bordered my-3">
        <tbody>
            <tr>
                <th class="w-50 bg-light">Ventas en Efectivo</th>
                <td class="text-end fw-bold text-success">Bs. {{ number_format($cierre->total_efectivo, 2) }}</td>
            </tr>
            <tr>
                <th class="bg-light">Ventas Transferencias / QR</th>
                <td class="text-end fw-bold text-primary">Bs. {{ number_format($cierre->total_transferencia_qr, 2) }}</td>
            </tr>
            <tr class="table-dark fs-5">
                <th>Total General Recaudado</th>
                <td class="text-end fw-bold">Bs. {{ number_format($cierre->total_general, 2) }}</td>
            </tr>
            <tr>
                <th class="bg-light">Cantidad de Ventas Realizadas</th>
                <td class="text-end fw-bold">{{ $cierre->total_ventas_conteo }} transacciones</td>
            </tr>
        </tbody>
    </table>

    @if($cierre->observaciones)
    <div class="p-2 border rounded bg-light small mb-3">
        <strong>Observaciones:</strong> {{ $cierre->observaciones }}
    </div>
    @endif

    <div class="row mt-5 text-center small">
        <div class="col-6">
            <div class="border-top border-dark pt-1 mx-3">
                Firma Responsable de Caja
            </div>
        </div>
        <div class="col-6">
            <div class="border-top border-dark pt-1 mx-3">
                V°B° Administración PEBIE
            </div>
        </div>
    </div>
</div>

</body>
</html>
