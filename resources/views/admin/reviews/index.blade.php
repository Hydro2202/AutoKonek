@extends('layouts.panel')

@section('title', 'Review moderation')
@section('heading', 'Review moderation')
@section('subheading', 'Remove reviews that are inappropriate or abusive.')

@section('content')
<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-3">
    <div class="sm:col-span-2"><label for="q" class="label">Search</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Comment, customer or establishment" class="input"></div>
    <div>
        <label for="rating" class="label">Rating</label>
        <select id="rating" name="rating" class="input">
            <option value="">Any</option>
            @for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected((string) ($filters['rating'] ?? '') === (string) $i)>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>@endfor
        </select>
    </div>
    <div class="flex gap-2 sm:col-span-3"><button class="btn-navy" type="submit">Filter</button><a href="{{ route('admin.reviews.index') }}" class="btn-outline">Clear</a></div>
</form>

@if ($reviews->isEmpty())
    <x-empty-state icon="star" title="No reviews found" />
@else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Review</th><th>Establishment</th><th>Customer</th><th>Date</th><th class="text-right">Action</th></tr></thead>
            <tbody>
                @foreach ($reviews as $r)
                    <tr>
                        <td class="max-w-xs"><x-stars :rating="$r->rating" /><p class="mt-1 line-clamp-3">{{ $r->comment }}</p></td>
                        <td><a href="{{ route('establishments.show', $r->establishment) }}" class="text-steel hover:underline">{{ $r->establishment->name }}</a></td>
                        <td>{{ $r->user->name }}</td>
                        <td class="whitespace-nowrap">{{ $r->created_at->format('M j, Y') }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}" data-confirm="Remove this review permanently?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-outline btn-sm text-danger"><x-icon name="trash" class="size-3.5" /> Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
@endif
@endsection
