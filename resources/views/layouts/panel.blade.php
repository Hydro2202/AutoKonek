@extends('layouts.base')

@php
    $u = auth()->user();
    $isAdmin = $u->isAdmin();
    $nav = $isAdmin
        ? [
            ['admin.dashboard', 'Dashboard', 'dashboard', 'admin.dashboard'],
            ['admin.approvals', 'Pending approvals', 'check', 'admin.approvals'],
            ['admin.establishments.index', 'Establishments', 'building', 'admin.establishments.*'],
            ['admin.users.index', 'Users', 'users', 'admin.users.*'],
            ['admin.categories.index', 'Categories', 'tag', 'admin.categories.*'],
            ['admin.reviews.index', 'Reviews', 'star', 'admin.reviews.*'],
            ['admin.reports.index', 'Reports', 'flag', 'admin.reports.*'],
            ['admin.messages.index', 'Messages', 'mail', 'admin.messages.*'],
            ['profile.edit', 'Profile', 'user', 'profile.*'],
        ]
        : [
            ['owner.dashboard', 'Dashboard', 'dashboard', 'owner.dashboard'],
            ['owner.establishments.index', 'My establishments', 'building', ['owner.establishments.*', 'owner.services.*', 'owner.images.*']],
            ['owner.reviews', 'Reviews', 'star', 'owner.reviews'],
            ['profile.edit', 'Profile', 'user', 'profile.*'],
        ];
@endphp

@section('body')
<div class="min-h-screen lg:flex">
    {{-- Mobile top bar --}}
    <div class="flex items-center justify-between bg-navy px-4 py-3 text-white lg:hidden">
        <a href="{{ route('home') }}"><x-logo :light="true" class="text-lg" /></a>
        <button type="button" data-toggle="#sidebar" aria-expanded="false" aria-controls="sidebar" class="rounded-md p-2 hover:bg-white/10" aria-label="Toggle menu"><x-icon name="menu" class="size-6" /></button>
    </div>

    <aside id="sidebar" class="hidden bg-navy text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-64 lg:shrink-0 lg:flex-col">
        <div class="hidden px-5 py-5 lg:block"><a href="{{ route('home') }}"><x-logo :light="true" /></a></div>
        <p class="px-5 pt-3 text-xs font-semibold uppercase tracking-wider text-white/50 lg:pt-0">{{ $isAdmin ? 'Administrator' : 'Business owner' }}</p>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3" aria-label="Dashboard">
            @foreach ($nav as [$route, $label, $icon, $patterns])
                @php $active = request()->routeIs($patterns); @endphp
                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'bg-white/10 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                    <x-icon :name="$icon" class="size-4.5" /> {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-3">
            <p class="truncate px-3 text-sm font-semibold">{{ $u->name }}</p>
            <p class="truncate px-3 pb-2 text-xs text-white/60">{{ $u->email }}</p>
            <a href="{{ route('establishments.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm text-white/75 hover:bg-white/10 hover:text-white"><x-icon name="search" class="size-4.5" /> View public site</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" data-no-loading class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm text-white/75 hover:bg-white/10 hover:text-white"><x-icon name="logout" class="size-4.5" /> Log out</button>
            </form>
        </div>
    </aside>

    <main id="main" class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <header class="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl">@yield('heading')</h1>
                    @hasSection('subheading')<p class="mt-1 text-sm text-muted">@yield('subheading')</p>@endif
                </div>
                @yield('actions')
            </header>
            <div class="mb-4"><x-flash /></div>
            @yield('content')
        </div>
    </main>
</div>
@endsection
