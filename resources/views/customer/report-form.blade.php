@extends('layouts.site')

@section('title', 'Report')

@php $isListing = $type === 'establishment'; @endphp

@section('content')
<div class="mx-auto max-w-xl px-4 py-12 sm:px-6">
    <h1 class="text-5xl">Report {{ $isListing ? 'a listing' : 'a review' }}</h1>
    <p class="mt-1.5 text-muted">Reports go to the AutoKonek moderators. The business and the author are not told who reported.</p>

    <div class="mt-6 rounded-lg border border-line bg-white p-4">
        @if ($isListing)
            <p class="text-sm text-muted">Listing</p>
            <p class="font-display text-2xl font-semibold text-navy">{{ $target->name }}</p>
        @else
            <p class="text-sm text-muted">Review of {{ $target->establishment->name }}</p>
            <p class="mt-1 whitespace-pre-line text-[0.9375rem]">{{ Str::limit($target->comment, 250) }}</p>
        @endif
    </div>

    <form method="POST" action="{{ route('reports.store') }}" class="card mt-4 space-y-5 p-6">
        @csrf
        <input type="hidden" name="target_type" value="{{ $type }}">
        <input type="hidden" name="target_id" value="{{ $target->id }}">

        <x-form.select name="reason" label="What is the problem?" required>
            <option value="">Choose a reason</option>
            @foreach (\App\Models\Report::REASONS as $value => $label)
                <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
            @endforeach
        </x-form.select>
        <x-form.textarea name="description" label="Details (optional)" rows="4" hint="What should the moderators look at?" />

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Send report</button>
            <a href="{{ url()->previous() }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
