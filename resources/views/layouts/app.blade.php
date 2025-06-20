<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @if (!app()->environment('local'))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Q4QNW9JPGG"></script>

    <!-- Google AdSense -->
    {{-- <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7042088525718719"
      crossorigin="anonymous"></script> --}}
  @endif
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Passarelli Eduardo">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- .ico -->
  <link rel="shortcut icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">
  <title>@yield('metaTitle', 'Mi folklore Argentino')</title>
  <meta name="description" content="@yield('metaDescription', 'Descubre el rico folklore argentino en nuestro portal')">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  @yield('styles')

</head>

<body>

  @include('layouts.partials.header')

  <div class="mt-4 mb-4">

    @yield('content')

  </div>

  @include('layouts.partials.footer')

  @yield('scripts')

  @if (!app()->environment('local'))
    <!-- Google tag (gtag.js) -->
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }
      gtag('js', new Date());

      gtag('config', 'G-Q4QNW9JPGG');
    </script>
  @endif

</body>

</html>
