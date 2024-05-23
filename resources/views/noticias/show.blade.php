@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="container">
    <div class="row d-flex flex-wrap py-4">

      <div class="col-12 col-md-9 px-4">
        <h1>{{ $noticia->titulo }}</h1>
        <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
          class="mb-4 img-fluid rounded shadow-lg">
        <p class="fs-5 mb-4">{!! $noticia->noticia !!}</p>
        <p class="text-muted">Visitas: {{ $noticia->visitas }}</p>
      </div>

      <div class="col-md-3">
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

  </div>
@endsection
