@extends('layouts.app')
@section('title', 'Avisos Clasificados del Folklore Argentino')
@section('meta_description', 'Comprá, vendé y encontrá servicios del mundo del folklore argentino. Instrumentos, indumentaria, clases de danza y más.')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-[#8B4513] mb-2">
                🎶 Clasificados del Folklore
            </h1>
            <p class="text-gray-600">Comprá, vendé y conectá con el mundo del folklore argentino.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @auth
                <a href="{{ route('classifieds.create') }}" class="bg-[#ff661f] hover:bg-orange-600 text-white font-bold py-2 px-6 rounded shadow-sm transition">
                    + Publicar Aviso Gratis
                </a>
                <a href="{{ route('classifieds.mis-avisos') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2 px-6 rounded shadow-sm transition">
                    Mis Avisos
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-[#ff661f] hover:bg-orange-600 text-white font-bold py-2 px-6 rounded shadow-sm transition">
                    + Publicar Aviso Gratis
                </a>
            @endauth
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('classifieds.index') }}" class="bg-white p-4 rounded-lg shadow-sm mb-8 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-4">
                <input type="text" name="q" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#ff661f] focus:ring focus:ring-orange-200 focus:ring-opacity-50 py-2 px-3" placeholder="Buscar avisos..." value="{{ request('q') }}">
            </div>
            <div class="md:col-span-3">
                <select name="categoria" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#ff661f] focus:ring focus:ring-orange-200 focus:ring-opacity-50 py-2 px-3 text-gray-700 bg-white">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ request('categoria') == $cat->slug ? 'selected' : '' }}>
                            {{ $cat->icon ?? '' }} {{ $cat->name }}
                            @if($cat->classifieds_count) ({{ $cat->classifieds_count }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <input type="text" name="provincia" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#ff661f] focus:ring focus:ring-orange-200 focus:ring-opacity-50 py-2 px-3" placeholder="Provincia / Ciudad" value="{{ request('provincia') }}">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition">Buscar</button>
            </div>
        </div>
    </form>

    {{-- Categorías rápidas --}}
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('classifieds.index') }}" class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ !$selectedCategory ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Todos
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('classifieds.index', ['categoria' => $cat->slug]) }}"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ $selectedCategory == $cat->slug ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                {{ $cat->icon ?? '' }} {{ $cat->name }}
            </a>
        @endforeach
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Grid de avisos --}}
    @if($classifieds->isEmpty())
        <div class="text-center py-20 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-gray-500 text-lg mb-6">No hay avisos en esta categoría todavía. ¡Sé el primero en publicar!</p>
            <a href="{{ route('classifieds.create') }}" class="bg-[#ff661f] hover:bg-orange-600 text-white font-bold py-2 px-6 rounded shadow-sm inline-block">Publicar Aviso</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($classifieds as $aviso)
            <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col h-full border {{ $aviso->is_featured ? 'border-yellow-400 ring-2 ring-yellow-400 ring-opacity-50' : 'border-gray-100 hover:shadow-lg transition-shadow duration-300' }} relative">
                
                @if($aviso->is_featured)
                    <div class="absolute top-0 right-0 m-3 z-10">
                        <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">⭐ Destacado</span>
                    </div>
                @endif

                <a href="{{ route('classifieds.show', $aviso->slug) }}" class="block w-full h-48 bg-gray-100 relative overflow-hidden group">
                    @if($aviso->images->isNotEmpty())
                        <x-optimized-image :image="$aviso->images->first()" variant="card" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                    @else
                        <div class="w-full h-full flex items-center justify-center text-6xl opacity-50 transition-transform duration-500 group-hover:scale-110">
                            {{ $aviso->category->icon ?? '📦' }}
                        </div>
                    @endif
                </a>

                <div class="p-5 flex-grow flex flex-col">
                    <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-md mb-3 self-start font-medium">{{ $aviso->category->icon ?? '' }} {{ $aviso->category->name }}</span>
                    <a href="{{ route('classifieds.show', $aviso->slug) }}" class="block group">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $aviso->title }}</h3>
                    </a>
                    <p class="text-gray-500 text-sm line-clamp-3 mb-4">{{ Str::limit($aviso->description, 100) }}</p>
                </div>

                <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center mt-auto">
                    <div>
                        @if($aviso->price)
                            <span class="text-lg font-bold text-green-600">$ {{ is_numeric($aviso->price) ? number_format((float)$aviso->price, 0, ',', '.') : $aviso->price }}</span>
                        @else
                            <span class="text-sm font-medium text-gray-500">Precio a consultar</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500 flex items-center justify-end">
                            <span class="mr-1">📍</span> <span class="truncate max-w-[100px]" title="{{ $aviso->location }}">{{ $aviso->location }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $classifieds->links() }}
        </div>
    @endif
</div>
@endsection
