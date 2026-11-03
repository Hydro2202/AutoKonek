@props(['rating' => 0, 'count' => null, 'size' => 'size-4'])
@php $filled = (int) round($rating); @endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>
    <span class="inline-flex" role="img" aria-label="{{ $rating ? number_format($rating, 1).' out of 5 stars' : 'No ratings yet' }}">
        @for ($i = 1; $i <= 5; $i++)
            <x-icon name="star" :class="$size.' '.($i <= $filled ? 'text-accent' : 'text-line')" />
        @endfor
    </span>
    @if ($count !== null)
        <span class="text-xs text-muted">
            @if ($count > 0) <strong class="text-ink">{{ number_format($rating, 1) }}</strong> ({{ $count }} {{ Str::plural('review', $count) }}) @else No reviews yet @endif
        </span>
    @endif
</span>
