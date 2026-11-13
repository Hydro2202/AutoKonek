{{--
| Dark header panel for dashboards: greeting, headline numbers that count up, and the fuel ticker.
| $stats: list of [label, value, decimals, note]. Every value comes from the controller; null shows a dash.
--}}
@props(['eyebrow', 'title', 'stats' => [], 'fuelSeries' => [], 'fuelHref' => null])
<section {{ $attributes->merge(['class' => 'dash-hero']) }} aria-labelledby="dash-hero-title">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center">
        <div>
            <p class="fuel-eyebrow dash-rise" style="--i: 0"><span class="fuel-live" aria-hidden="true"></span> {{ $eyebrow }}</p>
            <h2 id="dash-hero-title" class="dash-rise mt-1 text-4xl leading-none !text-white sm:text-[2.75rem]" style="--i: 1">{{ $title }}</h2>
            @if ($slot->isNotEmpty())<div class="dash-rise mt-2 text-[0.9375rem] text-white/70" style="--i: 2">{{ $slot }}</div>@endif

            @if ($stats)
                <dl @class(['mt-6 grid grid-cols-2 gap-x-6 gap-y-5', 'sm:grid-cols-3' => count($stats) === 3, 'sm:grid-cols-4' => count($stats) >= 4])>
                    @foreach ($stats as [$label, $value, $decimals, $note])
                        <div class="dash-rise border-l-2 border-accent/70 pl-3" style="--i: {{ $loop->index + 2 }}">
                            <dt class="text-xs font-semibold tracking-wide text-white/60 uppercase">{{ $label }}</dt>
                            @if ($value === null)
                                <dd class="mt-1 font-display text-4xl leading-none font-semibold text-white/60">&ndash;</dd>
                            @else
                                <dd class="mt-1 font-display text-4xl leading-none font-semibold text-white tabular-nums" data-count="{{ $value }}" data-decimals="{{ $decimals }}">{{ number_format($value, $decimals) }}</dd>
                            @endif
                            @if ($note)<dd class="mt-1 text-xs text-white/55">{{ $note }}</dd>@endif
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>

        <x-fuel.mini :series="$fuelSeries" :href="$fuelHref" class="dash-rise" style="--i: 3" />
    </div>
</section>
