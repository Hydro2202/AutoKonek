@props(['rating' => 0, 'size' => 'size-4'])
@php $filled = (int) round($rating); @endphp
<span {{ $attributes->merge(['class' => 'inline-flex']) }} role="img" aria-label="{{ $rating ? number_format($rating, 1).' out of 5 stars' : 'No rating' }}">
    @for ($i = 1; $i <= 5; $i++)
        <x-ui.icon name="star" :class="$size.' '.($i <= $filled ? 'text-accent' : 'text-line-strong')" />
    @endfor
</span>
