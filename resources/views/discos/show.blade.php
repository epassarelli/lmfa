@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container mx-auto py-8">
    <div class="flex flex-col md:flex-row items-center">
      <div class="w-full md:w-1/3 mb-4 md:mb-0">
        <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->titulo }}"
          class="w-full h-auto md:max-w-xs rounded-lg shadow-md">
      </div>
      <div class="w-full md:w-2/3 md:pl-8">
        <h1 class="text-4xl font-bold mb-4">{{ $disco->titulo }}</h1>
        <p class="text-lg mb-4"><span class="font-bold">Año:</span> {{ $disco->anio }}</p>
        <p class="text-lg mb-4"><span class="font-bold">Intérprete:</span> <a
            href="{{ route('interprete.show', $disco->interprete_id) }}">{{ $disco->interprete->interprete }}</a></p>
        <div class="mb-4">
          <iframe src="https://open.spotify.com/embed/playlist/{{ $disco->spotify }}" width="100%" height="380"
            frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
        </div>
        <h2 class="text-2xl font-bold mb-4">Canciones</h2>
        <ul>
          {{-- @foreach ($disco->canciones as $cancion)
          <li class="text-lg mb-2"><a href="{{ route('cancion.show', $cancion->id) }}">{{ $cancion->titulo }}</a></li>
        @endforeach --}}
        </ul>
      </div>
    </div>
  </div>


@endsection
