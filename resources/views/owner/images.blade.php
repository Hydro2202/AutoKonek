@extends('layouts.panel')

@section('title', 'Manage images')
@section('heading', $establishment->name)
@section('actions')<x-badge :status="$establishment->status" class="text-sm" />@endsection

@section('content')
@include('owner._tabs', ['active' => 'images'])

<div class="max-w-4xl space-y-6">
    <form method="POST" action="{{ route('owner.images.store', $establishment) }}" enctype="multipart/form-data" class="card p-5">
        @csrf
        <label for="images" class="label">Upload images</label>
        <input id="images" type="file" name="images[]" multiple required accept="image/jpeg,image/png,image/webp" data-preview="#image-names"
               class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-700">
        <p id="image-names" class="hint">JPG, PNG or WebP, up to 2 MB each. Up to 8 images per establishment.</p>
        <button type="submit" class="btn-primary mt-3">Upload</button>
    </form>

    @if ($establishment->images->isEmpty())
        <x-empty-state icon="image" title="No images yet" text="Listings without images show a placeholder." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($establishment->images as $image)
                <div class="card overflow-hidden">
                    <img src="{{ $image->url() }}" alt="Image {{ $loop->iteration }} of {{ $establishment->name }}" class="aspect-[4/3] w-full object-cover">
                    <div class="flex items-center justify-between gap-2 p-3">
                        @if ($image->is_primary)
                            <span class="rounded-full bg-accent/15 px-2.5 py-0.5 text-xs font-semibold text-accent-600">Main image</span>
                        @else
                            <form method="POST" action="{{ route('owner.images.primary', [$establishment, $image]) }}">
                                @csrf @method('PUT')
                                <button type="submit" class="btn-outline btn-sm">Set as main</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('owner.images.destroy', [$establishment, $image]) }}" data-confirm="Remove this image?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-outline btn-sm text-danger" aria-label="Remove image"><x-icon name="trash" class="size-3.5" /></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
