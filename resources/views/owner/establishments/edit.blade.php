@extends('layouts.owner')

@section('title', 'Edit establishment')
@section('heading', $establishment->name)
@section('actions')<x-ui.status :status="$establishment->status" />@endsection

@section('content')
@include('owner._tabs', ['active' => 'details'])

@if ($establishment->status === 'rejected')
    <div class="mb-6 rounded-lg border border-danger/30 bg-danger-50 p-5 text-[0.9375rem]" role="alert">
        <p class="font-display text-2xl font-semibold text-danger-700">This listing needs changes</p>
        <p class="mt-1 text-ink/90"><span class="font-semibold">Administrator's note:</span> {{ $establishment->rejection_reason }}</p>
        <p class="mt-2 text-muted">Fix the details below and save. Saving sends the listing back for review.</p>
    </div>
@elseif ($establishment->status === 'approved')
    <div class="mb-6 rounded-lg border border-warning/30 bg-warning-50 p-4 text-[0.9375rem] text-warning-700">
        This listing is live. If you change its details it returns to <strong>pending</strong> and is hidden until an administrator approves the changes. Services and photos can be changed freely.
    </div>
@endif

<form method="POST" action="{{ route('owner.establishments.update', $establishment) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf @method('PUT')
    @include('owner.establishments._form')
    <div class="flex gap-2">
        <button type="submit" class="btn-primary btn-lg">{{ $establishment->status === 'rejected' ? 'Save and resubmit' : 'Save changes' }}</button>
        <a href="{{ route('owner.establishments.index') }}" class="btn-outline btn-lg">Back</a>
    </div>
</form>
@endsection
