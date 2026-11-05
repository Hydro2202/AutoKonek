@extends('layouts.site')

@section('title', 'Cities and regions')
@section('description', 'Browse AutoKonek by place: every region, province, city and municipality in the Philippines.')

@section('content')
<section class="border-b border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <h1 class="text-5xl sm:text-6xl">Cities and regions</h1>
        <p class="mt-3 max-w-2xl text-lg leading-relaxed text-ink/80">
            All {{ $regions->count() }} regions of the Philippines, with their {{ $cityCount }} cities.
            Pick a place to see the services listed there. The number beside a place is how many approved listings it has.
        </p>

        <div class="mt-5 inline-flex rounded-sign border border-line-strong p-0.5 text-sm font-semibold" role="group" aria-label="What to show">
            <a href="{{ route('places.index') }}" @if (! $showAll) aria-current="true" @endif
               class="rounded-[4px] px-3.5 py-1.5 {{ ! $showAll ? 'bg-navy text-white' : 'text-navy hover:bg-page' }}">Cities ({{ $cityCount }})</a>
            <a href="{{ route('places.index', ['show' => 'all']) }}" @if ($showAll) aria-current="true" @endif
               class="rounded-[4px] px-3.5 py-1.5 {{ $showAll ? 'bg-navy text-white' : 'text-navy hover:bg-page' }}">Cities and municipalities ({{ number_format($townCount) }})</a>
        </div>
    </div>
</section>

<div class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-14">
    <nav aria-label="Regions" class="lg:sticky lg:top-24 lg:self-start">
        <p class="text-sm font-semibold text-navy">Jump to a region</p>
        <ol class="mt-3 space-y-1 border-l border-line text-[0.9375rem]">
            @foreach ($regions as $region)
                @continue(! $placesByRegion->has($region->id))
                <li><a href="#region-{{ $region->id }}" class="-ml-px block border-l-2 border-transparent py-0.5 pl-3 text-muted hover:border-accent hover:text-navy">{{ $region->name }}</a></li>
            @endforeach
        </ol>
    </nav>

    <div class="space-y-12">
        @foreach ($regions as $region)
            @php $inRegion = $placesByRegion->get($region->id, collect()); @endphp
            @continue($inRegion->isEmpty())

            <section id="region-{{ $region->id }}" class="scroll-mt-24" aria-labelledby="region-title-{{ $region->id }}">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b-2 border-navy pb-2">
                    <h2 id="region-title-{{ $region->id }}" class="text-3xl">{{ $region->label() }}</h2>
                    <p class="text-sm text-muted">{{ $inRegion->count() }} {{ $showAll ? 'places' : 'cities' }}</p>
                </div>

                <div class="mt-4 space-y-6">
                    @foreach ($inRegion->groupBy('province_id') as $provinceId => $items)
                        <div>
                            <h3 class="text-lg text-steel">{{ $provinceId ? $provinces[$provinceId]->name : ($region->name === 'NCR' ? 'Metro Manila' : $region->name) }}</h3>
                            <ul class="mt-2 grid gap-x-6 gap-y-1 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($items as $place)
                                    <li>
                                        <a href="{{ route('establishments.index', ['city' => $place->id]) }}"
                                           class="flex items-center justify-between gap-3 rounded-sign px-2 py-1.5 text-[0.9375rem] hover:bg-white">
                                            <span class="text-navy">{{ $place->name }}@if ($showAll && ! $place->is_city)<span class="text-muted"> (municipality)</span>@endif</span>
                                            @if ($place->establishments_count)
                                                <span class="rounded-full bg-accent-100 px-2 text-sm font-semibold text-accent-600">{{ $place->establishments_count }}</span>
                                            @else
                                                <span class="text-sm text-line-strong">0</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <p class="text-sm text-muted">Place names come from the Philippine Standard Geographic Code of the Philippine Statistics Authority. Newly created cities may appear after the list is next updated.</p>
    </div>
</div>
@endsection
