@extends('layouts.base')

{{-- Business owner area: light sidebar, roomy content. --}}
@php
    $user = auth()->user();
    $nav = [
        ['owner.dashboard', 'Dashboard', 'dashboard', 'owner.dashboard'],
        ['owner.establishments.index', 'My establishments', 'building', ['owner.establishments.*', 'owner.services.*', 'owner.images.*']],
        ['owner.reviews', 'Customer reviews', 'star', 'owner.reviews'],
        ['profile.edit', 'Profile', 'user', 'profile.*'],
    ];
@endphp

@section('body')
<div class="min-h-screen lg:grid lg:grid-cols-[16.5rem_minmax(0,1fr)]">
    <div class="flex items-center justify-between bg-navy px-4 py-3 lg:hidden">
        <a href="{{ route('home') }}" aria-label="AutoKonek home"><x-brand.logo :light="true" /></a>
        <button type="button" data-toggle="#sidebar" aria-expanded="false" aria-controls="sidebar" class="rounded-sign p-2 text-white hover:bg-white/10" aria-label="Menu"><x-ui.icon name="menu" class="size-6" /></button>
    </div>

    <aside id="sidebar" class="anim-menu hidden flex-col border-r border-line bg-white lg:sticky lg:top-0 lg:flex lg:h-screen">
        <div class="hidden px-6 pt-6 pb-5 lg:block"><a href="{{ route('home') }}" aria-label="AutoKonek home"><x-brand.logo /></a></div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3" aria-label="Business owner">
            @foreach ($nav as [$route, $label, $icon, $patterns])
                @php $active = request()->routeIs($patterns); @endphp
                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-sign border-l-[3px] px-3 py-2.5 text-[0.9375rem] font-semibold {{ $active ? 'border-accent bg-steel-50 text-navy' : 'border-transparent text-muted hover:bg-page hover:text-navy' }}">
                    <x-ui.icon :name="$icon" class="size-5" /> {{ $label }}
                </a>
            @endforeach

            <a href="{{ route('owner.establishments.create') }}" class="btn-primary mt-4 w-full"><x-ui.icon name="plus" class="size-4" /> Add establishment</a>
        </nav>

        <div class="border-t border-line p-3">
            <div class="flex items-center gap-3 px-3 py-2">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-sign bg-navy font-display text-lg font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-navy">{{ $user->name }}</p>
                    <p class="truncate text-xs text-muted">{{ $user->email }}</p>
                </div>
            </div>
            <a href="{{ route('establishments.index') }}" class="flex items-center gap-3 rounded-sign px-3 py-2 text-sm text-muted hover:bg-page hover:text-navy"><x-ui.icon name="external" class="size-4" /> View public site</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" data-no-loading class="flex w-full items-center gap-3 rounded-sign px-3 py-2 text-sm text-muted hover:bg-page hover:text-navy"><x-ui.icon name="logout" class="size-4" /> Log out</button>
            </form>
        </div>
    </aside>

    <main id="main" class="min-w-0 px-4 py-8 sm:px-8 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-4xl">@yield('heading')</h1>
                    @hasSection('subheading')<p class="mt-1.5 max-w-xl text-[0.9375rem] text-muted">@yield('subheading')</p>@endif
                </div>
                @yield('actions')
            </header>

            <x-ui.form-errors />
            @yield('content')
        </div>
    </main>
</div>
@endsection
