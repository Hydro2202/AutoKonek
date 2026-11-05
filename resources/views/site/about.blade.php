@extends('layouts.site')

@section('title', 'About')

@section('content')
<section class="border-b border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
        <h1 class="max-w-2xl text-5xl sm:text-6xl">One place for every stop your vehicle makes.</h1>
        <p class="mt-4 max-w-2xl text-lg leading-relaxed text-ink/80">AutoKonek is a web-based automotive services discovery and management system. It brings gas stations, car washes, repair shops, tire shops, parking and battery services together, so a driver can compare them without calling around.</p>
    </div>
</section>

<section class="mx-auto grid max-w-7xl gap-6 px-4 py-14 sm:px-6 md:grid-cols-3">
    @foreach ([
        ['car', 'For drivers', 'Search is free. Filter by category and city, read prices and reviews, and report anything that looks wrong.'],
        ['building', 'For businesses', 'Submit your establishment, add services and photos, and keep your information current from your own dashboard.'],
        ['shield', 'Checked listings', 'An administrator approves every new listing and every change to an existing one before it is shown to the public.'],
    ] as [$icon, $title, $text])
        <div class="card p-6">
            <span class="sign size-11"><x-ui.icon :name="$icon" class="size-6" /></span>
            <h2 class="mt-4 text-2xl">{{ $title }}</h2>
            <p class="mt-1.5 leading-relaxed text-muted">{{ $text }}</p>
        </div>
    @endforeach
</section>

<section class="mx-auto max-w-3xl px-4 pb-16 sm:px-6">
    <div class="rounded-lg border border-dashed border-line-strong bg-white p-6">
        <h2 class="text-2xl">About the demo data</h2>
        <p class="mt-2 leading-relaxed text-muted">AutoKonek is a college final project. The establishments you see while it runs locally are fictional and are not verified real-world businesses. AutoKonek does not process payments or bookings; prices shown are provided by each business, so confirm them with the business before you go.</p>
    </div>
</section>
@endsection
