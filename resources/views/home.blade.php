@extends('layouts.app')

@section('title', 'Find the Services Your Vehicle Needs')

@section('content')
{{-- Hero --}}
<section class="bg-navy text-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-accent">Automotive services directory</p>
            <h1 class="mt-3 text-3xl font-extrabold text-white sm:text-5xl">Find the services your vehicle needs.</h1>
            <p class="mt-4 text-base text-white/80 sm:text-lg">Search gas stations, car washes, repair shops, tire shops, parking and battery services, with real customer reviews.</p>
        </div>

        <form action="{{ route('establishments.index') }}" method="GET" class="mt-8 flex max-w-2xl flex-col gap-2 rounded-lg bg-white p-2 shadow-lg sm:flex-row" role="search">
            <label for="hero-q" class="sr-only">Search by establishment name</label>
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-muted" />
                <input id="hero-q" type="search" name="q" placeholder="Search by name, e.g. car wash, tire shop..." class="w-full rounded-md py-3 pl-10 pr-3 text-sm text-ink placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-steel/30">
            </div>
            <button type="submit" class="btn-primary px-6 py-3">Search</button>
        </form>
    </div>
</section>

{{-- Category shortcuts --}}
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <h2 class="text-xl sm:text-2xl">Browse by category</h2>
    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category) }}" class="card group flex flex-col items-center gap-2 p-4 text-center transition-colors hover:border-accent">
                <span class="flex size-12 items-center justify-center rounded-full bg-page text-navy group-hover:bg-accent/15"><x-icon :name="$category->icon()" class="size-6" /></span>
                <span class="text-sm font-semibold text-navy">{{ $category->name }}</span>
                <span class="text-xs text-muted">{{ $category->establishments_count }} {{ Str::plural('listing', $category->establishments_count) }}</span>
            </a>
        @endforeach
    </div>
</section>

{{-- Recently approved --}}
<section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6">
    <div class="flex items-end justify-between gap-4">
        <h2 class="text-xl sm:text-2xl">Recently approved establishments</h2>
        <a href="{{ route('establishments.index') }}" class="text-sm font-semibold text-steel hover:underline">View all</a>
    </div>
    @if ($recent->isEmpty())
        <x-empty-state class="mt-5" icon="building" title="No establishments yet" text="Approved listings will appear here." />
    @else
        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($recent as $establishment)
                <x-establishment-card :establishment="$establishment" />
            @endforeach
        </div>
    @endif
</section>

{{-- How it works --}}
<section class="border-y border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <h2 class="text-xl sm:text-2xl">How AutoKonek works</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['search', 'Search', 'Look up an establishment by name, or filter by category and city.'],
                ['eye', 'Compare', 'Open a profile to see services, prices, hours and contact details.'],
                ['star', 'Review', 'Read ratings from other drivers and share your own experience.'],
            ] as $i => [$icon, $title, $text])
                <div class="flex gap-4">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-navy text-white"><x-icon :name="$icon" /></span>
                    <div>
                        <h3 class="text-base">{{ $i + 1 }}. {{ $title }}</h3>
                        <p class="mt-1 text-sm text-muted">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Owner CTA --}}
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <div class="flex flex-col items-start justify-between gap-5 rounded-lg bg-navy p-8 text-white md:flex-row md:items-center">
        <div class="max-w-xl">
            <h2 class="text-xl text-white sm:text-2xl">Run an automotive business?</h2>
            <p class="mt-2 text-sm text-white/80">Create a free owner account, submit your establishment, and reach drivers searching for your services. Every listing is reviewed before it goes live.</p>
        </div>
        <a href="{{ route('register') }}" class="btn-primary shrink-0 px-6 py-3">List your business <x-icon name="arrow-right" class="size-4" /></a>
    </div>
</section>
@endsection
