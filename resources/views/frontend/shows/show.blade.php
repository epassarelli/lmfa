@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="d-flex flex-wrap py-4">

    <div class="col-12 col-md-9 px-4">
      <h2 class="text-3xl fw-bold mb-4">{{ $show->titulo }}</h2>
      <img src="{{ asset('storage/mitos/' . $show->foto) }}" alt="{{ $show->titulo }}" class="mb-4 rounded-lg shadow-lg">
      <p class="text-lg mb-4">{!! $show->receta !!}</p>
      <p class="text-gray-600">Visitas: {{ $show->visitas }}</p>
    </div>

    <div class="col-12 col-md-3 px-4">
      <h3 class="text-2xl fw-bold mb-4">Últimas recetas</h3>
      <ul class="border-top border-bottom border-3 border-gray-300 py-4">
        @foreach ($ultimos_shows as $latest_show)
          <li class="py-2"><a href="{{ url('mitos/' . $latest_show->slug) }}"
              class="text-lg text-decoration-none hover-underline">{{ $latest_show->titulo }}</a></li>
          <hr class="my-2">
        @endforeach
      </ul>
    </div>

  </div>


@endsection
