<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $venta->tipo_comprobante }} #{{ $venta->id_venta }} - {{ $empresa->razon_social }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            padding: 25px 15px;
        }

        .receipt-container {
            max-width: 780px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .header-title {
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .receipt-badge {
            background: #0f172a;
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
        }

        .table-custom thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.8rem;
            text-transform: uppercase;
            border-bottom: 2px solid #cbd5e1;
        }

        .total-box {
            background-color: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 15px 20px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                padding: 5px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <!-- BOTONES DE ACCIÓN (NO IMPRIMIR) -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom no-print">
        <a href="{{ route('ventas.directas') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a Ventas
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-printer-fill"></i> Imprimir Comprobante
            </button>
        </div>
    </div>

    <!-- ENCABEZADO EMPRESA Y COMPROBANTE (HU30) -->
    <div class="row align-items-center mb-4">
        <div class="col-sm-7">
            @if($empresa->logo_path && file_exists(public_path($empresa->logo_path)))
                <img src="{{ asset($empresa->logo_path) }}" alt="Logo" class="img-fluid mb-2" style="max-height: 60px;">
            @endif
            <h3 class="header-title mb-1">{{ $empresa->razon_social }}</h3>
            <p class="text-muted small mb-0">{{ $empresa->direccion }} &bull; {{ $empresa->ciudad }}</p>
            <p class="text-muted small mb-0">NIT: {{ $empresa->nit }} &bull; Tel/WhatsApp: {{ $empresa->telefono }}</p>
            <p class="text-muted small mb-0">{{ $empresa->email }}</p>
        </div>
        <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
            <div class="receipt-badge mb-2">
                {{ strtoupper($venta->tipo_comprobante) }} DE VENTA
            </div>
            <h4 class="fw-bold text-dark mb-0">N° {{ str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT) }}</h4>
            <small class="text-muted d-block">Fecha: {{ date('d/m/Y H:i', strtotime($venta->fecha)) }}</small>
            <div class="mt-1">
                <span class="badge bg-light text-dark border">Pago: {{ $venta->metodo_pago }}</span>
                <span class="badge bg-light text-dark border">Despacho: {{ $venta->estado_despacho }}</span>
            </div>
        </div>
    </div>

    <!-- DATOS DEL CLIENTE Y ATENCIÓN -->
    <div class="card bg-light border-0 rounded-3 p-3 mb-4">
        <div class="row g-2 small">
            <div class="col-sm-6">
                <strong class="text-muted d-block">Cliente / Razón Social:</strong>
                <span class="fw-bold text-dark fs-6">{{ $venta->cliente ? $venta->cliente->nombre_completo : 'Consumidor Final' }}</span>
                <div class="text-muted">
                    {{ $venta->cliente && $venta->cliente->tipo_registro === 'Unipersonal' ? 'CI: ' . ($venta->cliente->unipersonal->ci ?? '-') : 'NIT: ' . ($venta->cliente->institucional->nit ?? '-') }}
                </div>
            </div>
            <div class="col-sm-6">
                <strong class="text-muted d-block">Modalidad de Venta:</strong>
                <span>{{ $venta->tipo_venta }} &bull; {{ $venta->es_venta_mayor ? 'Venta por mayor' : 'Venta directa' }} &bull; Promoción: {{ $venta->promocion ?? 'Ninguna' }}</span>
            </div>
            <div class="col-sm-6">
                <strong class="text-muted d-block">Dirección / Teléfono:</strong>
                <span>{{ $venta->cliente->direccion ?? 'Sin dirección' }} &bull; {{ $venta->cliente->telefono ?? '-' }}</span>
            </div>
            <div class="col-sm-6">
                <strong class="text-muted d-block">Atendido por:</strong>
                <span>{{ $venta->usuario ? $venta->usuario->nombre . ' ' . $venta->usuario->apellido : 'Personal PEBIE' }}</span>
            </div>
        </div>
    </div>

    <!-- TABLA DE PRODUCTOS -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>Descripción del Producto</th>
                    <th style="width: 90px;" class="text-center">Cant.</th>
                    <th style="width: 120px;" class="text-end">P. Unitario</th>
                    <th style="width: 120px;" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $n = 1; @endphp
                @if(in_array($venta->tipo_venta, ['Pedido', 'Reserva']) && $venta->detalles->isEmpty())
                    @foreach($venta->detallesPedido as $item)
                    <tr>
                        <td class="text-center text-muted">{{ $n++ }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->producto->nombre ?? 'Producto no especificado' }}</div>
                            <small class="text-muted">{{ $item->producto->marca ?? '' }}</small>
                        </td>
                        <td class="text-center">{{ $item->cantidad }}</td>
                        <td class="text-end">Bs. {{ number_format($item->precio_estimado, 2) }}</td>
                        <td class="text-end fw-semibold">Bs. {{ number_format($item->cantidad * $item->precio_estimado, 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    @foreach($venta->detalles as $item)
                    <tr>
                        <td class="text-center text-muted">{{ $n++ }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->producto->nombre ?? 'Producto no especificado' }}</div>
                            <small class="text-muted">{{ $item->producto->marca ?? '' }}</small>
                        </td>
                        <td class="text-center">{{ $item->cantidad }}</td>
                        <td class="text-end">Bs. {{ number_format($item->precio_aplicado, 2) }}</td>
                        <td class="text-end fw-semibold">Bs. {{ number_format($item->cantidad * $item->precio_aplicado, 2) }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- TOTALES Y DESCUENTOS -->
    <div class="row justify-content-end mb-4">
        <div class="col-sm-6">
            <div class="total-box">
                <div class="d-flex justify-content-between mb-1 small text-muted">
                    <span>Subtotal:</span>
                    <strong class="text-dark">Bs. {{ number_format($venta->subtotal, 2) }}</strong>
                </div>
                @if($venta->descuento > 0)
                <div class="d-flex justify-content-between mb-1 small text-danger">
                    <span>Descuento aplicado:</span>
                    <strong>- Bs. {{ number_format($venta->descuento, 2) }}</strong>
                </div>
                @endif
                <div class="d-flex justify-content-between pt-2 border-top">
                    <span class="fs-6 fw-bold text-dark">TOTAL PAGADO:</span>
                    <span class="fs-5 fw-bold text-primary">Bs. {{ number_format($venta->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- PIE DE COMPROBANTE -->
    <div class="text-center pt-3 border-top small text-muted">
        <p class="mb-1 fw-semibold text-dark">{{ $empresa->mensaje_pie_pagina }}</p>
        <p class="mb-0">Documento emitido electrónicamente por el Sistema Comercial PEBIE &bull; Oruro, Bolivia</p>
    </div>
</div>

</body>
</html>
