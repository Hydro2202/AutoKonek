@extends('layouts.panel')

@section('title', 'My establishments')
@section('heading', 'My establishments')
@section('actions')
    <a href="{{ route('owner.establishments.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> Add establishment</a>
@endsection

@section('content')
@if ($establishments->isEmpty())
    <x-empty-state icon="building" title="No establishments yet" text="Submit your first establishment for approval.">
        <a href="{{ route('owner.establishments.create') }}" class="btn-primary">Add establishment</a>
    </x-empty-state>
@else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Establishment</th><th>Category</th><th>Status</th><th>Rating</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($establishments as $e)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $e->imageUrl() }}" alt="" class="size-12 rounded object-cover">
                                <div>
                                    <p class="font-semibold text-navy">{{ $e->name }}</p>
                                    <p class="text-xs text-muted">{{ $e->locationLabel() }}</p>
                                </div>
                            </div>
                            @if ($e->status === 'rejected')
                                <p class="mt-2 rounded bg-red-50 px-2 py-1 text-xs text-red-900"><strong>Rejected:</strong> {{ $e->rejection_reason }}</p>
                            @endif
                        </td>
                        <td>{{ $e->category->name }}</td>
                        <td><x-badge :status="$e->status" /></td>
                        <td><x-stars :rating="$e->averageRating()" :count="$e->reviewCount()" /></td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($e->status === 'rejected')
                                    <a href="{{ route('owner.establishments.edit', $e) }}" class="btn-primary btn-sm">Fix and resubmit</a>
                                @else
                                    <a href="{{ route('owner.establishments.edit', $e) }}" class="btn-outline btn-sm">Edit</a>
                                @endif
                                <a href="{{ route('owner.services.index', $e) }}" class="btn-outline btn-sm">Services</a>
                                <a href="{{ route('owner.images.index', $e) }}" class="btn-outline btn-sm">Images</a>
                                <a href="{{ route('establishments.show', $e) }}" class="btn-outline btn-sm">View</a>
                                <form method="POST" action="{{ route('owner.establishments.destroy', $e) }}" data-confirm="Delete &quot;{{ $e->name }}&quot; with all its services, images and reviews? This cannot be undone.">
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
