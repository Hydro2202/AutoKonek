@extends('layouts.app')

@section('title', 'My reviews')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl">My reviews</h1>

    <div class="mt-5 space-y-4">
        @forelse ($reviews as $review)
            <article class="card p-5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <a href="{{ route('establishments.show', $review->establishment) }}" class="font-semibold text-navy hover:underline">{{ $review->establishment->name }}</a>
                        <p class="text-xs text-muted">{{ $review->created_at->format('M j, Y') }}</p>
                    </div>
                    <x-stars :rating="$review->rating" />
                </div>
                <p class="mt-2 whitespace-pre-line text-sm">{{ $review->comment }}</p>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('establishments.show', $review->establishment) }}#reviews" class="btn-outline btn-sm"><x-icon name="pencil" class="size-3.5" /> Edit</a>
                    <form method="POST" action="{{ route('reviews.destroy', $review) }}" data-confirm="Delete this review? This cannot be undone.">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-outline btn-sm text-danger"><x-icon name="trash" class="size-3.5" /> Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <x-empty-state icon="star" title="You have not written any reviews" text="Visit an establishment's page to share your experience.">
                <a href="{{ route('establishments.index') }}" class="btn-navy">Find services</a>
            </x-empty-state>
        @endforelse
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
</div>
@endsection
