@extends('layouts.panel')

@section('title', $title)
@section('heading', $title)
@section('subheading', $route === 'admin.approvals' ? 'New and changed listings waiting for a decision.' : 'All establishment listings on the platform.')

@section('content')
<form method="GET" action="{{ route($route) }}" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="lg:col-span-2"><label for="q" class="label">Search</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, description or address" class="input"></div>
    @if ($route === 'admin.establishments.index')
        <div>
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="input">
                <option value="">Any</option>
                @foreach (['pending', 'approved', 'rejected'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="category" class="label">Category</label>
            <select id="category" name="category" class="input">
                <option value="">All</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((string) ($filters['category'] ?? '') === (string) $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
    @endif
    <div class="flex gap-2 sm:col-span-2 lg:col-span-4">
        <button class="btn-navy" type="submit">Filter</button>
        <a href="{{ route($route) }}" class="btn-outline">Clear</a>
    </div>
</form>

@if ($establishments->isEmpty())
    <x-empty-state icon="building" title="Nothing to show" :text="$route === 'admin.approvals' ? 'There are no pending submissions.' : 'No establishments match your filters.'" />
@else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Establishment</th><th>Owner</th><th>Category</th><th>Status</th><th>Submitted</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($establishments as $e)
                    <tr>
                        <td><p class="font-semibold text-navy">{{ $e->name }}</p><p class="text-xs text-muted">{{ $e->locationLabel() }}</p></td>
                        <td>{{ $e->owner->name }}</td>
                        <td>{{ $e->category->name }}</td>
                        <td><x-badge :status="$e->status" /></td>
                        <td class="whitespace-nowrap">{{ $e->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('admin.establishments.show', $e) }}" class="btn-outline btn-sm">{{ $e->status === 'pending' ? 'Review' : 'View' }}</a>
                                @if ($e->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.establishments.approve', $e) }}">@csrf @method('PATCH')<button type="submit" class="btn-navy btn-sm">Approve</button></form>
                                @endif
                                <form method="POST" action="{{ route('admin.establishments.destroy', $e) }}" data-confirm="Permanently delete &quot;{{ $e->name }}&quot; with its services, images and reviews?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-outline btn-sm text-danger" aria-label="Delete {{ $e->name }}"><x-icon name="trash" class="size-3.5" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $establishments->links() }}</div>
@endif
@endsection
