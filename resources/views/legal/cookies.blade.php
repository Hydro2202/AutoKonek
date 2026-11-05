@extends('layouts.site')

@section('title', 'Cookie Policy')
@section('description', 'The cookies AutoKonek uses and why.')

@php
    $email = config('autokonek.contact_email');
    $sections = [
        'what' => 'What cookies are',
        'we-use' => 'Cookies we use',
        'we-dont' => 'Cookies we do not use',
        'control' => 'Your choices',
        'changes' => 'Changes to this policy',
        'contact' => 'Contact us',
    ];
    $cookies = [
        [config('session.cookie'), 'Keeps you signed in and carries short messages (such as "Review posted") from one page to the next.', '2 hours of inactivity', 'Strictly necessary'],
        ['XSRF-TOKEN', 'Protects forms from being submitted by other websites on your behalf.', '2 hours', 'Strictly necessary'],
        ['remember_web_…', 'Keeps you signed in on this device. Only set if you tick "Keep me signed in".', '400 days', 'Strictly necessary for that feature'],
        [config('autokonek.consent_cookie'), 'Remembers that you have seen and accepted the cookie notice, so we do not show it on every visit.', '180 days', 'Preference'],
    ];
@endphp

@section('content')
<x-legal.page title="Cookie Policy" :sections="$sections"
              summary="AutoKonek uses only the cookies it needs to sign you in and keep forms safe. There are no advertising, tracking or analytics cookies.">

    <h2 id="what">What cookies are</h2>
    <p>Cookies are small text files that a website stores in your browser. Websites use them to remember who you are between pages, or to remember a choice you made.</p>

    <h2 id="we-use">Cookies we use</h2>
    <p>All of these are set by AutoKonek itself, on our own domain.</p>
    <div class="overflow-x-auto">
        <table>
            <thead><tr><th>Cookie</th><th>What it does</th><th>Lasts</th><th>Type</th></tr></thead>
            <tbody>
                @foreach ($cookies as [$name, $purpose, $lasts, $type])
                    <tr>
                        <td class="whitespace-nowrap font-semibold text-navy">{{ $name }}</td>
                        <td>{{ $purpose }}</td>
                        <td class="whitespace-nowrap">{{ $lasts }}</td>
                        <td>{{ $type }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p>Our fonts, icons and images are served from AutoKonek itself, so no other company sets a cookie on your device while you browse. The exceptions are services you choose to open: the map that loads when you press <strong>Show map</strong> (OpenStreetMap) and the Google Maps, Waze and OpenStreetMap pages opened by the directions buttons. Those sites are not controlled by AutoKonek and have their own cookie policies.</p>

    <h2 id="we-dont">Cookies we do not use</h2>
    <ul>
        <li>Advertising or marketing cookies.</li>
        <li>Analytics or tracking cookies, from us or from third parties.</li>
        <li>Social media buttons or third-party widgets that load by themselves. The map only loads when you press Show map.</li>
    </ul>
    <p>Because every cookie above is needed for the site to work, the notice at the bottom of the page asks you to acknowledge them rather than to choose between categories. If we ever add optional cookies, we will ask for your permission first.</p>

    <h2 id="control">Your choices</h2>
    <ul>
        <li><strong>Cookie settings:</strong> use the "Cookie settings" link in the footer to review your choice or withdraw it.</li>
        <li><strong>Your browser:</strong> you can block or delete cookies in your browser settings. If you block the strictly necessary cookies, you will not be able to log in or submit forms.</li>
    </ul>
    <p><a href="#" data-cookie-settings>Open cookie settings</a></p>

    <h2 id="changes">Changes to this policy</h2>
    <p>If we change the cookies we use, we will update this page and the date at the top.</p>

    <h2 id="contact">Contact us</h2>
    <p>Questions about cookies? Email <a href="mailto:{{ $email }}">{{ $email }}</a>. See also our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>
</x-legal.page>
@endsection
