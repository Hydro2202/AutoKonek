@php
    $user = auth()->user();

    // Members-only pages: guests are not shown links they cannot open.
    $links = $user
        ? [
            ['establishments.index', 'Find services'],
            ['places.index', 'Places'],
            ['about', 'About'],
            ['contact', 'Contact'],
        ]
        : [];
@endphp
<header class="sticky top-0 z-40 bg-navy text-white">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3 sm:px-6" aria-label="Main">
        <a href="{{ route('home') }}" aria-label="AutoKonek home"><x-brand.logo :light="true" /></a>

        <div class="hidden flex-1 items-center gap-1 md:flex">
            @foreach ($links as [$route, $label])
                @php $active = request()->routeIs($route.'*'); @endphp
                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                   class="relative px-3 py-2 text-[0.9375rem] font-medium {{ $active ? 'text-white' : 'text-white/70 hover:text-white' }}">
                    {{ $label }}
                    @if ($active)<span class="absolute inset-x-3 -bottom-[13px] h-[3px] bg-accent"></span>@endif
                </a>
            @endforeach
        </div>

        <div class="hidden items-center gap-2 md:flex">
            @auth
                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-sign py-1 pr-2 pl-1 text-sm font-medium hover:bg-white/10">
                        <span class="flex size-8 items-center justify-center rounded-sign bg-accent font-display text-lg font-bold text-navy-950">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        <span class="max-w-32 truncate">{{ $user->name }}</span>
                        <x-ui.icon name="chevron-down" class="size-4 text-white/60" />
                    </summary>
                    <div class="absolute right-0 mt-3 w-56 overflow-hidden rounded-md border border-line bg-white text-sm text-ink shadow-xl shadow-navy/15">
                        <p class="border-b border-line px-4 py-2.5 text-xs text-muted">Signed in as {{ $user->roleLabel() }}</p>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 hover:bg-page">{{ $user->isCustomer() ? 'Browse services' : 'Dashboard' }}</a>
                        @if ($user->isCustomer())
                            <a href="{{ route('customer.reviews') }}" class="block px-4 py-2.5 hover:bg-page">My reviews</a>
                            <a href="{{ route('customer.reports') }}" class="block px-4 py-2.5 hover:bg-page">My reports</a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 hover:bg-page">Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-line">@csrf
                            <button type="submit" data-no-loading class="block w-full px-4 py-2.5 text-left text-danger-700 hover:bg-page">Log out</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="px-3 py-2 text-[0.9375rem] font-medium text-white/80 hover:text-white">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary">Create account</a>
            @endauth
        </div>

        <button type="button" data-toggle="#mobile-menu" aria-expanded="false" aria-controls="mobile-menu"
                class="rounded-sign p-2 text-white hover:bg-white/10 md:hidden" aria-label="Menu"><x-ui.icon name="menu" class="size-6" /></button>
    </nav>

    <div id="mobile-menu" class="hidden border-t border-white/10 px-4 pt-3 pb-4 md:hidden">
        <div class="flex flex-col">
            @foreach ($links as [$route, $label])
                <a href="{{ route($route) }}" class="rounded-sign px-3 py-3 text-base font-medium text-white/90 hover:bg-white/10">{{ $label }}</a>
            @endforeach
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-sign px-3 py-3 text-base font-medium text-white/90 hover:bg-white/10">{{ $user->isCustomer() ? 'Browse services' : 'Dashboard' }}</a>
                @if ($user->isCustomer())
                    <a href="{{ route('customer.reviews') }}" class="rounded-sign px-3 py-3 text-base font-medium text-white/90 hover:bg-white/10">My reviews</a>
                    <a href="{{ route('customer.reports') }}" class="rounded-sign px-3 py-3 text-base font-medium text-white/90 hover:bg-white/10">My reports</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="rounded-sign px-3 py-3 text-base font-medium text-white/90 hover:bg-white/10">Profile</a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" data-no-loading class="w-full rounded-sign px-3 py-3 text-left text-base font-medium text-white/90 hover:bg-white/10">Log out</button>
                </form>
            @else
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="btn-outline justify-center !bg-transparent !text-white !border-white/30">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary">Create account</a>
                </div>
            @endauth
        </div>
    </div>
</header>
