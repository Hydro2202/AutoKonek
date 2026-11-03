@props(['establishment'])
<article class="card flex flex-col overflow-hidden transition-shadow hover:shadow-md">
    <a href="{{ route('establishments.show', $establishment) }}" class="block aspect-[16/10] overflow-hidden bg-page">
        <img src="{{ $establishment->imageUrl() }}" alt="{{ $establishment->name }}" loading="lazy" class="size-full object-cover">
    </a>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-steel">{{ $establishment->category?->name }}</p>
        <h3 class="mt-1 text-lg leading-snug">
            <a href="{{ route('establishments.show', $establishment) }}" class="hover:text-steel">{{ $establishment->name }}</a>
        </h3>
        <p class="mt-1 flex items-start gap-1.5 text-sm text-muted">
            <x-icon name="map-pin" class="mt-0.5 size-4 shrink-0" />
            <span>{{ $establishment->address }}, {{ $establishment->locationLabel() }}</span>
        </p>
        <x-stars class="mt-3" :rating="$establishment->averageRating()" :count="$establishment->reviewCount()" />
        <p class="mt-3 line-clamp-2 text-sm text-ink/80">{{ $establishment->description }}</p>
        <div class="mt-auto pt-4">
            <a href="{{ route('establishments.show', $establishment) }}" class="btn-outline w-full">View details <x-icon name="arrow-right" class="size-4" /></a>
        </div>
    </div>
</article>
