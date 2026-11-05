@extends('layouts.site')

@section('title', 'Privacy Policy')
@section('description', 'How AutoKonek collects, uses and protects your personal information.')

@php
    $email = config('autokonek.contact_email');
    $sections = [
        'who-we-are' => 'Who we are',
        'what-we-collect' => 'What we collect',
        'how-we-use' => 'How we use it',
        'location-maps' => 'Location and maps',
        'legal-basis' => 'Legal basis',
        'sharing' => 'Who can see it',
        'retention' => 'How long we keep it',
        'your-rights' => 'Your rights',
        'security' => 'Security',
        'children' => 'Children',
        'cookies' => 'Cookies',
        'changes' => 'Changes to this policy',
        'contact' => 'Contact us',
    ];
@endphp

@section('content')
<x-legal.page title="Privacy Policy" :sections="$sections"
              summary="We collect only what we need to run your account and listings, we never sell it, and you can ask to see, correct or delete it at any time.">

    <h2 id="who-we-are">Who we are</h2>
    <p>AutoKonek is a web-based automotive services directory operated from {{ config('autokonek.location') }}. In this policy, "we", "us" and "AutoKonek" mean the people who run the website. AutoKonek is a college final project.</p>
    <p>This policy explains what personal information we collect, why, and the choices you have.</p>

    <h2 id="what-we-collect">What we collect</h2>
    <h3>Information you give us</h3>
    <ul>
        <li><strong>Account details:</strong> your name, email address, optional phone number, and your password. Passwords are stored only as a one-way hash, so nobody at AutoKonek can read them.</li>
        <li><strong>Account type:</strong> whether you registered as a customer or a business owner.</li>
        <li><strong>Reviews and reports:</strong> star ratings, written comments, and any report you send about a listing or review.</li>
        <li><strong>Business listings (owners):</strong> establishment name, category, description, the city or municipality, barangay and street address, phone, email, opening hours, services, prices, optional GPS coordinates of the business, and the photos you upload.</li>
        <li><strong>Messages:</strong> your name, email and message when you use the Contact page.</li>
    </ul>

    <h3>Information collected automatically</h3>
    <ul>
        <li>A session identifier, your IP address and your browser's user-agent text, stored with your login session so we can keep you signed in and protect your account.</li>
        <li>Standard web server logs (such as the pages requested and the time), used to find and fix errors and abuse.</li>
    </ul>

    <h3>What we do not collect</h3>
    <p>We do not collect payment details, government ID numbers or your precise location. We do not use advertising trackers or third-party analytics.</p>

    <h2 id="how-we-use">How we use it</h2>
    <ul>
        <li>To create and run your account and let you sign in.</li>
        <li>To show listings, ratings and reviews to people searching for services.</li>
        <li>To review new and changed listings and to moderate reviews and reports.</li>
        <li>To keep the site secure, prevent abuse and fix problems.</li>
        <li>To read and reply to messages you send us.</li>
    </ul>
    <p>We do not use your information for automated decisions or for marketing emails.</p>

    <h2 id="location-maps">Location and maps</h2>
    <ul>
        <li><strong>Your device location.</strong> If you press "Near me" or "How far is it?", your browser first asks your permission and then shares your position with AutoKonek's page. For "Near me", your coordinates are sent to our server once to sort the results by distance. We do not save them to your account, but they are part of the page address, so they can appear in standard web server logs. "How far is it?" is worked out in your browser and is not sent to us.</li>
        <li><strong>Directions.</strong> "Get directions", "Open in Waze" and "OpenStreetMap" are links. They open a map service in a new tab, and from then on that service's own privacy policy applies. We tell the service only where the business is, not where you are.</li>
        <li><strong>Embedded map.</strong> The map on a listing page is provided by OpenStreetMap and loads only after you press "Show map". When it loads, OpenStreetMap receives your IP address and browser details.</li>
        <li><strong>Business pins.</strong> Business owners can add GPS coordinates of their business. These are public, because they are shown on the listing.</li>
    </ul>

    <h2 id="legal-basis">Legal basis</h2>    <p>We handle personal information in line with the Philippine Data Privacy Act of 2012 (Republic Act No. 10173) and its implementing rules. We process your information because you gave consent when you registered or wrote to us, and because we need it to provide the service you asked for.</p>

    <h2 id="sharing">Who can see it</h2>
    <ul>
        <li><strong>The public</strong> can see business listings and the reviews you post. A review shows your name and the date, not your email or phone number.</li>
        <li><strong>Administrators</strong> can see account details, reviews, reports and contact messages, only to run and moderate the service.</li>
        <li><strong>Business owners</strong> can see the reviews posted on their own establishments (your name, rating and comment). They cannot see who reported them.</li>
    </ul>
    <p>We do not sell or rent your personal information. We may disclose it if the law requires, or to protect people from harm. If AutoKonek is hosted by a service provider, that provider processes data only on our instructions.</p>

    <h2 id="retention">How long we keep it</h2>
    <ul>
        <li>Account information is kept while your account exists.</li>
        <li>You can delete your own reviews at any time. If an account is removed, its reviews and reports are removed with it.</li>
        <li>Contact messages are kept only as long as needed to answer them.</li>
        <li>Login session records expire automatically after 2 hours of inactivity.</li>
    </ul>

    <h2 id="your-rights">Your rights</h2>
    <p>Under the Data Privacy Act you have the right to:</p>
    <ul>
        <li>be informed about how your data is processed;</li>
        <li>access the personal information we hold about you;</li>
        <li>object to processing, or withdraw your consent;</li>
        <li>correct inaccurate or outdated information (you can edit most of it yourself on your Profile page);</li>
        <li>have your data blocked, removed or destroyed, where it is no longer needed or was unlawfully obtained;</li>
        <li>receive a copy of your data in a common format;</li>
        <li>claim compensation for damages caused by misuse of your data.</li>
    </ul>
    <p>To use any of these rights, email <a href="mailto:{{ $email }}">{{ $email }}</a>. We will answer within a reasonable time. If you are unhappy with our response, you may complain to the Philippine National Privacy Commission at <a href="https://privacy.gov.ph" rel="noopener noreferrer" target="_blank">privacy.gov.ph</a>.</p>

    <h2 id="security">Security</h2>
    <p>We protect your information with hashed passwords, protection against forged form submissions, role-based access so customers and business owners cannot reach administrator tools, and checks on every file upload. No website can promise perfect security, so please choose a strong, unique password.</p>

    <h2 id="children">Children</h2>
    <p>AutoKonek is not meant for people under 18. If you believe a minor has created an account, contact us and we will remove it.</p>

    <h2 id="cookies">Cookies</h2>
    <p>We use only the cookies needed to keep you signed in and to protect our forms. See the <a href="{{ route('legal.cookies') }}">Cookie Policy</a> for the full list.</p>

    <h2 id="changes">Changes to this policy</h2>
    <p>If we change this policy in a way that matters, we will update the date at the top of this page and, where appropriate, tell you when you next sign in.</p>

    <h2 id="contact">Contact us</h2>
    <p>Questions about privacy? Email <a href="mailto:{{ $email }}">{{ $email }}</a> or write through our <a href="{{ route('contact') }}">Contact page</a>.</p>
</x-legal.page>
@endsection
