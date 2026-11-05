@extends('layouts.admin')

@section('title', $establishment->name)
@section('heading', $establishment->name)
@section('subheading', $establishment->category->name.', submitted by '.$establishment->owner->name.' ('.$establishment->owner->email.')')
@section('actions')<x-ui.status :status="$establishment->status" />@endsection

@section('content')
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_21rem]">
    <div class="space-y-6">
        <x-panel.card title="Photos" :flush="true">
            <img src="{{ $establishment->imageUrl() }}" alt="{{ $establishment->name }}" class="aspect-[16/7] w-full object-cover">
            @if ($establishment->images->count() > 1)
                <div class="grid grid-cols-5 gap-2 p-3">
                    @foreach ($establishment->images as $image)<img src="{{ $image->url() }}" alt="" class="aspect-square w-full rounded object-cover">@endforeach
                </div>
            @endif
        </x-panel.card>

        <x-panel.card title="Listing details">
            <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div class="sm:col-span-2"><dt class="font-medium text-muted">Description</dt><dd class="mt-1 whitespace-pre-line">{{ $establishment->description }}</dd></div>
                <div><dt class="font-medium text-muted">Address</dt><dd class="mt-1">{{ $establishment->address }}<br>{{ $establishment->locationLabel() }}@if ($establishment->city?->region), {{ $establishment->city->region->name }}@endif</dd></div>
                <div><dt class="font-medium text-muted">GPS pin</dt><dd class="mt-1">@if ($establishment->hasCoordinates()){{ $establishment->latitude }}, {{ $establishment->longitude }} <a href="{{ $establishment->mapLinks()->openStreetMap() }}" target="_blank" rel="noopener" class="font-medium text-steel hover:underline">View on map</a>@else<span class="text-muted">Not set (directions search the address)</span>@endif</dd></div>
                <div><dt class="font-medium text-muted">Contact</dt><dd class="mt-1">{{ $establishment->contact_number ?: 'No phone' }}<br>{{ $establishment->contact_email ?: 'No email' }}</dd></div>
                <div class="sm:col-span-2"><dt class="font-medium text-muted">Opening hours</dt><dd class="mt-1 whitespace-pre-line">{{ $establishment->opening_hours ?: 'Not provided' }}</dd></div>
            </dl>
        </x-panel.card>

        <x-panel.card :title="'Services ('.$establishment->services->count().')'" :flush="true">
            @forelse ($establishment->services as $service)
                <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-2.5 text-sm last:border-0">
                    <span>{{ $service->name }}</span>
                    <span class="font-medium">{{ $service->price !== null ? '₱'.number_format($service->price, 2) : 'No price' }}</span>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-muted">No services listed.</p>
            @endforelse
        </x-panel.card>
    </div>

    <aside class="space-y-4">
        @if ($establishment->status === 'rejected')
            <div class="rounded-md border border-danger/30 bg-danger-50 p-4 text-sm text-danger-700">
                <p class="font-semibold">Previously rejected</p>
                <p class="mt-1">{{ $establishment->rejection_reason }}</p>
            </div>
        @endif

        <x-panel.card title="Decision">
            @if ($establishment->status !== 'approved')
                <form method="POST" action="{{ route('admin.establishments.approve', $establishment) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-navy w-full"><x-ui.icon name="check" class="size-4" /> Approve and publish</button>
                </form>
            @else
                <p class="text-sm text-muted">Approved and visible to the public.</p>
            @endif

            <form method="POST" action="{{ route('admin.establishments.reject', $establishment) }}" class="mt-5 space-y-3 border-t border-line pt-5"
                  data-confirm="Reject this listing? The owner will see your reason.">
                @csrf @method('PATCH')
                <x-form.textarea name="rejection_reason" label="Rejection reason" rows="4" hint="Shown to the owner. Say what to fix." required />
                <button type="submit" class="btn-danger w-full">Reject listing</button>
            </form>
        </x-panel.card>

        <div class="flex gap-2">
            <a href="{{ route('establishments.show', $establishment) }}" class="btn-outline flex-1">Public page</a>
            <a href="{{ route('admin.approvals') }}" class="btn-outline flex-1">Back to queue</a>
        </div>
    </aside>
</div>
@endsection
