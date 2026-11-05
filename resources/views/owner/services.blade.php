@extends('layouts.owner')

@section('title', 'Services')
@section('heading', $establishment->name)
@section('actions')<x-ui.status :status="$establishment->status" />@endsection

@section('content')
@include('owner._tabs', ['active' => 'services'])

<div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_19rem]">
    <section>
        <h2 class="mb-3 text-3xl">Services offered</h2>
        <p class="mb-4 text-sm text-muted">Services and prices show on your public page as a menu. Changes here do not need re-approval.</p>

        @forelse ($establishment->services as $service)
            <div class="mb-3 rounded-lg border border-line bg-white p-4">
                <form method="POST" action="{{ route('owner.services.update', [$establishment, $service]) }}" class="grid gap-4 sm:grid-cols-6">
                    @csrf @method('PUT')
                    <div class="sm:col-span-4">
                        <label class="label" for="name-{{ $service->id }}">Service</label>
                        <input id="name-{{ $service->id }}" name="name" value="{{ $service->name }}" required class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="price-{{ $service->id }}">Price (₱)</label>
                        <input id="price-{{ $service->id }}" name="price" type="number" step="0.01" min="0" value="{{ $service->price }}" placeholder="Optional" class="input">
                    </div>
                    <div class="sm:col-span-6">
                        <label class="label" for="desc-{{ $service->id }}">Description</label>
                        <input id="desc-{{ $service->id }}" name="description" value="{{ $service->description }}" placeholder="Optional" class="input">
                    </div>
                    <div class="flex items-center justify-between sm:col-span-6">
                        <button type="submit" class="btn-navy btn-sm">Save</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('owner.services.destroy', [$establishment, $service]) }}" data-confirm="Remove the service &quot;{{ $service->name }}&quot;?" class="mt-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm font-semibold text-danger-700 hover:underline">Remove service</button>
                </form>
            </div>
        @empty
            <x-ui.empty-state icon="wrench" title="No services yet" text="Add what you offer, with an optional price for each." />
        @endforelse
    </section>

    <section>
        <h2 class="mb-3 text-3xl">Add a service</h2>
        <form method="POST" action="{{ route('owner.services.store', $establishment) }}" class="space-y-4 rounded-lg border border-line bg-white p-5 lg:sticky lg:top-8">
            @csrf
            <x-form.input name="name" label="Service name" required />
            <x-form.input name="price" type="number" step="0.01" min="0" label="Price (₱, optional)" />
            <x-form.input name="description" label="Description (optional)" />
            <button type="submit" class="btn-primary w-full">Add service</button>
        </form>
    </section>
</div>
@endsection
