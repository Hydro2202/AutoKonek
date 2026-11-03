@extends('layouts.panel')

@section('title', 'Admin dashboard')
@section('heading', 'Administrator dashboard')
@section('subheading', 'Platform overview. All figures come from the database.')

@section('content')
<div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
    @foreach ([
        ['Total users', $stats['users'], 'users', route('admin.users.index'), false],
        ['Business owners', $stats['owners'], 'building', route('admin.users.index', ['role' => 'business_owner']), false],
        ['Establishments', $stats['establishments'], 'tag', route('admin.establishments.index'), false],
        ['Pending approvals', $stats['pending'], 'clock', route('admin.approvals'), $stats['pending'] > 0],
        ['Total reviews', $stats['reviews'], 'star', route('admin.reviews.index'), false],
        ['Unresolved reports', $stats['reports'], 'flag', route('admin.reports.index', ['status' => 'pending']), $stats['reports'] > 0],
    ] as [$label, $value, $icon, $url, $highlight])
        <a href="{{ $url }}" class="card p-4 transition-colors hover:border-steel {{ $highlight ? 'border-accent' : '' }}">
            <div class="flex items-center justify-between text-muted"><span class="text-xs font-semibold uppercase tracking-wide">{{ $label }}</span><x-icon :name="$icon" class="size-4" /></div>
            <p class="mt-2 text-3xl font-bold text-navy">{{ $value }}</p>
        </a>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section>
        <div class="mb-3 flex items-center justify-between"><h2 class="text-lg">Pending submissions</h2><a href="{{ route('admin.approvals') }}" class="text-sm font-semibold text-steel hover:underline">View all</a></div>
        @forelse ($pending as $e)
            <a href="{{ route('admin.establishments.show', $e) }}" class="card mb-2 flex items-center justify-between gap-3 p-4 text-sm hover:border-steel">
                <span class="min-w-0"><span class="block truncate font-semibold text-navy">{{ $e->name }}</span><span class="text-xs text-muted">{{ $e->category->name }} &middot; by {{ $e->owner->name }}</span></span>
                <span class="btn-outline btn-sm shrink-0">Review</span>
            </a>
        @empty
            <x-empty-state icon="check" title="Nothing waiting" text="All submissions have been reviewed." />
        @endforelse
    </section>

    <section>
        <div class="mb-3 flex items-center justify-between"><h2 class="text-lg">Recent reports</h2><a href="{{ route('admin.reports.index') }}" class="text-sm font-semibold text-steel hover:underline">View all</a></div>
        @forelse ($recentReports as $r)
            <div class="card mb-2 flex items-center justify-between gap-3 p-4 text-sm">
                <span class="min-w-0"><span class="block truncate font-semibold">{{ $r->reasonLabel() }}</span><span class="text-xs text-muted">{{ $r->establishment?->name ?? 'Review' }} &middot; {{ $r->created_at->diffForHumans() }}</span></span>
                <x-badge :status="$r->status" />
            </div>
        @empty
            <x-empty-state icon="flag" title="No reports" text="Reports from users will appear here." />
        @endforelse
    </section>

    <section>
        <h2 class="mb-3 text-lg">Newest users</h2>
        <div class="card divide-y divide-line text-sm">
            @foreach ($recentUsers as $u)
                <div class="flex items-center justify-between gap-3 p-3">
                    <span class="min-w-0"><span class="block truncate font-semibold">{{ $u->name }}</span><span class="text-xs text-muted">{{ $u->created_at->diffForHumans() }}</span></span>
                    <x-badge :status="$u->role" />
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="mb-3 text-lg">Latest reviews</h2>
        <div class="card divide-y divide-line text-sm">
            @forelse ($recentReviews as $r)
                <div class="p-3">
                    <div class="flex items-center justify-between gap-2"><span class="truncate font-semibold">{{ $r->establishment->name }}</span><x-stars :rating="$r->rating" /></div>
                    <p class="truncate text-xs text-muted">{{ $r->user->name }}: {{ $r->comment }}</p>
                </div>
            @empty
                <p class="p-4 text-muted">No reviews yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
