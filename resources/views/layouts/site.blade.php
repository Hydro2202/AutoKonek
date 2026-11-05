@extends('layouts.base')

{{-- Public pages and customer pages --}}
@section('body')
<div class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[60] focus:rounded-sign focus:bg-white focus:px-3 focus:py-2 focus:text-navy">Skip to content</a>

    <x-layout.site-header />

    <main id="main" class="flex-1">
        @if ($errors->any())
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6"><x-ui.form-errors /></div>
        @endif
        @yield('content')
    </main>

    <x-layout.site-footer />
</div>

<x-layout.cookie-banner />
@endsection
