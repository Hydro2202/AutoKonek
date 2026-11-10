@extends('layouts.base')

{{-- Administrator area: dark sidebar with live counters, top bar, dense content. --}}
@php
    $user = auth()->user();
    $groups = [
        'Overview' => [
            ['admin.dashboard', 'Dashboard', 'dashboard', 'admin.dashboard', null],
        ],
        'Moderation' => [
            ['admin.approvals', 'Pending approvals', 'circle-check', 'admin.approvals', $sidebarCounts['approvals'] ?? 0],
            ['admin.reports.index', 'Reports', 'flag', 'admin.reports.*', $sidebarCounts['reports'] ?? 0],
            ['admin.reviews.index', 'Reviews', 'star', 'admin.reviews.*', null],
        ],
        'Catalog' => [
            ['admin.establishments.index', 'Establishments', 'building', 'admin.establishments.*', null],
            ['admin.categories.index', 'Categories', 'tag', 'admin.categories.*', null],
        ],
        'People and inbox' => [
            ['admin.users.index', 'Users', 'users', 'admin.users.*', null],
            ['admin.messages.index', 'Messages', 'inbox', 'admin.messages.*', null],
        ],
    ];
@endphp

@section('body-class', 'admin')

@section('body')
<div class="min-h-screen lg:grid lg:grid-cols-[15.5rem_minmax(0,1fr)]">
    <div class="flex items-center justify-between bg-navy-950 px-4 py-3 lg:hidden">
        <a href="{{ route('admin.dashboard') }}" aria-label="Admin dashboard"><x-brand.logo :light="true" /></a>
        <button type="button" data-toggle="#sidebar" aria-expanded="false" aria-controls="sidebar" class="rounded-sign p-2 text-white hover:bg-white/10" aria-label="Menu"><x-ui.icon name="menu" class="size-6" /></button>
    </div>

    <aside id="sidebar" class="hidden flex-col bg-navy-950 text-white/70 lg:sticky lg:top-0 lg:flex lg:h-screen">
        <div class="hidden items-center gap-2 px-5 py-5 lg:flex">
            <a href="{{ route('admin.dashboard') }}" aria-label="Admin dashboard"><x-brand.logo :light="true" /></a>
        </div>
        <p class="px-5 pt-1 pb-2 text-xs font-medium text-accent">Administration</p>

        <nav class="flex-1 overflow-y-auto px-3 pb-4" aria-label="Administration">
            @foreach ($groups as $group => $items)
                <p class="mt-5 mb-1.5 px-3 text-xs font-semibold text-white/35">{{ $group }}</p>
                @foreach ($items as [$route, $label, $icon, $patterns, $count])
                    @php $active = request()->routeIs($patterns); @endphp
                    <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-[5px] px-3 py-2 text-sm font-medium {{ $active ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                        <x-ui.icon :name="$icon" class="size-[1.1rem] {{ $active ? 'text-accent' : '' }}" />
                        <span class="flex-1">{{ $label }}</span>
                        @if ($count)<span class="min-w-5 rounded-full bg-accent px-1.5 py-px text-center text-xs font-bold text-navy-950">{{ $count }}</span>@endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-[5px] px-3 py-2 text-sm hover:bg-white/5 hover:text-white"><x-ui.icon name="user" class="size-[1.1rem]" /> Profile</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" data-no-loading class="flex w-full items-center gap-3 rounded-[5px] px-3 py-2 text-sm hover:bg-white/5 hover:text-white"><x-ui.icon name="logout" class="size-[1.1rem]" /> Log out</button>
            </form>
        </div>
    </aside>

    <div class="min-w-0">
        <div class="hidden h-14 items-center justify-between border-b border-line bg-white px-8 lg:flex">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-muted">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-navy">Admin</a>
                <x-ui.icon name="chevron-right" class="size-3.5" />
                <span class="font-medium text-ink">@yield('heading')</span>
            </nav>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-muted hover:text-navy">View site <x-ui.icon name="external" class="size-3.5" /></a>
                <span class="h-5 w-px bg-line"></span>
                <span class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-full bg-navy text-xs font-semibold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="font-medium">{{ $user->name }}</span>
                </span>
            </div>
        </div>

        <main id="main" class="px-4 py-6 sm:px-6 lg:px-8">
            <header class="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-[1.65rem]">@yield('heading')</h1>
                    @hasSection('subheading')<p class="mt-1 text-sm text-muted">@yield('subheading')</p>@endif
                </div>
                @yield('actions')
            </header>

            <x-ui.form-errors />
            @yield('content')
        </main>
    </div>
</div>
@endsection
