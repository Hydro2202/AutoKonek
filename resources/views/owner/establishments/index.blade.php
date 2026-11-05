@extends('layouts.owner')

@section('title', 'My establishments')
@section('heading', 'My establishments')
@section('subheading', 'Edit details, manage services and photos, and see where each listing is in review.')
@section('actions')
    <a href="{{ route('owner.establishments.create') }}" class="btn-primary"><x-ui.icon name="plus" class="size-4" /> Add establishment</a>
@endsection

@section('content')
@if ($establishments->isEmpty())
    <x-ui.empty-state icon="building" title="No establishments yet" text="Submit your first listing for approval.">
        <a href="{{ route('owner.establishments.create') }}" class="btn-primary">Add establishment</a>
    </x-ui.empty-state>
@else
    <ul class="space-y-4">
        @foreach ($establishments as $e)
            <li class="anim-rise rounded-lg border border-line bg-white p-5" style="--i: {{ $loop->index }}">
                <div class="flex flex-wrap items-start gap-x-5 gap-y-4">
                    <img src="{{ $e->imageUrl() }}" alt="" class="size-20 shrink-0 rounded-md object-cover">
                    <div class="min-w-0 flex-1 basis-56">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-3xl">{{ $e->name }}</h2>
                            <x-ui.status :status="$e->status" />
                        </div>
                        <p class="mt-0.5 text-[0.9375rem] text-muted">{{ $e->category->name }}, {{ $e->locationLabel() }}</p>
                        <p class="mt-2 flex items-center gap-2 text-sm text-muted">
                            <x-ui.plate :rating="$e->averageRating()" :count="$e->reviewCount()" class="!text-base" />
                            {{ $e->reviewCount() }} {{ Str::plural('review', $e->reviewCount()) }}
                        </p>
                    </div>
                    <x-listing.status-track :status="$e->status" class="basis-60" />
                </div>

                @if ($e->status === 'rejected')
                    <p class="mt-4 rounded-md border border-danger/30 bg-danger-50 px-4 py-3 text-sm text-danger-700"><strong>Administrator's note:</strong> {{ $e->rejection_reason }}</p>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-line pt-4">
                    <a href="{{ route('owner.establishments.edit', $e) }}" class="{{ $e->status === 'rejected' ? 'btn-primary' : 'btn-navy' }} btn-sm">{{ $e->status === 'rejected' ? 'Fix and resubmit' : 'Edit details' }}</a>
                    <a href="{{ route('owner.services.index', $e) }}" class="btn-outline btn-sm"><x-ui.icon name="wrench" class="size-3.5" /> Services</a>
                    <a href="{{ route('owner.images.index', $e) }}" class="btn-outline btn-sm"><x-ui.icon name="image" class="size-3.5" /> Photos</a>
                    <a href="{{ route('establishments.show', $e) }}" class="btn-outline btn-sm"><x-ui.icon name="eye" class="size-3.5" /> Preview</a>
                    <form method="POST" action="{{ route('owner.establishments.destroy', $e) }}" class="ml-auto"
                          data-confirm="Delete &quot;{{ $e->name }}&quot; with all its services, photos and reviews? This cannot be undone.">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger-outline btn-sm"><x-ui.icon name="trash" class="size-3.5" /> Delete</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>
    <div class="mt-8">{{ $establishments->links() }}</div>
@endif
@endsection
