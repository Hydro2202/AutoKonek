@extends('layouts.owner')

@section('title', 'Dashboard')
@section('heading', 'Hello, '.Str::before(auth()->user()->name, ' '))
@section('subheading', 'Here is how your listings are doing.')
@section('content')
{{-- Stat strip: one bordered block, divided into cells --}}
<dl class="grid grid-cols-2 divide-x divide-y divide-line overflow-hidden rounded-lg border border-line bg-white lg:grid-cols-4 lg:divide-y-0">
    @foreach ([
        ['Live', $stats['approved'], 'Visible to drivers'],
        ['In review', $stats['pending'], 'Waiting for an administrator'],
        ['Reviews', $stats['reviews'], 'Across all listings'],
    ] as [$label, $value, $note])
        <div class="p-5">
            <dt class="text-sm font-semibold text-muted">{{ $label }}</dt>
            <dd class="mt-1 font-display text-5xl leading-none font-semibold text-navy" data-count-up="{{ $value }}">{{ $value }}</dd>
            <dd class="mt-1.5 text-sm text-muted">{{ $note }}</dd>
        </div>
    @endforeach
    <div class="p-5">
        <dt class="text-sm font-semibold text-muted">Average rating</dt>
        <dd class="mt-2"><x-ui.plate :rating="$stats['average']" :count="$stats['reviews']" class="!text-3xl" /></dd>
        <dd class="mt-1.5 text-sm text-muted">{{ $stats['reviews'] ? 'Out of 5' : 'No reviews yet' }}</dd>
    </div>
</dl>

@foreach ($rejected as $item)
    <div class="mt-6 flex flex-wrap items-start justify-between gap-4 rounded-lg border border-danger/30 bg-danger-50 p-5" role="alert">
        <div class="min-w-0 flex-1">
            <p class="font-display text-2xl font-semibold text-danger-700">"{{ $item->name }}" needs changes</p>
            <p class="mt-1 text-[0.9375rem] text-ink/85"><span class="font-semibold">Administrator's note:</span> {{ $item->rejection_reason }}</p>
        </div>
        <a href="{{ route('owner.establishments.edit', $item) }}" class="btn-danger">Fix and resubmit</a>
    </div>
@endforeach

<div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <section>
        <h2 class="mb-3 text-3xl">Your establishments</h2>
        @if ($establishments->isEmpty())
            <x-ui.empty-state icon="building" title="No establishments yet" text="Submit your first listing. An administrator checks it before it appears to drivers.">
                <a href="{{ route('owner.establishments.create') }}" class="btn-primary">Add establishment</a>
            </x-ui.empty-state>
        @else
            <ul class="space-y-3">
                @foreach ($establishments as $e)
                    <li class="anim-rise flex flex-wrap items-center gap-x-5 gap-y-3 rounded-lg border border-line bg-white p-4" style="--i: {{ $loop->index }}">
                        <img src="{{ $e->imageUrl() }}" alt="" class="size-16 shrink-0 rounded-md object-cover">
                        <div class="min-w-0 flex-1 basis-48">
                            <p class="truncate font-display text-2xl font-semibold text-navy">{{ $e->name }}</p>
                            <p class="text-sm text-muted">{{ $e->category->name }}, {{ $e->reviews_count }} {{ Str::plural('review', $e->reviews_count) }}</p>
                        </div>
                        <x-listing.status-track :status="$e->status" class="basis-56" />
                        <a href="{{ route('owner.establishments.edit', $e) }}" class="btn-outline btn-sm">Manage</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section>
        <h2 class="mb-3 text-3xl">Latest reviews</h2>
        @forelse ($latestReviews as $review)
            <article class="mb-3 rounded-lg border border-line bg-white p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="truncate font-semibold text-navy">{{ $review->user->name }}</p>
                    <x-ui.stars :rating="$review->rating" />
                </div>
                <p class="text-sm text-muted">{{ $review->establishment->name }}</p>
                <p class="mt-1.5 line-clamp-3 text-[0.9375rem] text-ink/90">{{ $review->comment }}</p>
            </article>
        @empty
            <x-ui.empty-state icon="message" title="No reviews yet" text="Reviews appear here once your listings are live." />
        @endforelse
        @if ($latestReviews->isNotEmpty())
            <a href="{{ route('owner.reviews') }}" class="text-sm font-semibold text-steel underline underline-offset-2">See all reviews</a>
        @endif
    </section>
</div>
@endsection
