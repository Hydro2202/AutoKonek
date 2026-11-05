@extends('layouts.admin')

@section('title', 'Reviews')
@section('heading', 'Reviews')
@section('subheading', 'Remove reviews that are abusive, off-topic or fake.')

@section('content')
<x-panel.card :title="$reviews->total().' '.Str::plural('review', $reviews->total())" :flush="true">
    <x-slot:actions>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label for="q" class="sr-only">Search</label>
            <div class="relative">
                <x-ui.icon name="search" class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted" />
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Comment, customer or place" class="input !w-60 py-1.5 pl-8 text-sm">
            </div>
            <label for="rating" class="sr-only">Rating</label>
            <select id="rating" name="rating" class="input !w-32 py-1.5 text-sm">
                <option value="">Any rating</option>
                @for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected((string) ($filters['rating'] ?? '') === (string) $i)>{{ $i }} {{ Str::plural('star', $i) }}</option>@endfor
            </select>
            <button class="btn-navy btn-sm" type="submit">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.reviews.index') }}" class="btn-ghost btn-sm">Reset</a>@endif
        </form>
    </x-slot:actions>

    @if ($reviews->isEmpty())
        <p class="px-5 py-14 text-center text-sm text-muted">No reviews found.</p>
    @else
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Review</th><th>Establishment</th><th>Customer</th><th>Date</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    @foreach ($reviews as $r)
                        <tr>
                            <td class="max-w-sm"><x-ui.stars :rating="$r->rating" size="size-3.5" /><p class="mt-1 line-clamp-2">{{ $r->comment }}</p></td>
                            <td><a href="{{ route('establishments.show', $r->establishment) }}" class="text-steel hover:underline">{{ $r->establishment->name }}</a></td>
                            <td>{{ $r->user->name }}</td>
                            <td class="whitespace-nowrap text-muted">{{ $r->created_at->format('M j, Y') }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}" data-confirm="Remove this review permanently?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger-outline btn-sm"><x-ui.icon name="trash" class="size-3.5" /> Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())<div class="border-t border-line px-5 py-3">{{ $reviews->links() }}</div>@endif
    @endif
</x-panel.card>
@endsection
