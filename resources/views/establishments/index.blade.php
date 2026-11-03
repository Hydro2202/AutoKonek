@extends('layouts.app')

@section('title', 'Find Services')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <h1 class="text-2xl sm:text-3xl">Find automotive services</h1>
    <p class="mt-1 text-sm text-muted">Only approved establishments are listed.</p>

    <form action="{{ route('establishments.index') }}" method="GET" class="card mt-5 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5" role="search">
        <div class="lg:col-span-2">
            <label for="q" class="label">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Establishment name" class="input">
        </div>
        <div>
            <label for="category" class="label">Category</label>
            <select id="category" name="category" class="input">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="city" class="label">City / area</label>
            <select id="city" name="city" class="input">
                <option value="">All locations</option>
                @foreach ($cities as $city)
                    <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sort" class="label">Sort by</label>
            <select id="sort" name="sort" class="input">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name (A-Z)</option>
                <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Highest rated</option>
            </select>
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
            <button type="submit" class="btn-primary"><x-icon name="search" class="size-4" /> Apply filters</button>
            @if (array_filter($filters))
                <a href="{{ route('establishments.index') }}" class="btn-outline">Clear</a>
            @endif
        </div>
    </form>

    <p class="mt-6 text-sm text-muted">
        Showing {{ $establishments->firstItem() ?? 0 }}-{{ $establishments->lastItem() ?? 0 }} of {{ $establishments->total() }} {{ Str::plural('result', $establishments->total()) }}
    </p>

    @if ($establishments->isEmpty())
        <x-empty-state class="mt-3" icon="search" title="No establishments found" text="Try a different name, or remove some filters.">
            <a href="{{ route('establishments.index') }}" class="btn-navy">Clear all filters</a>
        </x-empty-state>
    @else
        <div class="mt-3 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($establishments as $establishment)
                <x-establishment-card :establishment="$establishment" />
            @endforeach
        </div>
        <div class="mt-8">{{ $establishments->links() }}</div>
    @endif
</div>
@endsection
