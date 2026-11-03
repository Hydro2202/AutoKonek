@extends($user->isCustomer() ? 'layouts.app' : 'layouts.panel')

@section('title', 'Profile')
@section('heading', 'Profile')
@section('subheading', 'Manage your account details.')

@section('content')
@php $wrap = $user->isCustomer() ? 'mx-auto max-w-2xl px-4 py-10 sm:px-6' : 'max-w-2xl'; @endphp
<div class="{{ $wrap }} space-y-6">
    @if ($user->isCustomer()) <h1 class="text-2xl">Profile</h1> @endif

    <div class="card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg">Account details</h2>
            <x-badge :status="$user->role" />
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <x-input name="name" label="Full name" :value="$user->name" required />
            <x-input name="email" type="email" label="Email" :value="$user->email" required />
            <x-input name="phone" type="tel" label="Phone" :value="$user->phone" />
            <button type="submit" class="btn-primary">Save changes</button>
        </form>
    </div>

    <div class="card p-6">
        <h2 class="mb-4 text-lg">Change password</h2>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <x-input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
            <x-input name="password" type="password" label="New password" hint="At least 8 characters." required autocomplete="new-password" />
            <x-input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
            <button type="submit" class="btn-navy">Update password</button>
        </form>
    </div>
</div>
@endsection
