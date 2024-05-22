@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto py-8">
    <div class="row">
      <div class="col-md-9 px-4">
        <h2 class="text-3xl font-bold mb-4">{{ $festival->titulo }}</h2>
        <img src="{{ asset('storage/festivales/' . $festival->foto) }}" alt="{{ $festival->titulo }}"
          class="mb-4 rounded-lg shadow-lg">
        <p class="text-lg mb-4">{!! $festival->detalle !!}</p>
        <p class="text-gray-600">Visitas: {{ $festival->visitas }}</p>
      </div>
      <div class="col-md-3 px-4">
        <h3 class="text-2xl font-bold mb-4">Últimos festivales</h3>
        <ul class="border-t-4 border-b-4 border-gray-300 py-4">
          @foreach ($ultimos_festivales as $ultimo_festival)
            <li class="py-2"><a href="{{ route('festivales.show', $ultimo_festival->slug) }}"
                class="text-lg hover:underline">{{ $ultimo_festival->titulo }}</a></li>
            <hr class="my-2">
          @endforeach
        </ul>
      </div>
    </div>
  </div>


@endsection
