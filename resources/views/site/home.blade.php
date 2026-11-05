@extends('layouts.site')

@section('title', 'Find the Services Your Vehicle Needs')

@section('content')
{{-- Hero: search on the left, a live "service board" on the right --}}
<section class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:py-20">
    <div>
        <h1 class="text-[3.75rem] leading-[0.9] sm:text-[5.75rem]">Find the services your vehicle needs.</h1>
        <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink/80">Look up gas stations, car washes, repair shops, tire shops, parking and battery services, with prices and reviews from other drivers.</p>

        @auth
        <form action="{{ route('establishments.index') }}" method="GET" role="search"
              class="mt-8 flex max-w-xl flex-col overflow-hidden rounded-sign border-2 border-navy bg-white sm:flex-row">
            <label for="hero-q" class="sr-only">Establishment name</label>
            <div class="relative flex-1">
                <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-muted" />
                <input id="hero-q" type="search" name="q" placeholder="Name of a shop or station" autocomplete="off"
                       class="w-full border-0 py-3.5 pr-3 pl-11 text-base text-ink placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-inset focus:ring-steel/40">
            </div>
            <label for="hero-region" class="sr-only">Region</label>
            <select id="hero-region" name="region"
                    class="border-t-2 border-navy bg-white px-3 py-3.5 text-base text-ink focus:outline-none focus:ring-2 focus:ring-inset focus:ring-steel/40 sm:w-56 sm:border-t-0 sm:border-l-2">
                <option value="">All of the Philippines</option>
                @foreach ($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary !rounded-none px-7 py-3.5 text-base">Search</button>
        </form>
        @else
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('login') }}" class="btn-primary btn-lg">Log in to start</a>
            <a href="{{ route('register') }}" class="btn-outline btn-lg">Create free account</a>
        </div>
        <p class="mt-3 max-w-md text-sm text-muted">Search, listings, maps and reviews are for registered users. It takes a minute to sign up.</p>
        @endauth

        <p class="mt-5 text-[0.9375rem] text-muted">
            <strong class="font-semibold text-ink">{{ $totals['establishments'] }}</strong> approved listings and
            <strong class="font-semibold text-ink">{{ $totals['reviews'] }}</strong> customer reviews so far.
            @auth<a href="{{ route('places.index') }}" class="font-semibold text-steel underline underline-offset-2">Browse every city in the Philippines</a>@endauth
        </p>
    </div>

    <aside class="rounded-lg bg-navy-950 p-5 text-white sm:p-6" aria-labelledby="board-title">
        <div class="flex items-baseline justify-between border-b border-white/15 pb-3">
            <h2 id="board-title" class="text-2xl !text-white">Service board</h2>
            <span class="text-sm text-white/55">Listings per category</span>
        </div>
        <ul class="divide-y divide-white/10">
            @foreach ($categories as $category)
                <li>
                    <a href="{{ auth()->check() ? route('categories.show', $category) : route('login') }}" @auth data-transition data-rt-label="{{ $category->name }}" @endauth class="group -mx-2 flex items-center gap-4 rounded-sign px-2 py-3.5 hover:bg-white/5">
                        <span class="sign size-11 !bg-navy-800"><x-ui.icon :name="$category->icon()" class="size-6" /></span>
                        <span class="flex-1 font-display text-[1.65rem] leading-none font-medium">{{ $category->name }}</span>
                        <span data-flap="{{ sprintf('%02d', $category->establishments_count) }}" class="font-display text-[1.65rem] leading-none font-semibold text-accent">{{ sprintf('%02d', $category->establishments_count) }}</span>
                        <x-ui.icon name="chevron-right" class="size-5 text-white/40 transition-transform group-hover:translate-x-0.5 group-hover:text-accent" />
                    </a>
                </li>
            @endforeach
        </ul>
        <a href="{{ auth()->check() ? route('establishments.index') : route('login') }}" class="mt-2 inline-flex items-center gap-2 text-[0.9375rem] font-semibold text-white hover:text-accent">
            {{ auth()->check() ? 'Browse every listing' : 'Log in to browse listings' }} <x-ui.icon name="arrow-right" class="size-4" />
        </a>
    </aside>
</section>

@auth
{{-- Local: places around Lucena City and Quezon province --}}
@if ($places->isNotEmpty())
<section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6" aria-labelledby="places-title">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 id="places-title" class="text-3xl">Around {{ config('autokonek.home.city') }} and {{ config('autokonek.home.province') }}</h2>
            <p class="mt-1 text-muted">Pick a town to see what is listed there.</p>
        </div>
        <a href="{{ route('places.index') }}" class="text-[0.9375rem] font-semibold text-steel underline underline-offset-2">All cities and regions</a>
    </div>
    <ul class="mt-5 flex flex-wrap gap-2.5">
        @foreach ($places->take(10) as $place)
            <li>
                <a href="{{ route('establishments.index', ['city' => $place->id]) }}"
                   class="inline-flex items-center gap-2 rounded-sign border border-line-strong bg-white py-2 pr-3 pl-3.5 font-medium text-navy hover:border-navy">
                    <x-ui.icon name="map-pin" class="size-4 text-accent-600" />
                    {{ $place->name }}
                    <span class="rounded-full bg-page px-2 text-sm text-muted">{{ $place->establishments_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</section>
@endif
{{-- Recently approved --}}
<section class="border-y border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-4xl">Listed near {{ config('autokonek.home.city') }}</h2>
                <p class="mt-1.5 text-muted">A few places in {{ config('autokonek.home.province') }}. Use the filters to search the whole Philippines.</p>
            </div>
            <a href="{{ route('establishments.index') }}" class="btn-outline">See all listings</a>
        </div>

        @if ($recent->isEmpty())
            <x-ui.empty-state class="mt-6" icon="building" title="No listings yet" text="Listings near {{ config('autokonek.home.city') }} will show up here." />
        @else
            <div class="mt-7 grid gap-5 lg:grid-cols-2">
                @foreach ($recent as $establishment)
                    <x-listing.card :establishment="$establishment" class="anim-rise" :style="'--i: '.$loop->index" />
                @endforeach
            </div>
        @endif
    </div>
</section>
@endauth

{{-- How it works: a real sequence of three stops --}}
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <h2 class="text-4xl">Three stops from search to review</h2>
    <ol class="mt-9 grid gap-10 md:grid-cols-3 md:gap-8">
        @foreach ([
            ['Search', 'Type a name, pick a category or a city. Results come straight from approved listings.'],
            ['Compare', 'Open a listing to see services with prices, opening hours, address and phone number.'],
            ['Review', 'After your visit, rate the place from 1 to 5 and tell other drivers what to expect.'],
        ] as $index => [$title, $text])
            <li>
                <div class="flex items-center gap-4">
                    <span class="sign size-12 font-display text-3xl font-bold">{{ $index + 1 }}</span>
                    @unless ($loop->last)<span class="roadline draw-line hidden flex-1 md:block"></span>@endunless
                </div>
                <h3 class="mt-4 text-2xl">{{ $title }}</h3>
                <p class="mt-1.5 max-w-xs leading-relaxed text-muted">{{ $text }}</p>
            </li>
        @endforeach
    </ol>
</section>

{{-- Owner call to action: for visitors who might list a business, and for owners. Not for customers or admins. --}}
@if (! auth()->check() || auth()->user()->isOwner())
<section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6">
    <div class="grid items-center gap-10 rounded-lg bg-navy p-8 text-white md:grid-cols-2 md:p-12">
        <div>
            @auth
                <h2 class="text-4xl !text-white">Have another place to list?</h2>
                <p class="mt-3 max-w-md leading-relaxed text-white/80">Submit another establishment from your dashboard. An administrator checks every listing before it goes live, so drivers can trust what they find here.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('owner.establishments.create') }}" class="btn-primary btn-lg">Add establishment</a>
                    <a href="{{ route('owner.dashboard') }}" class="btn btn-lg border border-white/30 text-white hover:bg-white/10">My dashboard</a>
                </div>
            @else
                <h2 class="text-4xl !text-white">Own a shop, station or lot?</h2>
                <p class="mt-3 max-w-md leading-relaxed text-white/80">Create a free owner account and submit your business. An administrator checks every listing before it goes live, so drivers can trust what they find here.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary btn-lg">List your business</a>
                    <a href="{{ route('login') }}" class="btn btn-lg border border-white/30 text-white hover:bg-white/10">Owner login</a>
                </div>
            @endauth
        </div>

        <div class="rounded-md bg-white p-6 text-ink">
            <p class="text-sm font-semibold text-muted">What you will see after submitting</p>
            <p class="mt-1 text-xl font-semibold text-navy">Your listing's progress</p>
            <x-listing.status-track status="pending" class="mt-5 !max-w-none" />
            <p class="mt-5 text-sm leading-relaxed text-muted">If something needs fixing, the administrator tells you exactly what. Correct it and resubmit from your dashboard.</p>
        </div>
    </div>
</section>
@endif
@endsection
