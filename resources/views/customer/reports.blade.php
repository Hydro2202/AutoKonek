@extends('layouts.app')

@section('title', 'My reports')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl">My reports</h1>

    <div class="mt-5">
        @if ($reports->isEmpty())
            <x-empty-state icon="flag" title="You have not submitted any reports" text="You can report a listing or review from its page." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Target</th><th>Reason</th><th>Submitted</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>
                                    @if ($report->establishment)
                                        <span class="text-xs text-muted">Listing</span><br>{{ $report->establishment->name }}
                                    @elseif ($report->review)
                                        <span class="text-xs text-muted">Review at</span><br>{{ $report->review->establishment->name }}
                                    @endif
                                </td>
                                <td>{{ $report->reasonLabel() }}</td>
                                <td class="whitespace-nowrap">{{ $report->created_at->format('M j, Y g:i A') }}</td>
                                <td><x-badge :status="$report->status" /></td>
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
