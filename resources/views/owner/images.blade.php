@extends('layouts.owner')

@section('title', 'Photos')
@section('heading', $establishment->name)
@section('actions')<x-ui.status :status="$establishment->status" />@endsection

@section('content')
@include('owner._tabs', ['active' => 'images'])

<form method="POST" action="{{ route('owner.images.store', $establishment) }}" enctype="multipart/form-data" class="rounded-lg border border-line bg-white p-5">
    @csrf
    <label for="images" class="label">Upload photos</label>
    <input id="images" type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp" data-preview="#image-names" class="file-input">
    <p id="image-names" class="hint">JPG, PNG or WebP, up to 2 MB each. Up to {{ config('autokonek.images.max_per_establishment') }} photos per establishment.</p>
    <button type="submit" class="btn-primary mt-4"><x-ui.icon name="upload" class="size-4" /> Upload</button>
</form>

<div class="mt-8">
    @if ($establishment->images->isEmpty())
        <x-ui.empty-state icon="image" title="No photos yet" text="Without photos, your listing shows a placeholder picture." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($establishment->images as $image)
                <figure class="overflow-hidden rounded-lg border border-line bg-white">
                    <img src="{{ $image->url() }}" alt="Photo {{ $loop->iteration }} of {{ $establishment->name }}" class="aspect-[4/3] w-full object-cover">
                    <figcaption class="flex items-center justify-between gap-2 p-3">
                        @if ($image->is_primary)
                            <span class="chip border-accent/40 bg-accent-100 text-accent-600">Main photo</span>
                        @else
                            <form method="POST" action="{{ route('owner.images.primary', [$establishment, $image]) }}">
                                @csrf @method('PUT')
                                <button type="submit" class="btn-outline btn-sm">Make main</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('owner.images.destroy', [$establishment, $image]) }}" data-confirm="Remove this photo?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon text-danger-700" aria-label="Remove photo"><x-ui.icon name="trash" class="size-4" /></button>
                        </form>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</div>
@endsection
