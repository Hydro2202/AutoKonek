@extends('layouts.site')

@section('title', 'My reports')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
    <h1 class="text-5xl">My reports</h1>
    <p class="mt-1.5 text-muted">Listings and reviews you have flagged for the moderators.</p>

    <div class="mt-8">
        @if ($reports->isEmpty())
            <x-ui.empty-state icon="flag" title="No reports yet" text="If a listing or review looks wrong, use its Report button and it will show up here." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Reported item</th><th>Reason</th><th>Sent</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>
                                    @if ($report->establishment)
                                        <span class="text-xs text-muted">Listing</span>
                                        <p class="font-semibold text-navy">{{ $report->establishment->name }}</p>
                                    @elseif ($report->review)
                                        <span class="text-xs text-muted">Review at</span>
                                        <p class="font-semibold text-navy">{{ $report->review->establishment->name }}</p>
                                    @endif
                                </td>
                                <td>{{ $report->reasonLabel() }}</td>
                                <td class="whitespace-nowrap text-muted">{{ $report->created_at->format('M j, Y g:i A') }}</td>
                                <td><x-ui.status :status="$report->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $reports->links() }}</div>
        @endif
    </div>
</div>
@endsection
