@extends('layouts.site')

@section('title', 'Terms of Service')
@section('description', 'The rules for using AutoKonek as a customer or a business owner.')

@php
    $email = config('autokonek.contact_email');
    $sections = [
        'agreement' => 'Using AutoKonek',
        'accounts' => 'Your account',
        'customers' => 'Reviews and reports',
        'owners' => 'Business owners',
        'content' => 'Your content',
        'conduct' => 'What is not allowed',
        'moderation' => 'Moderation',
        'payments' => 'No payments or refunds',
        'accuracy' => 'Accuracy of information',
        'availability' => 'Availability',
        'liability' => 'Limits of liability',
        'ending' => 'Ending your account',
        'law' => 'Governing law',
        'changes' => 'Changes to these terms',
        'contact' => 'Contact us',
    ];
@endphp

@section('content')
<x-legal.page title="Terms of Service" :sections="$sections"
              summary="Be honest, be respectful, and only list a business you are allowed to represent. AutoKonek is free, takes no payments, and checks listings before they go public.">

    <h2 id="agreement">Using AutoKonek</h2>
    <p>By creating an account or using AutoKonek you agree to these terms and to our <a href="{{ route('legal.privacy') }}">Privacy Policy</a> and <a href="{{ route('legal.cookies') }}">Cookie Policy</a>. If you do not agree, please do not use the service. You must be at least 18 years old.</p>

    <h2 id="accounts">Your account</h2>
    <ul>
        <li>Give accurate information when you register and keep it up to date.</li>
        <li>Keep your password private. You are responsible for what happens under your account.</li>
        <li>Choose the account type that fits you: <strong>customer</strong> or <strong>business owner</strong>. Administrator accounts are created by the AutoKonek team only.</li>
        <li>Tell us right away if you think someone else has used your account.</li>
    </ul>

    <h2 id="customers">Reviews and reports</h2>
    <ul>
        <li>Write reviews only about places you have actually used, and describe your own experience honestly.</li>
        <li>You may post one review per establishment. You can edit or delete it at any time.</li>
        <li>Do not review a business you own, work for or compete with.</li>
        <li>Use the Report button for listings or reviews that are wrong, abusive or spam. Do not file reports in bad faith.</li>
    </ul>

    <h2 id="owners">Business owners</h2>
    <ul>
        <li>List only a business you own or are authorized to represent.</li>
        <li>Everything you submit (name, address, contact details, hours, services, prices, photos) must be accurate and not misleading.</li>
        <li>New listings are <strong>pending</strong> until an administrator approves them. If a listing is rejected, you will see the reason and can correct it and resubmit.</li>
        <li>If you change the details of an approved listing, it returns to pending until the changes are approved. Services and photos can be changed without a new approval.</li>
        <li>Only upload photos you have the right to use. Images must not be offensive or contain other people's personal information.</li>
    </ul>

    <h2 id="content">Your content</h2>
    <p>You keep ownership of what you post. By posting it, you give AutoKonek a free, non-exclusive right to store and display it on the service so that it can do its job. This right ends when you delete the content, except for copies that must be kept for a short time for backup or legal reasons.</p>

    <h2 id="conduct">What is not allowed</h2>
    <ul>
        <li>Fake, paid-for or retaliatory reviews, or attempts to manipulate ratings.</li>
        <li>Harassment, hate speech, threats, or sharing someone's private information.</li>
        <li>Spam, advertising unrelated to the listing, or links to harmful sites.</li>
        <li>Pretending to be another person or business.</li>
        <li>Trying to access other people's accounts, the administrator area, or data you are not allowed to see, or disrupting the service.</li>
        <li>Uploading files that are not genuine images.</li>
    </ul>

    <h2 id="moderation">Moderation</h2>
    <p>Administrators may approve, reject, edit the visibility of, or remove listings and reviews, and may deactivate accounts that break these terms. Deactivated accounts cannot sign in. We aim to be fair and, for listings, to explain the reason.</p>

    <h2 id="payments">No payments or refunds</h2>
    <p>AutoKonek is free to use and does not take payments, bookings or orders. Because we do not charge you anything, there is nothing for us to refund. Any payment you make to a business is a matter between you and that business, under that business's own terms and refund policy.</p>

    <h2 id="accuracy">Accuracy of information</h2>
    <p>Listings, prices, opening hours and reviews come from businesses and users, not from AutoKonek. We check listings before they are published but cannot guarantee that every detail is correct or current. Please confirm important details, such as prices and hours, with the business before you go.</p>
    <p>Maps and directions are provided by third-party services and a business's pin may be approximate. Check the route and road conditions yourself, and follow traffic laws. Do not use a map while driving.</p>

    <h2 id="availability">Availability</h2>
    <p>We try to keep AutoKonek running but do not promise it will always be available or error-free. We may change or pause features, for example for maintenance.</p>

    <h2 id="liability">Limits of liability</h2>
    <p>AutoKonek is provided "as is". To the extent allowed by law, we are not responsible for losses that result from relying on listings or reviews, from dealings with a business you found here, or from the service being unavailable. Nothing in these terms limits rights you have under Philippine law that cannot be limited.</p>

    <h2 id="ending">Ending your account</h2>
    <p>You can stop using AutoKonek at any time, and you can ask us to delete your account through the <a href="{{ route('contact') }}">Contact page</a>. We may suspend or end accounts that break these terms.</p>

    <h2 id="law">Governing law</h2>
    <p>These terms are governed by the laws of the Republic of the Philippines.</p>

    <h2 id="changes">Changes to these terms</h2>
    <p>If we update these terms, we will change the date at the top. If you keep using AutoKonek after a change, you accept the updated terms.</p>

    <h2 id="contact">Contact us</h2>
    <p>Questions about these terms? Email <a href="mailto:{{ $email }}">{{ $email }}</a>.</p>
</x-legal.page>
@endsection
