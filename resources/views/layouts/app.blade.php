<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
  {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" /> --}}
  @livewireStyles

  <!-- Scripts -->
  {{-- @mix(['resources/css/app.css', 'resources/js/app.js']) --}}
  <script src="{{ asset('js/app.js') }}" defer></script>
  <!-- Agregar en el head de la vista -->
  {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
  {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script> --}}

</head>

<body class="font-sans antialiased">
  <x-jet-banner />

  <div class="min-h-screen bg-gray-200">
    @livewire('navigation-menu')

    <!-- Page Heading -->
    @if (isset($header))
      <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
          {{ $header }}
        </div>
      </header>
    @endif

    <!-- Page Content -->
    <main class="container mx-auto mt-2">
      {{ $slot }}
    </main>
  </div>

  @include('footer')

  @stack('modals')

  @livewireScripts

  <!-- Sección de scripts -->
  @stack('scripts')

</body>

</html>
