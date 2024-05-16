<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Placasur">
    <meta name="author" content="Placasur SA">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- .ico -->
    <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" type="image/x-icon">
    <title>{{ config('app.name', 'PlacaSUR') }} - @yield('title')</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

</head>

<body>

    @include('layouts.partials.header')

    <div class="min-vh-100 mb-5">
        @yield('content')
    </div>

    @include('layouts.partials.footer')

</body>

</html>