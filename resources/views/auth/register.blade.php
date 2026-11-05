@extends('layouts.auth')

@section('title', 'Create account')

@section('panel-title', 'Your next stop starts here.')
@section('panel-text', 'One account lets you review services as a driver, or list and manage your own business.')
@section('panel-points')
    <li class="flex items-center gap-3"><x-ui.icon name="circle-check" class="size-5 text-accent" /> Free for drivers and business owners</li>
    <li class="flex items-center gap-3"><x-ui.icon name="circle-check" class="size-5 text-accent" /> Every listing is checked before it goes live</li>
    <li class="flex items-center gap-3"><x-ui.icon name="circle-check" class="size-5 text-accent" /> You control the information you share</li>
@endsection

@section('content')
<h1 class="text-5xl">Create account</h1>
<p class="mt-2 text-muted">Already registered? <a href="{{ route('login') }}" class="font-semibold text-steel underline underline-offset-2">Log in</a></p>

<form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
    @csrf
    <fieldset>
        <legend class="label">I want to</legend>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ([
                'customer' => ['Find services', 'Search, compare and review.', 'search'],
                'business_owner' => ['List my business', 'Submit and manage listings.', 'building'],
            ] as $value => [$title, $text, $icon])
                <label class="flex cursor-pointer gap-3 rounded-md border border-line-strong bg-white p-3.5 hover:border-navy has-[:checked]:border-navy has-[:checked]:bg-steel-50 has-[:checked]:shadow-[inset_3px_0_0_var(--color-accent)] has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-steel">
                    <input type="radio" name="account_type" value="{{ $value }}" class="sr-only" @checked(old('account_type', 'customer') === $value) required>
                    <span class="sign size-9"><x-ui.icon :name="$icon" class="size-5" /></span>
                    <span><span class="block font-semibold text-navy">{{ $title }}</span><span class="block text-sm text-muted">{{ $text }}</span></span>
                </label>
            @endforeach
        </div>
        @error('account_type') <p class="error-text">{{ $message }}</p> @enderror
    </fieldset>

    <x-form.input name="name" label="Full name" required autocomplete="name" />
    <x-form.input name="email" type="email" label="Email" required autocomplete="email" />
    <x-form.input name="phone" type="tel" label="Phone (optional)" autocomplete="tel" />
    <div class="grid gap-5 sm:grid-cols-2">
        <x-form.input name="password" type="password" label="Password" hint="At least 8 characters." required autocomplete="new-password" />
        <x-form.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />
    </div>

    <x-form.checkbox name="terms" required>
        I agree to the <a href="{{ route('legal.terms') }}" target="_blank" class="font-semibold text-steel underline underline-offset-2">Terms of Service</a>
        and have read the <a href="{{ route('legal.privacy') }}" target="_blank" class="font-semibold text-steel underline underline-offset-2">Privacy Policy</a>.
    </x-form.checkbox>

    <button type="submit" class="btn-primary btn-lg w-full">Create account</button>
</form>
@endsection
