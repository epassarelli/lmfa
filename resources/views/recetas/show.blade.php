@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="container">
    <div class="flex flex-wrap py-4">
      <div class="w-full md:w-3/4 px-4">
        <h2 class="text-3xl font-bold mb-4">{{ $receta->titulo }}</h2>
        {{-- <img src="{{ asset('storage/recetas/' . $receta->foto) }}" alt="{{ $receta->titulo }}"
          class="mb-4 rounded-lg shadow-lg"> --}}
        <p class="text-lg mb-4">{!! $receta->receta !!}</p>
        <p class="text-gray-600">Visitas: {{ $receta->visitas }}</p>
      </div>

      <div class="w-full md:w-1/4 px-4">
        <h3 class="text-2xl font-bold mb-4">Últimas recetas</h3>
        <ul class="border-t-4 border-b-4 border-gray-300 py-4">
          @foreach ($ultimas_recetas as $ultima_receta)
            <li class="py-2">
              <a href="{{ route('comidas.show', $ultima_receta->slug) }}"
                class="text-lg hover:underline">{{ $ultima_receta->titulo }}</a>
            </li>
            <hr class="my-2">
          @endforeach
        </ul>
      </div>
    </div>

  </div>
@endsection
