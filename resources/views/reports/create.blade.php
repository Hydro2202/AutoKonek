@extends('layouts.app')

@section('title', 'Report')

@section('content')
@php $isListing = $type === 'establishment'; @endphp
<div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl">Report {{ $isListing ? 'a listing' : 'a review' }}</h1>

    <div class="card mt-4 p-4 text-sm">
        @if ($isListing)
            <p class="text-xs uppercase tracking-wide text-muted">Listing</p>
            <p class="font-semibold text-navy">{{ $target->name }}</p>
        @else
            <p class="text-xs uppercase tracking-wide text-muted">Review of {{ $target->establishment->name }}</p>
            <p class="mt-1 whitespace-pre-line">{{ Str::limit($target->comment, 250) }}</p>
        @endif
    </div>

    <div class="card mt-4 p-6">
        <form method="POST" action="{{ route('reports.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="target_type" value="{{ $type }}">
            <input type="hidden" name="target_id" value="{{ $target->id }}">
            <x-select name="reason" label="What is the problem?" required>
                <option value="">Choose a reason...</option>
                @foreach (\App\Models\Report::REASONS as $value => $label)
                    <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                @endforeach
            </x-select>
            <x-textarea name="description" label="Details (optional)" rows="4" hint="Tell the administrators what is wrong." />
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Submit report</button>
                <a href="{{ url()->previous() }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
