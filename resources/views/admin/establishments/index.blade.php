@extends('layouts.admin')

@section('title', $title)
@section('heading', $title)
@section('subheading', $route === 'admin.approvals' ? 'New and changed listings waiting for a decision.' : 'Every establishment on the platform.')

@php
    $statusTabs = [
        '' => ['All', $statusCounts->sum()],
        'pending' => ['Pending', $statusCounts['pending'] ?? 0],
        'approved' => ['Approved', $statusCounts['approved'] ?? 0],
        'rejected' => ['Rejected', $statusCounts['rejected'] ?? 0],
    ];
    $currentStatus = $filters['status'] ?? '';
    $showTabs = $route === 'admin.establishments.index';
@endphp

@section('content')
<x-panel.card :title="$establishments->total().' '.Str::plural('establishment', $establishments->total())" :flush="true">
    <x-slot:actions>
        <form method="GET" action="{{ route($route) }}" class="flex flex-wrap items-center gap-2">
            @if ($showTabs && $currentStatus)<input type="hidden" name="status" value="{{ $currentStatus }}">@endif
            <label for="q" class="sr-only">Search</label>
            <div class="relative">
                <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted" />
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name or address" class="input !w-56 py-1.5 pl-8 text-sm">
            </div>
            @if ($showTabs)
                <label for="category" class="sr-only">Category</label>
                <select id="category" name="category" class="input !w-44 py-1.5 text-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((string) ($filters['category'] ?? '') === (string) $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            @endif
            <button class="btn-navy btn-sm" type="submit">Search</button>
            @if (array_filter(Arr::except($filters, $showTabs ? [] : ['status'])))<a href="{{ route($route) }}" class="btn-ghost btn-sm">Reset</a>@endif
        </form>
    </x-slot:actions>

    @if ($showTabs)
        <nav class="flex gap-1 border-b border-line px-3" aria-label="Filter by status">
            @foreach ($statusTabs as $key => [$label, $number])
                <a href="{{ route($route, array_filter(['status' => $key, 'q' => $filters['q'] ?? null, 'category' => $filters['category'] ?? null])) }}"
                   @if ($currentStatus === (string) $key) aria-current="page" @endif
                   class="-mb-px border-b-2 px-3 py-2.5 text-sm font-medium {{ $currentStatus === (string) $key ? 'border-navy text-navy' : 'border-transparent text-muted hover:text-navy' }}">
                    {{ $label }} <span class="ml-1 rounded-full bg-page px-1.5 py-px text-xs text-muted">{{ $number }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    @if ($establishments->isEmpty())
        <p class="px-5 py-14 text-center text-sm text-muted">{{ $route === 'admin.approvals' ? 'No submissions are waiting for approval.' : 'No establishments match these filters.' }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Establishment</th><th>Owner</th><th>Category</th><th>Status</th><th>Submitted</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($establishments as $e)
                        <tr>
                            <td>
                                <a href="{{ route('admin.establishments.show', $e) }}" class="font-medium text-navy hover:underline">{{ $e->name }}</a>
                                <p class="text-xs text-muted">{{ $e->locationLabel() }}</p>
                            </td>
                            <td>{{ $e->owner->name }}</td>
                            <td>{{ $e->category->name }}</td>
                            <td><x-ui.status :status="$e->status" /></td>
                            <td class="whitespace-nowrap text-muted">{{ $e->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.establishments.show', $e) }}" class="btn-icon" title="{{ $e->status === 'pending' ? 'Review' : 'View' }}" aria-label="{{ $e->status === 'pending' ? 'Review' : 'View' }} {{ $e->name }}"><x-ui.icon name="eye" class="size-4" /></a>
                                    @if ($e->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.establishments.approve', $e) }}">@csrf @method('PATCH')
                                            <button type="submit" class="btn-icon text-success-700" title="Approve" aria-label="Approve {{ $e->name }}"><x-ui.icon name="check" class="size-4" /></button>
                                        </form>
                                    @else
                                        <span class="size-8" aria-hidden="true"></span>
                                    @endif
                                    <form method="POST" action="{{ route('admin.establishments.destroy', $e) }}" data-confirm="Permanently delete &quot;{{ $e->name }}&quot; with its services, images and reviews?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon text-danger-700" title="Delete" aria-label="Delete {{ $e->name }}"><x-ui.icon name="trash" class="size-4" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($establishments->hasPages())<div class="border-t border-line px-5 py-3">{{ $establishments->links() }}</div>@endif
    @endif
</x-panel.card>
@endsection
