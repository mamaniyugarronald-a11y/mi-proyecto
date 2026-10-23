<div class="report-chart-block mb-4">
    <h6 class="fw-bold mb-3">{{ $chartTitle }}</h6>
    @if($chartItems->isEmpty() || $chartItems->sum('valor') <= 0)
        <div class="text-muted small py-3">No hay datos para graficar en este período.</div>
    @else
        @php
            $chartColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997', '#6c757d', '#d63384'];
            $chartTotal = $chartItems->sum('valor');
            $chartCursor = 0;
            $chartStops = [];
            foreach ($chartItems as $chartIndex => $chartItem) {
                $chartEnd = $chartCursor + ((float) $chartItem['valor'] / $chartTotal * 100);
                $chartStops[] = $chartColors[$chartIndex % count($chartColors)] . ' ' . $chartCursor . '% ' . $chartEnd . '%';
                $chartCursor = $chartEnd;
            }
            $chartGradient = 'conic-gradient(' . implode(', ', $chartStops) . ')';
        @endphp
        <div class="report-donut-layout">
            <div class="report-donut" style="background: {{ $chartGradient }}"><span>{{ number_format($chartTotal, 0) }}</span></div>
            <div class="report-donut-legend">
                @foreach($chartItems as $chartIndex => $chartItem)
                    <div class="report-legend-item"><span class="report-legend-swatch" style="background: {{ $chartColors[$chartIndex % count($chartColors)] }}"></span><span>{{ $chartItem['etiqueta'] }}</span><strong>{{ number_format($chartItem['valor'], 2) }}</strong></div>
                @endforeach
            </div>
        </div>
    @endif
</div>
