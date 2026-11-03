@extends('layouts.app')

@section('title', 'About AutoKonek')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl sm:text-3xl">About AutoKonek</h1>
    <p class="mt-1 text-muted">A web-based automotive services discovery and management system.</p>

    <div class="card mt-6 space-y-4 p-6 text-sm leading-relaxed">
        <p>AutoKonek centralizes information about automotive service establishments so vehicle owners can find what they need quickly: gas stations, car washes, auto repair shops, tire and vulcanizing shops, parking facilities and battery services.</p>
        <p>Drivers can search and filter listings, compare services and prices, and read ratings and reviews from other customers. Business owners can submit their establishments, keep their information current and see what customers say. Administrators review every submission before it is shown to the public.</p>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        @foreach ([
            ['car', 'For drivers', 'Free to use. Search, compare and review.'],
            ['building', 'For businesses', 'Create a listing and manage it yourself.'],
            ['shield', 'Reviewed listings', 'New and changed listings are approved by an administrator.'],
        ] as [$icon, $title, $text])
            <div class="card p-5">
                <x-icon :name="$icon" class="size-6 text-steel" />
                <h2 class="mt-3 text-base">{{ $title }}</h2>
                <p class="mt-1 text-sm text-muted">{{ $text }}</p>
            </div>
        @endforeach
    </div>

    <p class="mt-6 rounded-md border border-line bg-white p-4 text-xs text-muted">AutoKonek is a college final project. Demonstration establishments are fictional and are not verified real-world businesses.</p>
</div>
@endsection
