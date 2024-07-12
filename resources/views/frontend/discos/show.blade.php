@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-9">

        <div class="row">

          <div class="col-md-6">
            <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->titulo }}" class="card-img-top">

            <p class="text-lg mb-4"><span class="font-bold">Año:</span> {{ $disco->anio }}</p>
            <p class="text-lg mb-4"><span class="font-bold">Intérprete:</span> <a
                href="{{ route('interprete.show', $disco->interprete->slug) }}">{{ $disco->interprete->interprete }}</a>
            </p>

            @if ($disco->spotify !== '')
              <div class="mb-4">
                <iframe src="https://open.spotify.com/embed/playlist/{{ $disco->spotify }}" width="100%" height="380"
                  frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
              </div>
            @endif



          </div>

          <div class="col-md-6">
            <h1>{{ $disco->album }}</h1>
            {{-- <h2 class="mb-4">Canciones</h2> --}}
            <ul>
              @foreach ($disco->canciones as $cancion)
                <li class="text-lg mb-2">
                  <a href="{{ route('canciones.show', [$disco->interprete->slug, $cancion->slug]) }}"
                    class="h-100 shadow-sm text-decoration-none">{{ $cancion->cancion }}</a>
                </li>
              @endforeach
            </ul>
          </div>


        </div>




        <div class="row">
          <h3>Otros díscos de {{ $interprete->interprete }} </h3>
          @if ($related->isEmpty())
            <div class="warning"></div>
            <div class="alert alert-warning" role="alert">
              No hay discos relacionados disponibles para {{ $interprete->interprete }} aún.
            </div>
          @else
            @foreach ($related as $disco)
              <div class="col-md-4 mb-4">
                <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                  class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                  <div class="card-img-top">
                    <img src="{{ asset('storage/albunes/' . $disco->foto) }}" class="img-fluid w-100 h-auto object-cover"
                      alt="{{ $disco->album }}">
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2">{{ $disco->album }}</h5>
                    <p class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">
                      {{ $disco->interprete->interprete }}
                    </p>
                    <p class="card-text mt-auto">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
                  </div>
                </a>
              </div>
            @endforeach
          @endif

        </div>



      </div>

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>

  @endsection
