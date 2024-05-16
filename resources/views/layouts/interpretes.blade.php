<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Mi sitio web de música folklórica</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>

<body class="bg-gray-100">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center">
                        <img class="h-8 w-8" src="{{ asset('img/logo.svg') }}" alt="Mi sitio web de música folklórica">
                        <span class="ml-2 font-bold text-lg text-gray-800">Música folklórica</span>
                    </a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <div class="relative">
                            <input type="text" name="search" id="search"
                                class="block w-full py-2 pr-10 placeholder-gray-500 text-gray-900 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-gray-400 sm:text-sm border-gray-300 rounded-md"
                                placeholder="Buscar...">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <!-- Heroicon name: search -->
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M8.5 16a7.5 7.5 0 100-15 7.5 7.5 0 000 15zm4.78-1.22a5.5 5.5 0 11.707-.707l3.536 3.536a1 1 0 010 1.414l-.354.354a1 1 0 01-1.414 0l-3.536-3.536zm-5.657-5.657a3.5 3.5 0 115 0 3.5 3.5 0 01-5 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <a href="#" class="ml-5 text-gray-600 hover:text-gray-800">{{ auth()->user()->name }}</a>
                        <a href="#" class="ml-5 text-gray-600 hover:text-gray-800">Salir</a>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <!-- Mobile menu button -->
                    <button type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md">
                </div>
            </div>
        </div>
        <div class="md:hidden" x-show="open">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/"
                    class="hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium text-gray-900">Inicio</a>
                <a href="/interpretes"
                    class="bg-gray-200 block px-3 py-2 rounded-md text-base font-medium text-gray-900">Intérpretes</a>
                <a href="/fiestas-y-festivales"
                    class="hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium text-gray-900">Fiestas y
                    festivales</a>
                <a href="/mitos-y-leyendas"
                    class="hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium text-gray-900">Mitos y
                    leyendas</a>
                <a href="/recetas-de-comidas-tipicas"
                    class="hover:bg-gray-100 block px-3 py-2 rounded-md text-base font-medium text-gray-900">Recetas de
                    comidas típicas</a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-5">
                    <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full" src="{{ asset('img/profile.jpg') }}" alt="">
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                    <button
                        class="ml-auto flex-shrink-0 bg-white p-1 border-2 border-transparent rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 focus:bg-gray-100 transition duration-150 ease-in-out"
                        aria-label="Sign out">
                        <!-- Heroicon name: logout -->
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 12a1 1 0 01-1-1V9a1 1 0 112 0v2a1 1 0 01-1 1zm3.293-5.293a1 1 0 010 1.414L12.414 10H16a1 1 0 110 2H12.414l1.879 1.879a1 1 0 01-1.414 1.414l-3.586-3.586a2 2 0 010-2.828l3.586-3.586a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap -mx-4">
            <div class="w-full md:w-3/12 px-4">
                @include('partials.interpreter-header', ['interpreter' => $interpreter])
            </div>
            <div class="w-full md:w-9/12 px-4">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>

</html>
