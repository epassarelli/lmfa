<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @livewireStyles

    <!-- Scripts -->
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <script src="{{ asset('js/app.js') }}" defer></script>

</head>

<body class="font-sans antialiased">
    <x-jet-banner />

    <div class="min-h-screen bg-gray-100">
        @livewire('navigation-menu')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    {{-- <footer class="bg-gray-800 text-gray-400 py-6">
        <div class="container mx-auto flex justify-between px-4">
            <div class="flex items-center">
                <img src="/img/logo.png" alt="Logo de la empresa" class="h-10 mr-4">
                <span class="font-bold text-lg">MiPortalMusical.com</span>
            </div>
            <div class="flex items-center">
                <a href="#" class="mr-6 hover:text-gray-200">Acerca de</a>
                <a href="#" class="mr-6 hover:text-gray-200">Contacto</a>
                <a href="#" class="mr-6 hover:text-gray-200">Términos y condiciones</a>
                <a href="#" class="hover:text-gray-200">Política de privacidad</a>
            </div>
        </div>
    </footer> --}}
    @include('footer')

    @stack('modals')

    @livewireScripts

    <!-- Sección de scripts -->
    @stack('scripts')

</body>

</html>
