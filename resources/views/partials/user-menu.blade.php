@php $u = auth()->user(); @endphp
<details class="relative">
    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-white hover:bg-white/10">
        <span class="flex size-7 items-center justify-center rounded-full bg-accent text-xs font-bold text-navy">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
        <span class="max-w-32 truncate">{{ $u->name }}</span>
    </summary>
    <div class="absolute right-0 mt-2 w-52 rounded-md border border-line bg-white py-1 text-sm text-ink shadow-lg">
        <p class="border-b border-line px-3 py-2 text-xs text-muted">{{ $u->roleLabel() }}</p>
        <a href="{{ route('dashboard') }}" class="block px-3 py-2 hover:bg-page">{{ $u->isCustomer() ? 'Browse services' : 'Dashboard' }}</a>
        @if ($u->isCustomer())
            <a href="{{ route('customer.reviews') }}" class="block px-3 py-2 hover:bg-page">My reviews</a>
            <a href="{{ route('customer.reports') }}" class="block px-3 py-2 hover:bg-page">My reports</a>
        @endif
        <a href="{{ route('profile.edit') }}" class="block px-3 py-2 hover:bg-page">Profile</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" data-no-loading class="block w-full px-3 py-2 text-left hover:bg-page">Log out</button>
        </form>
    </div>
</details>
