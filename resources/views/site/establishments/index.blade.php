@extends('layouts.site')

@section('title', 'Find services')

@php
    $activeCategory = $categories->firstWhere('slug', $filters['category'] ?? null);
    $regionId = $location['regionId'];
    $provinceId = $location['provinceId'];
    $cityId = $location['cityId'];

    $region = $location['regions']->firstWhere('id', $regionId);
    $province = $location['provinces']->firstWhere('id', $provinceId);
    $city = $location['cities']->firstWhere('id', $cityId);

    // One removable chip per active filter. Removing a region also clears the province and city under it.
    $chips = collect([
        ['label' => filled($filters['q'] ?? null) ? 'Name: '.$filters['q'] : null, 'remove' => ['q']],
        ['label' => $activeCategory?->name, 'remove' => ['category']],
        ['label' => $region?->name, 'remove' => ['region', 'province', 'city']],
        ['label' => $province?->name, 'remove' => ['province', 'city']],
        ['label' => $city?->name, 'remove' => ['city']],
        ['label' => $hasPosition ? 'Near me' : null, 'remove' => ['lat', 'lng', 'sort']],
    ])->filter(fn ($chip) => $chip['label']);

    $filterCount = $chips->count();
    $showProvince = $location['provinces']->isNotEmpty();
    $showCity = $location['cities']->isNotEmpty();
@endphp

@section('content')
@if ($welcome)
    {{-- Customers: an animated welcome panel with their numbers and this week's pump prices. --}}
    <div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6">
        <h1 class="sr-only">Find services</h1>
        <x-dash.hero :eyebrow="$welcome['today']" :title="$welcome['greeting']" :fuel-series="$welcome['fuelSeries']" :fuel-href="route('home').'#fuel-title'"
            :stats="[
                ['Listings to explore', $welcome['listings'], 0, 'Approved by an administrator'],
                ['Regions covered', $welcome['regions'], 0, 'of '.$welcome['regionsTotal'].' regions'],
                ['Your reviews', $welcome['myReviews'], 0, $welcome['myReviews'] ? 'Thank you for helping other drivers' : 'Visited a place? Rate it'],
            ]">
            Find a gas station, car wash, repair shop, tire shop, parking or battery service.
            <a href="{{ route('places.index') }}" class="font-semibold text-white underline underline-offset-2 hover:text-accent">Browse by city and region</a>
        </x-dash.hero>
    </div>
@else
    <section class="border-b border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <h1 class="text-5xl">Find services</h1>
            <p class="mt-1.5 text-muted">Only listings approved by an administrator appear here. <a href="{{ route('places.index') }}" class="font-semibold text-steel underline underline-offset-2">Browse by city and region</a></p>
        </div>
    </section>
@endif

<form id="filters" action="{{ route('establishments.index') }}" method="GET" data-autosubmit role="search"
      class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[17.5rem_minmax(0,1fr)]">

    {{-- Filters --}}
    <aside>
        <button type="button" data-toggle="#filter-panel" aria-expanded="false" aria-controls="filter-panel"
                class="btn-outline w-full justify-between lg:hidden">
            <span class="inline-flex items-center gap-2"><x-ui.icon name="filter" class="size-4" /> Filters</span>
            @if ($filterCount)<span class="rounded-full bg-accent px-2 text-xs font-bold text-navy-950">{{ $filterCount }}</span>@endif
        </button>

        <div id="filter-panel" class="mt-3 hidden space-y-6 rounded-lg border border-line bg-white p-5 lg:sticky lg:top-24 lg:mt-0 lg:block">
            <div>
                <label for="q" class="label">Name</label>
                <div class="relative">
                    <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted" />
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="e.g. car wash" class="input pl-9">
                </div>
            </div>

            <fieldset>
                <legend class="label">Category</legend>
                <div class="-mx-2 space-y-0.5">
                    <label class="flex cursor-pointer items-center gap-3 rounded-sign px-2.5 py-2 text-[0.9375rem] hover:bg-page has-[:checked]:bg-steel-50 has-[:checked]:font-semibold has-[:checked]:text-navy has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-steel">
                        <input type="radio" name="category" value="" class="sr-only" @checked(blank($filters['category'] ?? null))>
                        <span class="flex size-7 items-center justify-center rounded-sign bg-page text-steel"><x-ui.icon name="list" class="size-4" /></span>
                        <span class="flex-1">All categories</span>
                    </label>
                    @foreach ($categories as $category)
                        <label class="flex cursor-pointer items-center gap-3 rounded-sign px-2.5 py-2 text-[0.9375rem] hover:bg-page has-[:checked]:bg-steel-50 has-[:checked]:font-semibold has-[:checked]:text-navy has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-steel">
                            <input type="radio" name="category" value="{{ $category->slug }}" class="sr-only" @checked(($filters['category'] ?? null) === $category->slug)>
                            <span class="flex size-7 items-center justify-center rounded-sign bg-page text-steel"><x-ui.icon :name="$category->icon()" class="size-4" /></span>
                            <span class="flex-1">{{ $category->name }}</span>
                            <span class="text-sm text-muted">{{ $category->establishments_count }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            {{-- Place: each choice reloads the page with the next list filled in --}}
            <fieldset class="space-y-3">
                <legend class="label">Place</legend>
                <div>
                    <label for="region" class="sr-only">Region</label>
                    <select id="region" name="region" class="input" data-resets="province,city">
                        <option value="">Anywhere in the Philippines</option>
                        @foreach ($location['regions'] as $r)
                            <option value="{{ $r->id }}" @selected($regionId === $r->id)>{{ $r->label() }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($showProvince)
                    <div>
                        <label for="province" class="sr-only">Province</label>
                        <select id="province" name="province" class="input" data-resets="city">
                            <option value="">Any province</option>
                            @foreach ($location['provinces'] as $p)
                                <option value="{{ $p->id }}" @selected($provinceId === $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($showCity)
                    <div>
                        <label for="city" class="sr-only">City or municipality</label>
                        <select id="city" name="city" class="input">
                            <option value="">Any city or municipality</option>
                            @foreach ($location['cities'] as $c)
                                <option value="{{ $c->id }}" @selected($cityId === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </fieldset>

            {{-- GPS: sort by distance from the visitor --}}
            <div>
                <input type="hidden" name="lat" value="{{ $filters['lat'] ?? '' }}" @disabled(! $hasPosition)>
                <input type="hidden" name="lng" value="{{ $filters['lng'] ?? '' }}" @disabled(! $hasPosition)>
                <button type="button" data-near-me data-no-loading class="btn-outline w-full"><x-ui.icon name="crosshair" class="size-4" /> Near me</button>
                <p data-geo-message class="hint" role="status"></p>
            </div>

            <button type="submit" class="btn-navy w-full">Apply filters</button>
        </div>
    </aside>

    {{-- Results --}}
    <section aria-live="polite">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-[0.9375rem] text-muted">
                <strong class="text-lg font-semibold text-navy">{{ $establishments->total() }}</strong>
                {{ Str::plural('listing', $establishments->total()) }} found
            </p>
            <div class="flex items-center gap-2">
                <label for="sort" class="text-sm text-muted">Sort by</label>
                <select id="sort" name="sort" class="input !w-auto py-1.5">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                    @if ($hasPosition)<option value="nearest" @selected(($filters['sort'] ?? '') === 'nearest')>Nearest to me</option>@endif
                    <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Highest rated</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name, A to Z</option>
                </select>
            </div>
        </div>

        @if ($chips->isNotEmpty())
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach ($chips as $chip)
                    <li>
                        <a href="{{ route('establishments.index', Arr::except(request()->query(), [...$chip['remove'], 'page'])) }}"
                           class="inline-flex items-center gap-1.5 rounded-full border border-line-strong bg-white py-1 pr-2 pl-3 text-sm font-medium text-navy hover:border-navy"
                           aria-label="Remove filter {{ $chip['label'] }}">
                            {{ $chip['label'] }} <x-ui.icon name="x" class="size-3.5 text-muted" />
                        </a>
                    </li>
                @endforeach
                <li><a href="{{ route('establishments.index') }}" class="inline-block py-1 text-sm font-semibold text-steel underline underline-offset-2">Clear all</a></li>
            </ul>
        @endif

        @if ($establishments->isEmpty())
            <x-ui.empty-state class="mt-5" icon="search" title="No establishments found" text="Nothing matches those filters yet. Try a shorter name, a wider place, or remove a filter.">
                <a href="{{ route('establishments.index') }}" class="btn-navy">Clear all filters</a>
            </x-ui.empty-state>
        @else
            <div class="mt-5 space-y-4">
                @foreach ($establishments as $establishment)
                    @if ($welcome)
                        <x-listing.card :establishment="$establishment" class="dash-rise" style="--i: {{ $loop->index + 4 }}" />
                    @else
                        <x-listing.card :establishment="$establishment" />
                    @endif
                @endforeach
            </div>
            <div class="mt-8">{{ $establishments->links() }}</div>
        @endif
    </section>
</form>
@endsection
