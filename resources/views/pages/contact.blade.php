@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl sm:text-3xl">Contact or send feedback</h1>
    <p class="mt-1 text-sm text-muted">Questions, suggestions or problems? Send us a message and an administrator will read it.</p>

    <div class="card mt-6 p-6">
        <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="name" label="Your name" :value="auth()->user()?->name" required />
                <x-input name="email" type="email" label="Email" :value="auth()->user()?->email" required />
            </div>
            <x-input name="subject" label="Subject" required />
            <x-textarea name="message" label="Message" rows="6" required />
            <button type="submit" class="btn-primary">Send message</button>
        </form>
    </div>
</div>
@endsection
