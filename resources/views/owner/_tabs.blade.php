@php
    $tabs = [
        'details' => ['Details', route('owner.establishments.edit', $establishment)],
        'services' => ['Services', route('owner.services.index', $establishment)],
        'images' => ['Images', route('owner.images.index', $establishment)],
    ];
@endphp
<nav class="mb-5 flex gap-1 border-b border-line" aria-label="Establishment sections">
    @foreach ($tabs as $key => [$label, $url])
        <a href="{{ $url }}" @if ($active === $key) aria-current="page" @endif
           class="-mb-px border-b-2 px-4 py-2 text-sm font-semibold {{ $active === $key ? 'border-accent text-navy' : 'border-transparent text-muted hover:text-navy' }}">{{ $label }}</a>
    @endforeach
</nav>
