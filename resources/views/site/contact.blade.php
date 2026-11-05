@extends('layouts.site')

@section('title', 'Contact')

@section('content')
<div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_1.2fr] lg:gap-16">
    <div>
        <h1 class="text-5xl">Questions or feedback?</h1>
        <p class="mt-4 max-w-md text-lg leading-relaxed text-ink/80">Tell us what is working, what is not, or what you would like to see. An administrator reads every message.</p>

        <dl class="mt-8 space-y-5 text-[0.9375rem]">
            <div class="flex gap-3">
                <span class="sign size-10"><x-ui.icon name="mail" class="size-5" /></span>
                <div><dt class="font-semibold text-navy">Email</dt><dd class="text-muted">{{ config('autokonek.contact_email') }}</dd></div>
            </div>
            <div class="flex gap-3">
                <span class="sign size-10"><x-ui.icon name="map-pin" class="size-5" /></span>
                <div><dt class="font-semibold text-navy">Based in</dt><dd class="text-muted">{{ config('autokonek.location') }}</dd></div>
            </div>
        </dl>

        <p class="mt-8 max-w-md text-sm text-muted">To report a wrong listing or an abusive review, use the "Report" button on that page instead, so it goes straight to the moderators.</p>
    </div>

    <div class="card p-6 sm:p-8">
        <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="name" label="Your name" :value="auth()->user()?->name" required autocomplete="name" />
                <x-form.input name="email" type="email" label="Email" :value="auth()->user()?->email" required autocomplete="email" />
            </div>
            <x-form.input name="subject" label="Subject" required />
            <x-form.textarea name="message" label="Message" rows="6" required />
            <p class="text-sm text-muted">We use your details only to read and answer this message. See the <a href="{{ route('legal.privacy') }}" class="font-semibold text-steel underline underline-offset-2">Privacy Policy</a>.</p>
            <button type="submit" class="btn-primary btn-lg">Send message</button>
        </form>
    </div>
</div>
@endsection
