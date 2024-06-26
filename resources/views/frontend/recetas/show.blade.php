@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <div class="row">

      <div class="col-md-9">

        <h1>{{ $receta->titulo }}</h1>
        {{-- <img src="{{ asset('storage/recetas/' . $receta->foto) }}" alt="{{ $receta->titulo }}"
          class="mb-4 rounded-lg shadow-lg"> --}}
        <p class="text-lg mb-4">{!! $receta->receta !!}</p>
        <p class="text-gray-600">Visitas: {{ $receta->visitas }}</p>





        <h2>Buscar por Orden Alfabético</h2>
        <p class="lead">
          Encuentra fácilmente tus recetas favoritas de la cocina argentina utilizando nuestro índice alfabético. Esta
          sección te permite explorar una amplia gama de platos tradicionales, ordenados de la A a la Z, facilitando tu
          búsqueda y ayudándote a descubrir nuevas delicias gastronómicas que puedes preparar en casa.
        </p>
        <hr>
        <nav>
          <ul class="pagination pagination-sm justify-content-center">
            @foreach (range('a', 'z') as $letra)
              <li class="page-item"><a class="page-link" href="{{ route('comidas.letra', $letra) }}">{{ $letra }}</a>
              </li>
            @endforeach
          </ul>
        </nav>
        <hr>

      </div>

      <div class="col-md-3">
        <h3>Últimas recetas</h3>
        <ul class="list-group">
          @foreach ($ultimas_recetas as $ultima_receta)
            <li class="list-group-item">
              <a href="{{ route('comidas.show', $ultima_receta->slug) }}"
                class="text-decoration-none">{{ $ultima_receta->titulo }}</a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>

  </div>

@endsection
