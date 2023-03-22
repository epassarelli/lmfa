<x-app-layout>

    <div class="w-full px-4">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
    </div>

    <div class="flex flex-wrap -mx-4 p-4">
        @foreach ($shows as $evento)
            <div class="w-full md:w-1/4 px-4 mb-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="relative">
                        <img class="w-full" src="{{ asset('storage/interpretes/' . $evento->interprete->foto) }}"
                            alt="{{ $evento->interprete->interprete }}">
                        <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-black to-transparent">
                        </div>
                        <div class="absolute bottom-0 left-0 p-4">
                            <h2 class="text-white text-xl font-bold">{{ $evento->titulo }}</h2>
                            <p class="text-gray-300">{{ $evento->interprete->interprete }}</p>
                        </div>
                    </div>
                    <div class="p-4">
                        <p class="text-gray-600 text-sm">{{ \Carbon\Carbon::parse($evento->fecha)->format('Y-m-d') }}
                        </p>
                        <p class="text-gray-800 text-lg font-bold mb-2">{{ $evento->titulo }}</p>
                        <p class="text-gray-700">{{ $evento->interprete->interprete }}</p>
                        <p class="text-gray-600">{{ $evento->lugar }}, {{ $evento->lugar }},
                            {{ $evento->lugar }}, {{ $evento->direccion }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>


    <!-- Links del paginado -->
    <div class="flex justify-center p-4">
        <div class="mt-8">
            {{ $shows->links() }}
        </div>
    </div>

</x-app-layout>
