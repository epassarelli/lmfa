@extends('layouts.app')

@section('metaTitle', 'Página no encontrada | Mi Folklore Argentino')

@section('content')
  <div class="flex flex-col items-center justify-center py-16 bg-white rounded-lg shadow-sm px-4 text-center">
    <h1 class="text-9xl font-extrabold text-[#ff661f] animate-bounce">404</h1>
    <h2 class="text-3xl font-bold text-gray-800 mt-4">¡Ups! Parece que te has perdido en el monte</h2>
    <p class="text-gray-600 text-lg mt-4 max-w-md">
      La página que buscas no existe o ha sido movida como una baguala al viento. 
      Prueba buscando lo que necesitas:
    </p>

    <div class="mt-8 w-full max-w-md">
      <form action="{{ route('buscar') }}" method="GET" class="flex border-2 border-orange-200 rounded-full overflow-hidden focus-within:border-orange-500 transition-all">
        <input type="text" name="q" placeholder="¿Qué estás buscando?" class="flex-grow px-6 py-3 outline-none text-gray-700">
        <button type="submit" class="bg-[#ff661f] text-white px-6 py-3 font-bold hover:bg-orange-600 transition-colors">
          Buscar
        </button>
      </form>
    </div>

    <div class="mt-12 flex flex-wrap justify-center gap-4">
      <a href="{{ url('/') }}" class="px-6 py-2 bg-gray-800 text-white rounded-full hover:bg-black transition-colors">
        Volver al Inicio
      </a>
      <a href="{{ route('interpretes.index') }}" class="px-6 py-2 border-2 border-gray-800 text-gray-800 rounded-full hover:bg-gray-800 hover:text-white transition-colors">
        Ver Artistas
      </a>
    </div>
  </div>
@endsection

@section('sidebar')
  <x-sidebar.social-links />
  <x-sidebar.donate />
@endsection
