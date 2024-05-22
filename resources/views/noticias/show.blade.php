@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="d-flex flex-wrap py-4">

    <div class="col-12 col-md-9 px-4">
      <h2 class="display-4 fw-bold mb-4">{{ $noticia->titulo }}</h2>
      <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
        class="mb-4 img-fluid rounded shadow-lg">
      <p class="fs-5 mb-4">{!! $noticia->noticia !!}</p>
      <p class="text-muted">Visitas: {{ $noticia->visitas }}</p>
    </div>

    <div class="col-12 col-md-3 px-4">
      <h3 class="h4 fw-bold mb-4">Últimas noticias</h3>
      <ul class="list-group border-top border-bottom border-3 border-secondary py-4">
        @foreach ($ultimas_noticias as $noticia)
          <li class="list-group-item border-0">
            <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}"
              class="fs-5 text-decoration-none">{{ $noticia->titulo }}</a>
            @if (!$loop->last)
              <hr class="my-2">
            @endif
          </li>
        @endforeach
      </ul>
    </div>

  </div>


@endsection
