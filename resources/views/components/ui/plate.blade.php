{{-- Rating shown like a license plate. --}}
@props(['rating' => 0, 'count' => null])
@if ($count === 0 || ! $rating)
    <span {{ $attributes->merge(['class' => 'plate plate-muted !text-sm']) }} title="No reviews yet">New</span>
@else
    <span {{ $attributes->merge(['class' => 'plate']) }} role="img" aria-label="Rated {{ number_format($rating, 1) }} out of 5">{{ number_format($rating, 1) }}</span>
@endif
