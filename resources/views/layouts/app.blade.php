<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @if (!app()->environment('local'))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Q4QNW9JPGG"></script>

    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }
      gtag('js', new Date());

      gtag('config', 'G-Q4QNW9JPGG');
    </script>
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

  <!-- Preconexión con el host de fuentes -->
  <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>

  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  <style>
    /* 1. Reservar altura mínima para el header */
    header.sticky-top {
      min-height: 60px;
      /* Ajustalo si tu header es más alto */
    }

    /* 2. Tamaño fijo para el botón hamburguesa */
    .navbar-toggler {
      width: 40px;
      height: 40px;
      padding: 0.25rem;
    }

    /* 3. Transiciones suaves para evitar saltos visuales */
    .navbar-nav .nav-link {
      transition: color 0.3s ease, background-color 0.3s ease, padding 0.3s ease;
    }

    .navbar-nav .nav-item.active .nav-link {
      transition: color 0.3s ease, background-color 0.3s ease, padding 0.3s ease;
    }

    .youtube-placeholder {
      position: relative;
      cursor: pointer;
      overflow: hidden;
      border-radius: 0.5rem;
    }

    .youtube-play-button {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: rgba(0, 0, 0, 0.5);
      padding: 0.75rem;
      border-radius: 0.75rem;
      transition: background-color 0.3s ease;
    }

    .youtube-placeholder:hover .youtube-play-button {
      background-color: rgba(0, 0, 0, 0.7);
    }
  </style>

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
