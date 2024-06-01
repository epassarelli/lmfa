@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-2">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

      <div class="col-md-10">

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
              <div class="col-md-4 mt-4">
                <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                  class="text-decoration-none">
                  <div class="card bg-white rounded-lg shadow-md overflow-hidden">
                    <img class="card-img-top w-100 h-auto object-cover"
                      src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->album }}">
                    <div class="card-body">
                      <h5 class="card-title text-lg font-medium text-gray-800 mb-2 hover:text-blue-600">
                        {{ $disco->album }}
                      </h5>
                      <p class="card-text text-gray-500 text-sm mb-2">{{ $disco->anio }}
                        -{{ $disco->interprete->interprete }}</p>
                    </div>
                  </div>
                </a>
              </div>
            @endforeach
          @endif

        </div>



      </div>

    </div>

  @endsection
