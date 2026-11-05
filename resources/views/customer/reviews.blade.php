@extends('layouts.site')

@section('title', 'My reviews')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
    <h1 class="text-5xl">My reviews</h1>
    <p class="mt-1.5 text-muted">Everything you have written about places on AutoKonek.</p>

    <div class="mt-8 space-y-4">
        @forelse ($reviews as $review)
            <article class="card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <a href="{{ route('establishments.show', $review->establishment) }}" class="font-display text-2xl font-semibold text-navy hover:text-steel">{{ $review->establishment->name }}</a>
                        <p class="text-sm text-muted">{{ $review->created_at->format('F j, Y') }}</p>
                    </div>
                    <x-ui.stars :rating="$review->rating" size="size-5" />
                </div>
                <p class="mt-3 max-w-2xl leading-relaxed whitespace-pre-line">{{ $review->comment }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('establishments.show', $review->establishment) }}#reviews" class="btn-outline btn-sm"><x-ui.icon name="pencil" class="size-3.5" /> Edit</a>
                    <form method="POST" action="{{ route('reviews.destroy', $review) }}" data-confirm="Delete this review? This cannot be undone.">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger-outline btn-sm"><x-ui.icon name="trash" class="size-3.5" /> Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <x-ui.empty-state icon="star" title="You have not written a review yet" text="Open a listing you have visited and tell other drivers what it was like.">
                <a href="{{ route('establishments.index') }}" class="btn-navy">Find services</a>
            </x-ui.empty-state>
        @endforelse
    </div>
    <div class="mt-8">{{ $reviews->links() }}</div>
</div>
@endsection
