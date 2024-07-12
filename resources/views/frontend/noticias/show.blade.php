@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-9">


        <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
          class="mb-4 img-fluid rounded shadow-lg">
        <h1 class="fs-4">{{ $noticia->titulo }}</h1>

        <p class="fs-5 mb-4">{!! $noticia->noticia !!}</p>
        <p class="text-muted">Visitas: {{ $noticia->visitas }}</p>
      </div>

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>

    <div class="row">
      <h3>Últimas noticias</h3>
      <ul class="list-group">
        @foreach ($ultimas_noticias as $noticia)
          <li class="list-group-item">
            <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}"
              class="text-decoration-none">{{ $noticia->titulo }}</a>
          </li>
        @endforeach
      </ul>
    </div>

  </div>

@endsection
