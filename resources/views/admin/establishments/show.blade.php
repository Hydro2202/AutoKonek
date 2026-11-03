@extends('layouts.panel')

@section('title', $establishment->name)
@section('heading', $establishment->name)
@section('subheading', $establishment->category->name.' - submitted by '.$establishment->owner->name.' ('.$establishment->owner->email.')')
@section('actions')<x-badge :status="$establishment->status" class="text-sm" />@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="card overflow-hidden">
            <img src="{{ $establishment->imageUrl() }}" alt="{{ $establishment->name }}" class="aspect-[16/8] w-full object-cover">
            @if ($establishment->images->count() > 1)
                <div class="grid grid-cols-5 gap-2 p-2">
                    @foreach ($establishment->images as $image)<img src="{{ $image->url() }}" alt="" class="aspect-square w-full rounded object-cover">@endforeach
                </div>
            @endif
        </div>

        <div class="card p-5 text-sm">
            <h2 class="text-base">Description</h2>
            <p class="mt-2 whitespace-pre-line">{{ $establishment->description }}</p>
            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase text-muted">Address</dt><dd>{{ $establishment->address }}, {{ $establishment->locationLabel() }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase text-muted">Contact</dt><dd>{{ $establishment->contact_number ?: 'No phone' }}<br>{{ $establishment->contact_email ?: 'No email' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase text-muted">Hours</dt><dd class="whitespace-pre-line">{{ $establishment->opening_hours ?: 'Not provided' }}</dd></div>
            </dl>
        </div>

        <div class="card p-5 text-sm">
            <h2 class="text-base">Services ({{ $establishment->services->count() }})</h2>
            @forelse ($establishment->services as $s)
                <div class="flex justify-between border-t border-line py-2 first:mt-2"><span>{{ $s->name }}</span><span class="font-semibold">{{ $s->price !== null ? 'PHP '.number_format($s->price, 2) : '-' }}</span></div>
            @empty
                <p class="mt-2 text-muted">No services listed.</p>
            @endforelse
        </div>
    </div>

    <aside class="space-y-4">
        @if ($establishment->status === 'rejected')
            <div class="rounded-md border border-danger/30 bg-red-50 p-4 text-sm text-red-900"><p class="font-semibold">Rejected</p><p class="mt-1">{{ $establishment->rejection_reason }}</p></div>
        @endif

        <div class="card p-5">
            <h2 class="text-base">Decision</h2>
            @if ($establishment->status !== 'approved')
                <form method="POST" action="{{ route('admin.establishments.approve', $establishment) }}" class="mt-3">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-primary w-full"><x-icon name="check" class="size-4" /> Approve listing</button>
                </form>
            @else
                <p class="mt-2 text-sm text-muted">This listing is approved and public.</p>
            @endif

            <form method="POST" action="{{ route('admin.establishments.reject', $establishment) }}" class="mt-4 space-y-3 border-t border-line pt-4"
                  data-confirm="Reject this listing? The owner will see your reason.">
                @csrf @method('PATCH')
                <x-textarea name="rejection_reason" label="Rejection reason" rows="4" hint="Shown to the owner. Explain what to fix." required />
                <button type="submit" class="btn-danger w-full">Reject listing</button>
            </form>
        </div>

        <a href="{{ route('establishments.show', $establishment) }}" class="btn-outline w-full">Open public page</a>
        <a href="{{ route('admin.approvals') }}" class="btn-outline w-full">Back to approvals</a>
    </aside>
</div>
@endsection
