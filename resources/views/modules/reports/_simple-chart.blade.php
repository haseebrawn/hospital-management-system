@php
    $items = collect($chartData ?? [])->filter(fn ($item) => isset($item['label'], $item['value']))->values();
    $maxValue = max(1, (float) $items->max('value'));
@endphp

<div style="margin-top: 16px; padding: 14px; border: 1px solid var(--border-color); border-radius: 14px;">
    <div style="font-weight: 800; margin-bottom: 12px;">{{ $chartTitle ?? 'Chart' }}</div>

    @forelse ($items as $item)
        @php
            $value = (float) $item['value'];
            $width = $maxValue > 0 ? max(8, min(100, ($value / $maxValue) * 100)) : 8;
        @endphp
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
            <div style="width:160px; font-size:12px; color: var(--text-muted); text-transform:capitalize; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $item['label'] }}
            </div>
            <div style="flex:1; background:#eef2ff; border-radius:999px; overflow:hidden; height:12px;">
                <div style="width: {{ $width }}%; height:100%; background: linear-gradient(90deg, var(--primary), #7c3aed); border-radius:999px;"></div>
            </div>
            <div style="width:64px; text-align:right; font-size:12px; font-weight:700;">
                {{ is_float($value) ? number_format($value, 2) : number_format((int) $value) }}
            </div>
        </div>
    @empty
        <div style="font-size:13px; color: var(--text-muted);">No chart data available.</div>
    @endforelse
</div>
