@extends('layouts.site')

@section('title', 'Not available for this account')

@section('content')
@php $user = auth()->user(); @endphp
<div class="mx-auto flex min-h-[60vh] max-w-2xl flex-col items-start justify-center px-4 py-16 sm:px-6">
    <span class="sign size-14"><x-ui.icon name="lock" class="size-7" /></span>
    <h1 class="mt-5 text-5xl">This page is for a different account</h1>

    @if ($user)
        <p class="mt-3 text-lg leading-relaxed text-ink/80">
            You are signed in as <strong class="text-navy">{{ $user->name }}</strong> ({{ $user->roleLabel() }}).
            This area is only for accounts with the right role, such as administrators.
        </p>
    @else
        <p class="mt-3 text-lg leading-relaxed text-ink/80">You do not have permission to open this page.</p>
    @endif

    <div class="mt-7 flex flex-wrap gap-3">
        @if ($user)
            <a href="{{ route('dashboard') }}" class="btn-primary btn-lg">Go to my dashboard</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-outline btn-lg">Log out and switch account</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-primary btn-lg">Log in</a>
            <a href="{{ route('home') }}" class="btn-outline btn-lg">Back to the home page</a>
        @endif
    </div>
</div>
@endsection
