<div class="report-chart-block mb-4">
    <h6 class="fw-bold mb-3">{{ $chartTitle }}</h6>
    @if($chartItems->isEmpty())
        <div class="text-muted small py-3">No hay datos para graficar en este período.</div>
    @else
        @php
            $chartMax = max(1, $chartItems->max(fn($item) => abs((float) $item['valor'])));
        @endphp
        <div class="report-bars">
            @foreach($chartItems as $item)
                @php
                    $barWidth = min(100, abs((float) $item['valor']) / $chartMax * 100);
                    $barDecimals = abs(fmod((float) $item['valor'], 1)) > 0.0001 ? 2 : 0;
                @endphp
                <div class="report-bar-row">
                    <div class="report-bar-label" title="{{ $item['etiqueta'] }}">{{ $item['etiqueta'] }}</div>
                    <div class="report-bar-track"><div class="report-bar-fill {{ $item['valor'] < 0 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $barWidth }}%"></div></div>
                    <div class="report-bar-value {{ $item['valor'] < 0 ? 'text-danger' : '' }}">{{ number_format($item['valor'], $barDecimals) }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
