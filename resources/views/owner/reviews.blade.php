@extends('layouts.owner')

@section('title', 'Customer reviews')
@section('heading', 'Customer reviews')
@section('subheading', 'What drivers say about your establishments.')

@section('content')
<form method="GET" class="mb-5 max-w-xs" data-autosubmit>
    <label for="establishment" class="label">Show reviews for</label>
    <select id="establishment" name="establishment" class="input">
        <option value="">All establishments</option>
        @foreach ($establishments as $e)
            <option value="{{ $e->id }}" @selected((string) request('establishment') === (string) $e->id)>{{ $e->name }}</option>
        @endforeach
    </select>
    <noscript><button class="btn-navy mt-2">Filter</button></noscript>
</form>

<div class="space-y-3">
    @forelse ($reviews as $review)
        <article class="rounded-lg border border-line bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-navy">{{ $review->user->name }}</p>
                    <p class="text-sm text-muted">{{ $review->establishment->name }}, {{ $review->created_at->format('F j, Y') }}</p>
                </div>
                <x-ui.stars :rating="$review->rating" size="size-5" />
            </div>
            <p class="mt-3 max-w-2xl leading-relaxed whitespace-pre-line">{{ $review->comment }}</p>
        </article>
    @empty
        <x-ui.empty-state icon="message" title="No reviews yet" text="Reviews from customers will show up here." />
    @endforelse
</div>
<div class="mt-8">{{ $reviews->links() }}</div>
@endsection
