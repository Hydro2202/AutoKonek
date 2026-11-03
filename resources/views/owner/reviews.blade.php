@extends('layouts.panel')

@section('title', 'Reviews')
@section('heading', 'Customer reviews')
@section('subheading', 'Reviews for your establishments.')

@section('content')
<form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
    <div>
        <label for="establishment" class="label">Establishment</label>
        <select id="establishment" name="establishment" class="input min-w-56" onchange="this.form.submit()">
            <option value="">All establishments</option>
            @foreach ($establishments as $e)
                <option value="{{ $e->id }}" @selected((string) request('establishment') === (string) $e->id)>{{ $e->name }}</option>
            @endforeach
        </select>
    </div>
    <noscript><button class="btn-navy">Filter</button></noscript>
</form>

@forelse ($reviews as $review)
    <article class="card mb-3 p-5">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="text-sm font-semibold">{{ $review->user->name }}</p>
                <p class="text-xs text-muted">{{ $review->establishment->name }} &middot; {{ $review->created_at->format('M j, Y') }}</p>
            </div>
            <x-stars :rating="$review->rating" />
        </div>
        <p class="mt-2 whitespace-pre-line text-sm">{{ $review->comment }}</p>
    </article>
@empty
    <x-empty-state icon="message" title="No reviews yet" text="Reviews from customers will show up here." />
@endforelse
<div class="mt-6">{{ $reviews->links() }}</div>
@endsection
