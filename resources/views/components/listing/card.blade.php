{{-- Listing card shaped like a garage job card: photo on the left, perforated edge, details on the right. --}}
@props(['establishment'])
<article {{ $attributes->merge(['class' => 'ticket']) }}>
    {{-- Covers the whole card so a click anywhere opens the listing. The title link below is the keyboard and screen reader link. --}}
    <a href="{{ route('establishments.show', $establishment) }}" data-transition data-rt-label="{{ $establishment->name }}" tabindex="-1" aria-hidden="true" class="absolute inset-0 z-[1]"></a>
    <div class="ticket-media">
        <img src="{{ $establishment->imageUrl() }}" alt="" loading="lazy" class="size-full object-cover">
        <span class="sign absolute top-3 left-3 size-9" title="{{ $establishment->category?->name }}">
            <x-ui.icon :name="$establishment->category?->icon() ?? 'car'" class="size-[1.15rem]" />
        </span>
    </div>

    <div class="ticket-body">
        <p class="text-sm font-semibold text-steel">{{ $establishment->category?->name }}</p>

        <h3 class="mt-0.5 text-[1.6rem]">
            <a href="{{ route('establishments.show', $establishment) }}" data-transition data-rt-label="{{ $establishment->name }}" class="hover:text-steel">{{ $establishment->name }}</a>
        </h3>

        <p class="mt-1.5 flex items-start gap-1.5 text-[0.9375rem] text-muted">
            <x-ui.icon name="map-pin" class="mt-0.5 size-4 shrink-0" />
            <span>{{ $establishment->locationLabel() }}</span>
        </p>

        @isset($establishment->distance_km)
            <p class="mt-1 inline-flex items-center gap-1.5 text-sm font-semibold text-navy">
                <x-ui.icon name="navigation" class="size-3.5 text-accent-600" />
                {{ $establishment->distance_km < 1 ? round($establishment->distance_km * 1000).' m' : number_format($establishment->distance_km, 1).' km' }} from you
            </p>
        @endisset

        <p class="mt-3 line-clamp-2 text-[0.9375rem] leading-relaxed text-ink/85">{{ $establishment->description }}</p>

        <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-4">
            <div class="flex items-center gap-2.5">
                <x-ui.plate :rating="$establishment->averageRating()" :count="$establishment->reviewCount()" />
                <span class="text-sm text-muted">
                    {{ $establishment->reviewCount() }} {{ Str::plural('review', $establishment->reviewCount()) }}
                </span>
            </div>
            <div class="relative z-[2] flex items-center gap-2">
                <a href="{{ route('establishments.show', [$establishment, 'directions' => 1]) }}#directions" data-transition data-rt-label="{{ $establishment->name }}"
                   class="btn-outline btn-sm" title="Show the route from where you are" aria-label="Directions to {{ $establishment->name }}">
                    <x-ui.icon name="navigation" class="size-3.5" /> Directions
                </a>
                <span class="btn-navy btn-sm pointer-events-none" aria-hidden="true">Details <x-ui.icon name="arrow-right" class="size-3.5" /></span>
            </div>
        </div>
    </div>
</article>
