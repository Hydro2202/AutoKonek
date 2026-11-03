@extends('layouts.panel')

@section('title', 'Owner dashboard')
@section('heading', 'Welcome, '.auth()->user()->name)
@section('subheading', 'Manage your establishments and see what customers say.')
@section('actions')
    <a href="{{ route('owner.establishments.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Add establishment</a>
@endsection

@section('content')
<div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
    @foreach ([
        ['Establishments', $stats['total'], 'building'],
        ['Approved', $stats['approved'], 'check'],
        ['Pending', $stats['pending'], 'clock'],
        ['Total reviews', $stats['reviews'], 'message'],
        ['Average rating', $stats['reviews'] ? number_format($stats['average'], 1) : '-', 'star'],
    ] as [$label, $value, $icon])
        <div class="card p-4">
            <div class="flex items-center justify-between text-muted"><span class="text-xs font-semibold uppercase tracking-wide">{{ $label }}</span><x-icon :name="$icon" class="size-4" /></div>
            <p class="mt-2 text-2xl font-bold text-navy">{{ $value }}</p>
        </div>
    @endforeach
</div>

@foreach ($rejected as $item)
    <div class="mt-6 flex flex-wrap items-start justify-between gap-3 rounded-md border border-danger/30 bg-red-50 p-4 text-sm text-red-900" role="alert">
        <div>
            <p class="font-semibold">"{{ $item->name }}" was rejected</p>
            <p class="mt-1">Reason: {{ $item->rejection_reason }}</p>
        </div>
        <a href="{{ route('owner.establishments.edit', $item) }}" class="btn-danger btn-sm">Fix and resubmit</a>
    </div>
@endforeach

<div class="mt-8 grid gap-6 lg:grid-cols-5">
    <section class="lg:col-span-3">
        <h2 class="mb-3 text-lg">Your establishments</h2>
        @if ($establishments->isEmpty())
            <x-empty-state icon="building" title="No establishments yet" text="Submit your first establishment. An administrator will review it before it appears publicly.">
                <a href="{{ route('owner.establishments.create') }}" class="btn-primary">Add establishment</a>
            </x-empty-state>
        @else
            <div class="card divide-y divide-line">
                @foreach ($establishments as $e)
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-navy">{{ $e->name }}</p>
                            <p class="text-xs text-muted">{{ $e->category->name }} &middot; {{ $e->reviews_count }} {{ Str::plural('review', $e->reviews_count) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-badge :status="$e->status" />
                            <a href="{{ route('owner.establishments.edit', $e) }}" class="btn-outline btn-sm">Manage</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="lg:col-span-2">
        <h2 class="mb-3 text-lg">Latest reviews</h2>
        @forelse ($latestReviews as $review)
            <article class="card mb-3 p-4 text-sm">
                <div class="flex items-center justify-between gap-2">
                    <p class="truncate font-semibold">{{ $review->user->name }}</p>
                    <x-stars :rating="$review->rating" />
                </div>
                <p class="text-xs text-muted">{{ $review->establishment->name }}</p>
                <p class="mt-1 line-clamp-3">{{ $review->comment }}</p>
            </article>
        @empty
            <x-empty-state icon="message" title="No reviews yet" text="Customer reviews appear here once your listings are approved." />
        @endforelse
    </section>
</div>
@endsection
