@extends('layouts.admin')

@section('title', 'Reports')
@section('heading', 'Reports')
@section('subheading', 'Resolve reports once you have acted, or dismiss the ones that do not need action.')

@section('content')
<x-panel.card :title="$reports->total().' '.Str::plural('report', $reports->total())" :flush="true">
    <x-slot:actions>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="input !w-36 py-1.5 text-sm">
                <option value="">Any status</option>
                @foreach (['pending', 'resolved', 'dismissed'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
            <label for="type" class="sr-only">Type</label>
            <select id="type" name="type" class="input !w-36 py-1.5 text-sm">
                <option value="">Any type</option>
                <option value="establishment" @selected(($filters['type'] ?? '') === 'establishment')>Listing</option>
                <option value="review" @selected(($filters['type'] ?? '') === 'review')>Review</option>
            </select>
            <button class="btn-navy btn-sm" type="submit">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.reports.index') }}" class="btn-ghost btn-sm">Reset</a>@endif
        </form>
    </x-slot:actions>

    @if ($reports->isEmpty())
        <p class="px-5 py-14 text-center text-sm text-muted">No reports found.</p>
    @else
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Reported item</th><th>Reason</th><th>Reported by</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($reports as $r)
                        <tr>
                            <td class="max-w-xs">
                                @if ($r->establishment)
                                    <p class="text-xs text-muted">Listing</p>
                                    <a href="{{ route('admin.establishments.show', $r->establishment) }}" class="font-medium text-navy hover:underline">{{ $r->establishment->name }}</a>
                                @elseif ($r->review)
                                    <p class="text-xs text-muted">Review by {{ $r->review->user->name }} at {{ $r->review->establishment->name }}</p>
                                    <p class="line-clamp-2">{{ $r->review->comment }}</p>
                                @endif
                            </td>
                            <td class="max-w-xs"><p class="font-medium">{{ $r->reasonLabel() }}</p>@if ($r->description)<p class="line-clamp-2 text-xs text-muted">{{ $r->description }}</p>@endif</td>
                            <td>{{ $r->reporter->name }}<p class="text-xs text-muted">{{ $r->created_at->format('M j, Y g:i A') }}</p></td>
                            <td>
                                <x-ui.status :status="$r->status" />
                                @if ($r->resolved_at)<p class="mt-1 text-xs text-muted">{{ $r->resolver?->name }}, {{ $r->resolved_at->format('M j') }}</p>@endif
                            </td>
                            <td>
                                @if ($r->status === 'pending')
                                    <div class="flex justify-end gap-1.5">
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
        @if ($reports->hasPages())<div class="border-t border-line px-5 py-3">{{ $reports->links() }}</div>@endif
    @endif
</x-panel.card>
@endsection
