@extends('layouts.base')

{{-- Login and registration: brand panel on the left, form on the right. --}}
@section('body')
<div class="grid min-h-screen lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
    <aside class="relative hidden flex-col justify-between overflow-hidden bg-navy p-12 text-white lg:flex">
        <a href="{{ route('home') }}" aria-label="AutoKonek home"><x-brand.logo :light="true" /></a>

        <div>
            <h2 class="max-w-sm text-6xl leading-[0.95] !text-white">@yield('panel-title')</h2>
            <p class="mt-5 max-w-sm text-lg text-white/75">@yield('panel-text')</p>

            <ul class="mt-8 space-y-3 text-[0.9375rem] text-white/85">
                @yield('panel-points')
            </ul>
        </div>

        {{-- Center line of the road --}}
        <div>
            <div class="roadline"></div>
            <p class="mt-4 text-sm text-white/50">{{ config('autokonek.location') }}</p>
        </div>
    </aside>

    <div class="flex flex-col">
        <div class="flex items-center justify-between bg-navy px-5 py-4 lg:hidden">
            <a href="{{ route('home') }}" aria-label="AutoKonek home"><x-brand.logo :light="true" /></a>
        </div>
        <main id="main" class="flex flex-1 items-center justify-center px-5 py-10 sm:px-10">
            <div class="w-full max-w-md">
                @if (session('info'))
                    <div role="status" class="mb-5 flex items-start gap-3 rounded-md border border-steel/30 bg-steel-50 px-4 py-3 text-sm text-navy">
                        <x-ui.icon name="lock" class="mt-0.5 size-5 shrink-0 text-steel" />
                        <p>{{ session('info') }}</p>
                    </div>
                @endif
                <x-ui.form-errors />
                @yield('content')
            </div>
        </main>
        <p class="px-5 pb-6 text-center text-sm text-muted">
            <a href="{{ route('legal.terms') }}" class="hover:text-navy hover:underline">Terms</a>
            <span class="mx-2" aria-hidden="true">/</span>
            <a href="{{ route('legal.privacy') }}" class="hover:text-navy hover:underline">Privacy</a>
            <span class="mx-2" aria-hidden="true">/</span>
            <a href="{{ route('legal.cookies') }}" class="hover:text-navy hover:underline">Cookies</a>
        </p>
    </div>
</div>

<x-layout.cookie-banner />
@endsection
