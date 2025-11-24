<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Canonical -->
  <link rel="canonical" href="{{ url()->current() }}" />

  <meta name="description" content="@yield('metaDescription', $metaDescription ?? 'Portal del folklore argentino')">
  <meta name="author" content="Placasur SA">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- .ico -->
  <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" type="image/x-icon">

  <title>@yield('metaTitle', $metaTitle ?? config('app.name', 'Mi Folklore Argentino'))</title>

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="@yield('metaTitle', $metaTitle ?? config('app.name', 'Mi Folklore Argentino'))">
  <meta property="og:description"
    content="@yield('metaDescription', $metaDescription ?? 'Portal del folklore argentino')">
  <meta property="og:image" content="@yield('metaImage', asset('img/logo-share.jpg'))">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ url()->current() }}">
  <meta property="twitter:title"
    content="@yield('metaTitle', $metaTitle ?? config('app.name', 'Mi Folklore Argentino'))">
  <meta property="twitter:description"
    content="@yield('metaDescription', $metaDescription ?? 'Portal del folklore argentino')">
  <meta property="twitter:image" content="@yield('metaImage', asset('img/logo-share.jpg'))">

  @vite(['resources/sass/app.scss', 'resources/js/app.js'])

</head>

<body>

  @include('layouts.partials.header')

  <div class="mt-4 mb-4">
    @yield('content')
  </div>

  @include('layouts.partials.footer')

</body>

</html>

{{--
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Styles -->
  @livewireStyles
</head>

<body>
  <div class="font-sans text-gray-900 antialiased">
    {{ $slot }}
  </div>

  @livewireScripts
</body>

</html> --}}