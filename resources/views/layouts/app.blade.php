<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @if (!app()->environment('local'))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Q4QNW9JPGG"></script>

    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7042088525718719"
      crossorigin="anonymous"></script>
  @endif
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Passarelli Eduardo">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Canonical -->
  <link rel="canonical" href="{{ url()->current() }}" />

  <!-- .ico -->
  <link rel="shortcut icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">

  <title>@yield('metaTitle', 'Mi folklore Argentino')</title>
  <meta name="description" content="@yield('metaDescription', 'Descubre el rico folklore argentino en nuestro portal')">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="@yield('metaTitle', 'Mi folklore Argentino')">
  <meta property="og:description"
    content="@yield('metaDescription', 'Descubre el rico folklore argentino en nuestro portal')">
  <meta property="og:image" content="@yield('metaImage', asset('img/logo-share.jpg'))">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ url()->current() }}">
  <meta property="twitter:title" content="@yield('metaTitle', 'Mi folklore Argentino')">
  <meta property="twitter:description"
    content="@yield('metaDescription', 'Descubre el rico folklore argentino en nuestro portal')">
  <meta property="twitter:image" content="@yield('metaImage', asset('img/logo-share.jpg'))">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  @yield('styles')
  @stack('json-ld')
</head>

<body class="bg-gray-100 text-gray-900">

  @include('layouts.partials.header')

  <main class="container mx-auto grid grid-cols-1 lg:grid-cols-12 mt-6">

    {{-- Contenido principal --}}
    <div class="lg:col-span-9 px-4 mb-4">
      @yield('content')
    </div>

    {{-- Sidebar dinámico o por defecto --}}
    <aside class="lg:col-span-3 px-4 mb-4">
      @hasSection('sidebar')
        @yield('sidebar')
      @else
        @include('layouts.partials.sidebar.default')
      @endif
    </aside>

  </main>

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