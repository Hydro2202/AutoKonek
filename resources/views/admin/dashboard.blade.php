@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Platform overview. Every figure is counted from the database.')

@php
    // 14-day activity chart (two series) drawn as SVG.
    $max = max(1, $activity->max(fn ($day) => max($day['users'], $day['reviews'])));
    $chartWidth = 640;
    $chartHeight = 170;
    $slot = $chartWidth / $activity->count();
    $barWidth = ($slot - 10) / 2;
    $usersTotal = $activity->sum('users');
    $reviewsTotal = $activity->sum('reviews');
    $ownerShare = $stats['users'] ? round($stats['owners'] / $stats['users'] * 100) : 0;
@endphp

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <x-panel.kpi label="Total users" :value="$stats['users']" :note="'+'.$newThisWeek['users'].' in the last 7 days'" icon="users" :href="route('admin.users.index')" />
    <x-panel.kpi label="Business owners" :value="$stats['owners']" :note="$ownerShare.'% of all users'" icon="building" :href="route('admin.users.index', ['role' => 'business_owner'])" />
    <x-panel.kpi label="Establishments" :value="$stats['establishments']" :note="'+'.$newThisWeek['establishments'].' in the last 7 days'" icon="tag" :href="route('admin.establishments.index')" />
    <x-panel.kpi label="Pending approvals" :value="$stats['pending']" :note="$stats['pending'] ? 'Waiting for a decision' : 'All caught up'" icon="circle-check" :href="route('admin.approvals')" :alert="$stats['pending'] > 0" />
    <x-panel.kpi label="Reviews" :value="$stats['reviews']" :note="'+'.$newThisWeek['reviews'].' in the last 7 days'" icon="star" :href="route('admin.reviews.index')" />
    <x-panel.kpi label="Unresolved reports" :value="$stats['reports']" :note="$stats['reports'] ? 'Needs moderation' : 'Nothing open'" icon="flag" :href="route('admin.reports.index', ['status' => 'pending'])" :alert="$stats['reports'] > 0" />
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <div class="space-y-6">
        <x-panel.card title="Activity, last 14 days" subtitle="New users and new reviews per day">
            <x-slot:actions>
                <div class="flex items-center gap-4 text-[0.8125rem] text-muted">
                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-steel"></span>Users ({{ $usersTotal }})</span>
                    <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-accent"></span>Reviews ({{ $reviewsTotal }})</span>
                </div>
            </x-slot:actions>

            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 24 }}" class="h-auto w-full" role="img"
                 aria-label="Bar chart of new users and new reviews per day for the last 14 days. {{ $usersTotal }} new users and {{ $reviewsTotal }} new reviews in total.">
                @foreach ([0, 0.5, 1] as $line)
                    <line x1="0" x2="{{ $chartWidth }}" y1="{{ $chartHeight - $line * $chartHeight + 0.5 }}" y2="{{ $chartHeight - $line * $chartHeight + 0.5 }}" stroke="var(--color-line)" />
                @endforeach
                <text x="0" y="10" font-size="11" fill="var(--color-muted)">{{ $max }}</text>

                @foreach ($activity as $i => $day)
                    @php
                        $x = $i * $slot + 5;
                        $usersHeight = $day['users'] / $max * ($chartHeight - 14);
                        $reviewsHeight = $day['reviews'] / $max * ($chartHeight - 14);
                    @endphp
                    <g>
                        <title>{{ $day['date']->format('M j') }}: {{ $day['users'] }} new {{ Str::plural('user', $day['users']) }}, {{ $day['reviews'] }} new {{ Str::plural('review', $day['reviews']) }}</title>
                        <rect class="chart-bar" style="--i: {{ $i }}" x="{{ $x }}" y="{{ $chartHeight - $usersHeight }}" width="{{ $barWidth }}" height="{{ max($usersHeight, $day['users'] ? 2 : 0) }}" rx="2" fill="var(--color-steel)" />
                        <rect class="chart-bar" style="--i: {{ $i }}" x="{{ $x + $barWidth + 2 }}" y="{{ $chartHeight - $reviewsHeight }}" width="{{ $barWidth }}" height="{{ max($reviewsHeight, $day['reviews'] ? 2 : 0) }}" rx="2" fill="var(--color-accent)" />
                    </g>
                    @if ($i % 2 === 0 || $loop->last)
                        <text x="{{ $x + $barWidth }}" y="{{ $chartHeight + 16 }}" font-size="11" text-anchor="middle" fill="var(--color-muted)">{{ $day['date']->format('M j') }}</text>
                    @endif
                @endforeach
            </svg>
        </x-panel.card>

        <x-panel.card title="Pending submissions" subtitle="Newest first" :flush="true">
            <x-slot:actions><a href="{{ route('admin.approvals') }}" class="text-sm font-medium text-steel hover:text-navy">View all</a></x-slot:actions>
            @if ($pending->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-muted">No submissions are waiting.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Establishment</th><th>Owner</th><th>Submitted</th><th class="text-right">Decision</th></tr></thead>
                        <tbody>
                            @foreach ($pending as $e)
                                <tr>
                                    <td><p class="font-medium text-navy">{{ $e->name }}</p><p class="text-xs text-muted">{{ $e->category->name }}</p></td>
                                    <td>{{ $e->owner->name }}</td>
                                    <td class="whitespace-nowrap text-muted">{{ $e->created_at->diffForHumans() }}</td>
                                    <td class="text-right"><a href="{{ route('admin.establishments.show', $e) }}" class="btn-outline btn-sm">Review</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-panel.card>
    </div>

    <div class="space-y-6">
        <x-panel.card title="Recent reports" :flush="true">
            <x-slot:actions><a href="{{ route('admin.reports.index') }}" class="text-sm font-medium text-steel hover:text-navy">View all</a></x-slot:actions>
            @forelse ($recentReports as $report)
                <div class="flex items-start justify-between gap-3 border-b border-line px-5 py-3 last:border-0">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ $report->reasonLabel() }}</p>
                        <p class="truncate text-xs text-muted">{{ $report->establishment?->name ?? 'A review' }}, {{ $report->created_at->diffForHumans() }}</p>
                    </div>
                    <x-ui.status :status="$report->status" />
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-muted">No reports yet.</p>
            @endforelse
        </x-panel.card>

        <x-panel.card title="Latest reviews" :flush="true">
            @forelse ($recentReviews as $review)
                <div class="border-b border-line px-5 py-3 last:border-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="truncate text-sm font-medium text-navy">{{ $review->establishment->name }}</p>
                        <x-ui.stars :rating="$review->rating" size="size-3.5" />
                    </div>
                    <p class="mt-0.5 truncate text-xs text-muted">{{ $review->user->name }}: {{ $review->comment }}</p>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-muted">No reviews yet.</p>
            @endforelse
        </x-panel.card>
    </div>
</div>
@endsection
