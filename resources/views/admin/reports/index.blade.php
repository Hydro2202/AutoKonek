@extends('layouts.panel')

@section('title', 'Reports')
@section('heading', 'Reported content')
@section('subheading', 'Review reports and mark them resolved or dismissed.')

@section('content')
<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-3">
    <div>
        <label for="status" class="label">Status</label>
        <select id="status" name="status" class="input">
            <option value="">Any</option>
            @foreach (['pending', 'resolved', 'dismissed'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
    </div>
    <div>
        <label for="type" class="label">Type</label>
        <select id="type" name="type" class="input">
            <option value="">Any</option>
            <option value="establishment" @selected(($filters['type'] ?? '') === 'establishment')>Listing</option>
            <option value="review" @selected(($filters['type'] ?? '') === 'review')>Review</option>
        </select>
    </div>
    <div class="flex items-end gap-2"><button class="btn-navy" type="submit">Filter</button><a href="{{ route('admin.reports.index') }}" class="btn-outline">Clear</a></div>
</form>

@if ($reports->isEmpty())
    <x-empty-state icon="flag" title="No reports found" />
@else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Reported item</th><th>Reason</th><th>Reported by</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($reports as $r)
                    <tr>
                        <td class="max-w-xs">
                            @if ($r->establishment)
                                <span class="text-xs text-muted">Listing</span><br>
                                <a href="{{ route('admin.establishments.show', $r->establishment) }}" class="font-semibold text-steel hover:underline">{{ $r->establishment->name }}</a>
                            @elseif ($r->review)
                                <span class="text-xs text-muted">Review by {{ $r->review->user->name }} at {{ $r->review->establishment->name }}</span>
                                <p class="line-clamp-3">{{ $r->review->comment }}</p>
                            @endif
                        </td>
                        <td><p class="font-semibold">{{ $r->reasonLabel() }}</p>@if ($r->description)<p class="text-xs text-muted">{{ $r->description }}</p>@endif</td>
                        <td>{{ $r->reporter->name }}<p class="text-xs text-muted">{{ $r->created_at->format('M j, Y g:i A') }}</p></td>
                        <td>
                            <x-badge :status="$r->status" />
                            @if ($r->resolved_at)<p class="mt-1 text-xs text-muted">{{ $r->resolver?->name }}, {{ $r->resolved_at->format('M j') }}</p>@endif
                        </td>
                        <td>
                            @if ($r->status === 'pending')
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.reports.update', $r) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="resolved"><button type="submit" class="btn-navy btn-sm">Resolve</button></form>
                                    <form method="POST" action="{{ route('admin.reports.update', $r) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="dismissed"><button type="submit" class="btn-outline btn-sm">Dismiss</button></form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $reports->links() }}</div>
@endif
@endsection
