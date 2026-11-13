{{--
| Fuel price watch: a trading-style board of weekly Metro Manila pump prices.
| Everything important (prices, changes, the full table) is in the markup, so it reads
| without JavaScript; resources/js/modules/fuel-tracker.js adds the live chart and motion.
--}}
@props(['series'])
@php
    $fuels = collect($series)->filter(fn ($s) => count($s['points']) > 0);
    $latestDate = $fuels->map(fn ($s) => end($s['points'])['date'])->max();
    $peso = fn (float $value) => '₱'.number_format($value, 2);
    $weeks = $fuels->flatMap(fn ($s) => array_column($s['points'], 'date'))->unique()->sortDesc()->values();
    $byDate = $fuels->map(fn ($s) => collect($s['points'])->keyBy('date'));
@endphp

@if ($fuels->isNotEmpty())
<section {{ $attributes->merge(['class' => 'fuel-board']) }} data-fuel-tracker aria-labelledby="fuel-title">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="fuel-eyebrow"><span class="fuel-live" aria-hidden="true"></span> Fuel price watch</p>
            <h2 id="fuel-title" class="mt-1 text-[2.4rem] leading-none !text-white sm:text-5xl">Pump prices this week</h2>
        </div>
        <p class="text-sm text-white/65">
            Metro Manila &middot; per liter &middot; effective
            <time datetime="{{ $latestDate }}" class="font-semibold text-white">{{ \Illuminate\Support\Carbon::parse($latestDate)->format('D, M j, Y') }}</time>
        </p>
    </header>

    {{-- Ticker tape of this week's adjustments (decorative; the same facts are in the tiles below). --}}
    <div class="fuel-tape" aria-hidden="true">
        <div class="fuel-tape-track">
            @foreach ([1, 2] as $copy)
                @foreach ($fuels as $key => $fuel)
                    @php $last = end($fuel['points']); $change = $last['change'] ?? 0; @endphp
                    <span class="fuel-tape-item">
                        <span class="text-white/55">{{ strtoupper($key) }}</span>
                        <span class="font-semibold text-white">{{ $peso($last['price']) }}</span>
                        <span class="{{ $change > 0 ? 'text-hike' : ($change < 0 ? 'text-rollback' : 'text-white/60') }}">
                            {{ $change > 0 ? '▲' : ($change < 0 ? '▼' : '■') }} {{ number_format(abs($change), 2) }}
                        </span>
                    </span>
                @endforeach
                <span class="fuel-tape-item text-white/55">DOE weekly adjustment &middot; every Tuesday</span>
            @endforeach
        </div>
    </div>

    {{-- One tile per fuel; they also work as tabs that choose the chart below. --}}
    <div class="mt-5 grid gap-3 md:grid-cols-3" role="tablist" aria-label="Choose a fuel to chart">
        @foreach ($fuels as $key => $fuel)
            @php
                $last = end($fuel['points']);
                $change = $last['change'] ?? 0;
                $direction = $change > 0 ? 'hike' : ($change < 0 ? 'rollback' : 'flat');
            @endphp
            <button type="button" role="tab" id="fuel-tab-{{ $key }}" data-fuel-tab="{{ $key }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="fuel-panel" tabindex="{{ $loop->first ? '0' : '-1' }}"
                    class="fuel-tile" style="--i: {{ $loop->index }}">
                <span class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1.5">
                    <span class="text-[0.9375rem] font-semibold text-white/80">{{ $fuel['label'] }}</span>
                    <span class="fuel-chip fuel-chip-{{ $direction }}">
                        <x-ui.icon :name="$direction === 'hike' ? 'arrow-up' : ($direction === 'rollback' ? 'arrow-down' : 'more')" class="size-3.5" />
                        {{ $direction === 'hike' ? 'Hike' : ($direction === 'rollback' ? 'Rollback' : 'No change') }}
                        @if ($change != 0) {{ $peso(abs($change)) }} @endif
                    </span>
                </span>
                <span class="mt-2 flex items-end justify-between gap-3">
                    <span class="fuel-price" data-count-to="{{ $last['price'] }}">{{ $peso($last['price']) }}</span>
                    <svg class="fuel-spark" data-spark="{{ $key }}" viewBox="0 0 120 36" preserveAspectRatio="none" aria-hidden="true"></svg>
                </span>
            </button>
        @endforeach
    </div>

    {{-- The chart. Filled in by JavaScript; the table below holds the same numbers. --}}
    <div id="fuel-panel" role="tabpanel" aria-labelledby="fuel-tab-{{ $fuels->keys()->first() }}" class="fuel-panel" data-fuel-panel hidden>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <dl class="fuel-stats" data-fuel-stats></dl>
            <div class="fuel-range" role="group" aria-label="Time range">
                @foreach (['1M' => 5, '3M' => 13, 'All' => 0] as $label => $weeksBack)
                    <button type="button" data-fuel-range="{{ $weeksBack }}" aria-pressed="{{ $label === 'All' ? 'true' : 'false' }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
        <div class="fuel-chart" data-fuel-chart>
            <div class="fuel-tooltip" data-fuel-tooltip hidden></div>
        </div>
        <p class="sr-only" aria-live="polite" data-fuel-live></p>
        <p class="mt-2 text-xs text-white/55">Use the arrow keys on the chart to step through the weeks. Bars under the line show each week's DOE adjustment: <span class="text-hike">red up = price hike</span>, <span class="text-rollback">green down = rollback</span>.</p>
    </div>

    {{-- The DOE note. --}}
    <div class="fuel-note">
        <x-ui.icon name="info" class="mt-0.5 size-5 shrink-0 text-accent" />
        <p>
            <strong class="text-white">Prices change every week.</strong>
            The <abbr title="Department of Energy">DOE</abbr> (Department of Energy) announces oil price adjustments, usually on Monday, and they take effect on Tuesday,
            depending on world oil prices and DOE decisions. Prices shown are Metro Manila estimates: the DOE common price for Aug 25&ndash;31, 2026,
            plus or minus each weekly DOE adjustment. What you pay depends on the station, brand and province.
        </p>
    </div>

    <details class="fuel-table">
        <summary>View all weeks as a table</summary>
        <div class="mt-3 overflow-x-auto">
            <table>
                <caption class="sr-only">Weekly Metro Manila pump prices and DOE adjustments, newest first</caption>
                <thead>
                    <tr>
                        <th scope="col">Week of</th>
                        @foreach ($fuels as $fuel)<th scope="col">{{ $fuel['label'] }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($weeks as $date)
                        <tr>
                            <th scope="row">{{ \Illuminate\Support\Carbon::parse($date)->format('M j, Y') }}</th>
                            @foreach ($fuels as $key => $fuel)
                                @php $point = $byDate[$key][$date] ?? null; @endphp
                                <td>
                                    @if ($point)
                                        {{ $peso($point['price']) }}
                                        @if ($point['change'] !== null)
                                            <span class="{{ $point['change'] > 0 ? 'text-hike' : ($point['change'] < 0 ? 'text-rollback' : 'text-white/60') }}">
                                                ({{ $point['change'] > 0 ? '+' : ($point['change'] < 0 ? '−' : '') }}{{ number_format(abs($point['change']), 2) }})
                                            </span>
                                        @endif
                                    @else
                                        &ndash;
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>

    <script type="application/json" data-fuel-data>@json($fuels)</script>
</section>
@endif
