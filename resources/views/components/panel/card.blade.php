{{-- Admin content card with a title bar. The "actions" slot sits on the right of the title bar. --}}
@props(['title', 'subtitle' => null, 'flush' => false])
<section {{ $attributes->merge(['class' => 'rounded-md border border-line bg-white']) }}>
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-3.5">
        <div>
            <h2 class="text-base">{{ $title }}</h2>
            @if ($subtitle)<p class="mt-0.5 text-[0.8125rem] text-muted">{{ $subtitle }}</p>@endif
        </div>
        @isset($actions){{ $actions }}@endisset
    </header>
    <div @class(['p-5' => ! $flush])>{{ $slot }}</div>
</section>
