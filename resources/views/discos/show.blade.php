<x-app-layout>


    <div class="w-full px-4">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
    </div>


    <div class="flex flex-col items-center">
        <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->titulo }}"
            class="w-64 h-64 object-cover rounded-lg">
        <h1 class="mt-4 text-2xl font-bold">{{ $disco->titulo }}</h1>
        <p class="text-lg">{{ $disco->interprete->interprete }} ({{ $disco->anio }})</p>
        <div class="mt-8">
            <iframe src="https://open.spotify.com/embed/playlist/{{ $disco->spotify }}" width="100%" height="380"
                frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
        </div>
        <div class="mt-8">
            <h2 class="text-lg font-bold">Canciones:</h2>
            <ul class="mt-4 list-disc list-inside">
                @foreach ($disco->canciones as $cancion)
                    <li><a href="{{ route('canciones.show', $cancion) }}">{{ $cancion->titulo }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>




    <div class="container mx-auto py-8">
        <div class="flex flex-col md:flex-row">
            <div class="w-full md:w-1/3">
                <img src="{{ asset('storage/albunes/' . $disco->foto) }}"
                    alt="{{ $disco->titulo }} - {{ $disco->interprete }}" class="rounded-lg shadow-md">
            </div>
            <div class="w-full md:w-2/3 md:pl-8">
                <h1 class="text-4xl font-bold mb-4">{{ $disco->titulo }}</h1>
                <p class="text-lg mb-4"><span class="font-bold">Año:</span> {{ $disco->anio }}</p>
                <p class="text-lg mb-4"><span class="font-bold">Intérprete:</span> <a
                        href="{{ route('interprete.show', $disco->interprete_id) }}">{{ $disco->interprete->interprete }}</a>
                </p>
                <div class="mb-4">
                    <iframe src="https://open.spotify.com/embed/playlist/{{ $disco->spotify }}" width="100%"
                        height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
                </div>
                <h2 class="text-2xl font-bold mb-4">Canciones</h2>
                <ul>
                    @foreach ($disco->canciones as $cancion)
                        <li class="text-lg mb-2"><a
                                href="{{ route('cancion.show', $cancion->id) }}">{{ $cancion->titulo }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>





</x-app-layout>

{{-- <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                <div class="flex justify-center">
                    <img src="{{ $album->cover_photo }}" alt="{{ $album->title }}" class="w-64 h-64 rounded-lg">
                </div>
                <div class="mt-4">
                    <h1 class="text-2xl font-bold">{{ $album->title }}</h1>
                    <p class="text-sm text-gray-500">{{ $album->year }}</p>
                    <p class="mt-2"><a href="{{ route('artist.show', $album->artist_id) }}" class="text-blue-500">{{ $album->artist->name }}</a></p>
                </div>
</ --}}