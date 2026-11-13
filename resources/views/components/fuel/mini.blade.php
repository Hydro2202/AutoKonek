{{-- Compact fuel price ticker for dashboards: latest price, this week's DOE change and a sparkline per fuel. --}}
@props(['series', 'href' => null])
@php
    $fuels = collect($series)->filter(fn ($s) => count($s['points']) > 0);
    $spark = function (array $points): string {
        $prices = array_column($points, 'price');
        $min = min($prices);
        $range = (max($prices) - $min) ?: 1;
        $last = count($points) - 1;

        return collect($points)->map(fn ($p, $i) => ($i ? 'L' : 'M').round($last ? $i / $last * 100 : 50, 1).','.round(26 - ($p['price'] - $min) / $range * 22, 1))->implode('');
    };
@endphp

@if ($fuels->isNotEmpty())
<div {{ $attributes->merge(['class' => 'fuel-mini']) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="fuel-eyebrow !text-[0.75rem]"><span class="fuel-live" aria-hidden="true"></span> Pump prices, Metro Manila</p>
        @if ($href)<a href="{{ $href }}" class="text-xs font-semibold text-white/70 underline-offset-2 hover:text-accent hover:underline">Full chart</a>@endif
    </div>
    <ul class="mt-2 divide-y divide-white/10">
        @foreach ($fuels as $key => $fuel)
            @php
                $last = end($fuel['points']);
                $change = $last['change'] ?? 0;
                $direction = $change > 0 ? 'hike' : ($change < 0 ? 'rollback' : 'flat');
                $stroke = $direction === 'hike' ? 'var(--color-hike)' : ($direction === 'rollback' ? 'var(--color-rollback)' : 'rgb(255 255 255 / 0.6)');
            @endphp
            <li class="dash-rise flex items-center gap-3 py-2" style="--i: {{ $loop->index + 3 }}">
                <span class="w-24 shrink-0 text-sm text-white/70">{{ Str::before($fuel['label'], ' (') }}</span>
                <svg class="fuel-mini-spark" viewBox="0 0 100 28" preserveAspectRatio="none" aria-hidden="true">
                    <path d="{{ $spark($fuel['points']) }}" fill="none" stroke="{{ $stroke }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" pathLength="1" class="dash-draw" />
                </svg>
                <span class="ml-auto text-right">
                    <span class="block font-display text-xl leading-none font-semibold text-white tabular-nums">₱{{ number_format($last['price'], 2) }}</span>
                    <span class="mt-0.5 inline-flex items-center gap-0.5 text-xs font-semibold {{ $direction === 'hike' ? 'text-hike' : ($direction === 'rollback' ? 'text-rollback' : 'text-white/60') }}">
                        <x-ui.icon :name="$direction === 'hike' ? 'arrow-up' : ($direction === 'rollback' ? 'arrow-down' : 'more')" class="size-3" />
                        <span class="sr-only">{{ $direction === 'hike' ? 'Up' : ($direction === 'rollback' ? 'Down' : 'No change') }}</span>
                        {{ number_format(abs($change), 2) }}
                    </span>
                </span>
            </li>
        @endforeach
    </ul>
    <p class="mt-1 text-[0.7rem] leading-snug text-white/50">Changes every Tuesday per DOE (Department of Energy) adjustments.</p>
</div>
@endif
