@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex flex-col lg:flex-row gap-8">

      {{-- Contenido principal --}}
      <div class="w-full lg:w-2/3">
        <h1 class="text-3xl font-bold mb-4">{{ $mito->titulo }}</h1>

        {{-- Imagen (descomentá si la querés mostrar) --}}
        {{-- <img src="{{ asset('storage/mitos/' . $mito->foto) }}" alt="{{ $mito->titulo }}" class="mb-6 rounded-lg shadow-lg"> --}}

        <div class="text-lg text-gray-800 mb-6">
          {!! $mito->mito !!}
        </div>

        <p class="text-sm text-gray-600">Visitas: {{ $mito->visitas }}</p>

        {{-- Muestro ls redes p compartir --}}
        <div class="redes">
          <x-compartir-redes :titulo="$mito->titulo" :url="Request::url()" />
        </div>

      </div>

      {{-- Barra lateral --}}
      <div class="w-full lg:w-1/3">
        <h3 class="text-xl font-semibold mb-4">Últimos mitos</h3>


        @foreach ($ultimos_mitos as $ultimo_mito)
          <x-mito-card :mito="$mito" />
        @endforeach

      </div>

    </div>

  </div>

@endsection
