@extends('layouts.auth')

@section('title', 'Log in')

@section('panel-title', 'Welcome back on the road.')
@section('panel-text', 'Log in to review places you visited or to manage the listings you own.')
@section('panel-points')
    <li class="flex items-center gap-3"><x-ui.icon name="star" class="size-5 text-accent" /> Rate and review the places you used</li>
    <li class="flex items-center gap-3"><x-ui.icon name="building" class="size-5 text-accent" /> Update your business listing</li>
    <li class="flex items-center gap-3"><x-ui.icon name="flag" class="size-5 text-accent" /> Report anything that looks wrong</li>
@endsection

@section('content')
<h1 class="text-5xl">Log in</h1>
<p class="mt-2 text-muted">New to AutoKonek? <a href="{{ route('register') }}" class="font-semibold text-steel underline underline-offset-2">Create an account</a></p>

<form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
    @csrf
    <x-form.input name="email" type="email" label="Email" required autocomplete="email" autofocus />
    <x-form.input name="password" type="password" label="Password" required autocomplete="current-password" />
    <x-form.checkbox name="remember">Keep me signed in on this device</x-form.checkbox>
    <button type="submit" class="btn-primary btn-lg w-full">Log in</button>
</form>
@endsection
