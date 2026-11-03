<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AutoKonek') | AutoKonek</title>
    <meta name="description" content="@yield('description', 'AutoKonek helps vehicle owners find gas stations, car washes, repair shops, tire shops, parking and battery services.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    @yield('body')
    <x-confirm-dialog />
</body>
</html>
