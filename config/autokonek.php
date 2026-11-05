<?php

return [

    'name' => 'AutoKonek',

    'tagline' => 'Find the services your vehicle needs.',

    /*
    | Contact details shown on the legal pages and the contact page.
    */
    'contact_email' => env('AUTOKONEK_CONTACT_EMAIL', 'support@autokonek.test'),
    'location' => 'Lucena City, Quezon, Philippines',

    /*
    | Where AutoKonek is based. New listings start with this city selected,
    | and the "near" suggestions on the homepage are written for it.
    */
    'home' => [
        'city' => 'Lucena City',
        'province' => 'Quezon',
    ],

    /*
    | Date shown as "Last updated" on the Privacy Policy, Terms and Cookie Policy.
    | Change it whenever the wording of those pages changes.
    */
    'legal_updated' => '2026-11-05',

    /*
    | Cookie that remembers the visitor's cookie-banner choice (set in the browser).
    */
    'consent_cookie' => 'ak_cookie_consent',

    'images' => [
        'max_per_establishment' => 8,
        'max_per_upload' => 5,
        'max_kilobytes' => 2048,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    'pagination' => [
        'directory' => 9,
        'admin' => 15,
        'owner' => 10,
    ],

];
