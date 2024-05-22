@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-4">
        <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" class="img-fluid rounded"
          alt="{{ $interprete->interprete }}">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

      <div class="col-md-8">
        <h1>Noticias de {{ $interprete->interprete }}</h1>
        <div class="row">
          @foreach ($noticias as $noticia)
            <div class="col-12 pb-4">
              <div class="card h-200 shadow-sm text-decoration-none">
                <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}"
                  class="text-decoration-none">
                  <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
                    class="card-img-top" style="height: 16rem; object-fit: cover;">
                  <div class="card-body">
                    <h3 class="card-title h5 font-weight-bold text-dark mb-2">{{ $noticia->titulo }}</h3>
                  </div>
                </a>
              </div>
            </div>
          @endforeach

        </div>
        {{-- @foreach ($noticias as $noticia)
          <div class="col-12 col-sm-6 col-md-4 p-4">
            <div class="card h-100 shadow-sm text-decoration-none">
              <a href="noticias/{{ $noticia->slug }}" class="text-decoration-none">
                <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
                  class="card-img-top" style="height: 12rem; object-fit: cover;">
                <div class="card-body">
                  <h3 class="card-title h5 fw-bold text-dark mb-2">{{ $noticia->titulo }}</h3>
                </div>
              </a>
            </div>
          </div>
        @endforeach --}}


      </div>


    @endsection
