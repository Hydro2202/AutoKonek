@extends('layouts.panel')

@section('title', 'Edit establishment')
@section('heading', $establishment->name)
@section('actions')<x-badge :status="$establishment->status" class="text-sm" />@endsection

@section('content')
@include('owner._tabs', ['active' => 'details'])

@if ($establishment->status === 'rejected')
    <div class="mb-5 max-w-3xl rounded-md border border-danger/30 bg-red-50 p-4 text-sm text-red-900" role="alert">
        <p class="font-semibold">This listing was rejected</p>
        <p class="mt-1">{{ $establishment->rejection_reason }}</p>
        <p class="mt-2">Make the corrections below and save. Saving resubmits the listing for approval.</p>
    </div>
@elseif ($establishment->status === 'approved')
    <div class="mb-5 max-w-3xl rounded-md border border-warning/30 bg-amber-50 p-4 text-sm text-amber-900">
        This listing is live. If you change its details, it goes back to <strong>pending</strong> and is hidden until an administrator approves the changes. Services and images can be changed without re-approval.
    </div>
@endif

<form method="POST" action="{{ route('owner.establishments.update', $establishment) }}" enctype="multipart/form-data" class="max-w-3xl space-y-5">
    @csrf @method('PUT')
    @include('owner.establishments._form')
    <div class="flex gap-2">
        <button type="submit" class="btn-primary">{{ $establishment->status === 'rejected' ? 'Save and resubmit' : 'Save changes' }}</button>
        <a href="{{ route('owner.establishments.index') }}" class="btn-outline">Back</a>
    </div>
</form>
@endsection
