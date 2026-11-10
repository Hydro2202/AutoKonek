<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('autokonek.name')) | {{ config('autokonek.name') }}</title>
    <meta name="description" content="@yield('description', 'AutoKonek helps drivers find gas stations, car washes, repair shops, tire shops, parking and battery services.')">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    {{-- Tells the CSS a page transition is in progress, before the first paint (see route-transition.js). --}}
    <script>try{if(sessionStorage.getItem('ak-rt'))document.documentElement.classList.add('rt-pending')}catch(e){}</script>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class') min-h-screen">
    @yield('body')

    <x-ui.flash />
    <x-ui.confirm-dialog />
    <x-layout.route-transition />
</body>
</html>
