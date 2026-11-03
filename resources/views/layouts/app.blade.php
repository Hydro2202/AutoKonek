@extends('layouts.base')

@section('body')
<div class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-2 focus:rounded focus:bg-white focus:px-3 focus:py-2">Skip to content</a>

    <header class="sticky top-0 z-40 bg-navy text-white shadow-sm">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6" aria-label="Main">
            <a href="{{ route('home') }}" aria-label="AutoKonek home"><x-logo :light="true" /></a>

            <div class="hidden items-center gap-1 md:flex">
                @foreach ([['establishments.index', 'Find Services'], ['about', 'About'], ['contact', 'Contact']] as [$route, $label])
                    <a href="{{ route($route) }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($route) ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </div>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    @include('partials.user-menu')
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-white/80 hover:text-white">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary">Sign up</a>
                @endauth
            </div>

            <button type="button" data-toggle="#mobile-menu" aria-expanded="false" aria-controls="mobile-menu" class="rounded-md p-2 text-white hover:bg-white/10 md:hidden" aria-label="Toggle menu">
                <x-icon name="menu" class="size-6" />
            </button>
        </nav>

        <div id="mobile-menu" class="hidden border-t border-white/10 px-4 pb-4 md:hidden">
            <div class="flex flex-col gap-1 pt-3">
                <a href="{{ route('establishments.index') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">Find Services</a>
                <a href="{{ route('about') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">About</a>
                <a href="{{ route('contact') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">My dashboard</a>
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('customer.reviews') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">My reviews</a>
                        <a href="{{ route('customer.reports') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">My reports</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" data-no-loading class="w-full rounded-md px-3 py-2 text-left text-sm text-white/90 hover:bg-white/10">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm text-white/90 hover:bg-white/10">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary mt-2">Sign up</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="main" class="flex-1">
        @if (session('success') || session('error') || $errors->any())
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6"><x-flash /></div>
        @endif
        @yield('content')
    </main>

    <footer class="bg-navy text-white/80">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <x-logo :light="true" />
                <p class="mt-3 max-w-sm text-sm">Find the services your vehicle needs. AutoKonek brings automotive service information into one place.</p>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-white">Explore</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a class="hover:text-white" href="{{ route('establishments.index') }}">Find services</a></li>
                    <li><a class="hover:text-white" href="{{ route('about') }}">About AutoKonek</a></li>
                    <li><a class="hover:text-white" href="{{ route('contact') }}">Contact us</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-white">For businesses</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a class="hover:text-white" href="{{ route('register') }}">List your business</a></li>
                    <li><a class="hover:text-white" href="{{ route('login') }}">Owner login</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 px-4 py-4 text-center text-xs text-white/60">
            &copy; {{ date('Y') }} AutoKonek. A college project. Demo listings are fictional.
        </div>
    </footer>
</div>
@endsection
