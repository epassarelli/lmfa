@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto py-8">
    <div class="row">
      <div class="col-md-9">
        <h1>{{ $festival->titulo }}</h1>
        <img src="{{ asset('storage/festivales/' . $festival->foto) }}" alt="{{ $festival->titulo }}"
          class="mb-4 img-fluid rounded shadow-lg">
        <p class="text-lg mb-4">{!! $festival->detalle !!}</p>
        <p class="text-gray-600">Visitas: {{ $festival->visitas }}</p>
      </div>

      <div class="col-md-3">
        <h3>Últimos festivales</h3>
        <ul class="list-group">
          @foreach ($ultimos_festivales as $ultimo_festival)
            <li class="list-group-item">
              <a href="{{ route('festivales.show', $ultimo_festival->slug) }}"
                class="text-decoration-none">{{ $ultimo_festival->titulo }}</a>
            </li>
          @endforeach
        </ul>
      </div>

    </div>
  </div>


@endsection
