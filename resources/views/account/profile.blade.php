{{-- Profile page shared by every role, wrapped in the layout for that role. --}}
@php
    $layout = match (true) {
        $user->isAdmin() => 'layouts.admin',
        $user->isOwner() => 'layouts.owner',
        default => 'layouts.site',
    };
@endphp
@extends($layout)

@section('title', 'Profile')
@section('heading', 'Profile')
@section('subheading', 'Your account details and password.')

@section('content')
<div class="{{ $user->isCustomer() ? 'mx-auto max-w-2xl px-4 py-12 sm:px-6' : 'max-w-2xl' }} space-y-6">
    @if ($user->isCustomer())
        <h1 class="text-5xl">Profile</h1>
    @endif

    <section class="panel p-6">
        <div class="mb-5 flex items-center justify-between gap-3">
            <h2 class="text-2xl">Account details</h2>
            <x-ui.status :status="$user->role" />
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf @method('PUT')
            <x-form.input name="name" label="Full name" :value="$user->name" required />
            <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
            <x-form.input name="phone" type="tel" label="Phone" :value="$user->phone" />
            <button type="submit" class="btn-primary">Save changes</button>
        </form>
    </section>

    <section class="panel p-6">
        <h2 class="mb-5 text-2xl">Change password</h2>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
            @csrf @method('PUT')
            <x-form.input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
            <x-form.input name="password" type="password" label="New password" hint="At least 8 characters." required autocomplete="new-password" />
            <x-form.input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
            <button type="submit" class="btn-navy">Update password</button>
        </form>
    </section>

    <p class="text-sm text-muted">
        Read how we handle your data in the <a href="{{ route('legal.privacy') }}" class="font-semibold text-steel underline underline-offset-2">Privacy Policy</a>.
    </p>
</div>
@endsection
