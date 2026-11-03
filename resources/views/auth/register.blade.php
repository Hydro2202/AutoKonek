@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<div class="mx-auto max-w-lg px-4 py-12 sm:py-16">
    <div class="card p-6 sm:p-8">
        <h1 class="text-2xl">Create your account</h1>
        <p class="mt-1 text-sm text-muted">Join AutoKonek as a customer or a business owner.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf
            <fieldset>
                <legend class="label">I want to...</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach (['customer' => ['Find services', 'Search listings and write reviews.'], 'business_owner' => ['List my business', 'Submit and manage establishments.']] as $value => [$title, $text])
                        <label class="card flex cursor-pointer items-start gap-3 p-3 has-[:checked]:border-accent has-[:checked]:bg-orange-50">
                            <input type="radio" name="account_type" value="{{ $value }}" class="mt-1" @checked(old('account_type', 'customer') === $value) required>
                            <span><span class="block text-sm font-semibold text-navy">{{ $title }}</span><span class="block text-xs text-muted">{{ $text }}</span></span>
                        </label>
                    @endforeach
                </div>
                @error('account_type') <p class="error-text">{{ $message }}</p> @enderror
            </fieldset>

            <x-input name="name" label="Full name" required autocomplete="name" />
            <x-input name="email" type="email" label="Email" required autocomplete="email" />
            <x-input name="phone" type="tel" label="Phone (optional)" autocomplete="tel" />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="password" type="password" label="Password" hint="At least 8 characters." required autocomplete="new-password" />
                <x-input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />
            </div>
            <button type="submit" class="btn-primary w-full py-2.5">Create account</button>
        </form>
        <p class="mt-5 text-center text-sm text-muted">Already registered? <a href="{{ route('login') }}" class="font-semibold text-steel hover:underline">Log in</a></p>
    </div>
</div>
@endsection
