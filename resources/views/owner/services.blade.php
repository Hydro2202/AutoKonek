@extends('layouts.panel')

@section('title', 'Manage services')
@section('heading', $establishment->name)
@section('actions')<x-badge :status="$establishment->status" class="text-sm" />@endsection

@section('content')
@include('owner._tabs', ['active' => 'services'])

<div class="grid max-w-4xl gap-6 lg:grid-cols-5">
    <section class="lg:col-span-3">
        <h2 class="mb-3 text-lg">Services offered</h2>
        @forelse ($establishment->services as $service)
            <div class="card mb-3 p-4">
                <form method="POST" action="{{ route('owner.services.update', [$establishment, $service]) }}" class="grid gap-3 sm:grid-cols-6">
                    @csrf @method('PUT')
                    <div class="sm:col-span-4">
                        <label class="label" for="name-{{ $service->id }}">Name</label>
                        <input id="name-{{ $service->id }}" name="name" value="{{ $service->name }}" required class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="price-{{ $service->id }}">Price (PHP)</label>
                        <input id="price-{{ $service->id }}" name="price" type="number" step="0.01" min="0" value="{{ $service->price }}" placeholder="Optional" class="input">
                    </div>
                    <div class="sm:col-span-6">
                        <label class="label" for="desc-{{ $service->id }}">Description</label>
                        <input id="desc-{{ $service->id }}" name="description" value="{{ $service->description }}" placeholder="Optional" class="input">
                    </div>
                    <div class="sm:col-span-6"><button type="submit" class="btn-navy btn-sm">Save</button></div>
                </form>
                <form method="POST" action="{{ route('owner.services.destroy', [$establishment, $service]) }}" data-confirm="Remove the service &quot;{{ $service->name }}&quot;?" class="mt-2">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-danger hover:underline">Remove service</button>
                </form>
            </div>
        @empty
            <x-empty-state icon="wrench" title="No services yet" text="Add the services you offer, with optional prices." />
        @endforelse
    </section>

    <section class="lg:col-span-2">
        <h2 class="mb-3 text-lg">Add a service</h2>
        <form method="POST" action="{{ route('owner.services.store', $establishment) }}" class="card space-y-3 p-4">
            @csrf
            <x-input name="name" label="Service name" required />
            <x-input name="price" type="number" step="0.01" min="0" label="Price (PHP, optional)" />
            <x-input name="description" label="Description (optional)" />
            <button type="submit" class="btn-primary w-full">Add service</button>
        </form>
    </section>
</div>
@endsection
