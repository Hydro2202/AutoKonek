@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<div class="mx-auto max-w-md px-4 py-12 sm:py-16">
    <div class="card p-6 sm:p-8">
        <h1 class="text-2xl">Welcome back</h1>
        <p class="mt-1 text-sm text-muted">Log in to your AutoKonek account.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <x-input name="email" type="email" label="Email" required autocomplete="email" autofocus />
            <x-input name="password" type="password" label="Password" required autocomplete="current-password" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded border-line"> Remember me</label>
            <button type="submit" class="btn-primary w-full py-2.5">Log in</button>
        </form>
        <p class="mt-5 text-center text-sm text-muted">New here? <a href="{{ route('register') }}" class="font-semibold text-steel hover:underline">Create an account</a></p>
    </div>
</div>
@endsection
