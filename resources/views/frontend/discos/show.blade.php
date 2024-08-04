@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-9">

        <div class="row">

          <div class="col-md-6">
            <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->titulo }}" class="card-img-top mb-4">
            <h1 class="fs-2">{{ $disco->anio }} - {{ $disco->album }}</h1>
            <p class="fs-5 mb-4"><span class="font-bold">Intérprete:</span> <a
                href="{{ route('interprete.show', $disco->interprete->slug) }}">{{ $disco->interprete->interprete }}</a>
            </p>
          </div>

          <div class="col-md-6">

            <h4>Listado de Canciones</h4>
            <hr>
            <ol>
              @foreach ($disco->canciones as $cancion)
                <li class="fs-5 mb-2">
                  <a href="{{ route('canciones.show', [$disco->interprete->slug, $cancion->slug]) }}"
                    class="h-100 shadow-sm text-decoration-none">{{ $cancion->cancion }}
                    <span class="float-end">
                      <i class="fas fa-file-alt"></i>
                      <i class="fas fa-video"></i>
                    </span>
                  </a>
                </li>
              @endforeach
              </ul>
          </div>
          <hr>

          @if ($disco->spotify !== '')
            <div class="mb-4">
              {{ $disco->spotify }}
            </div>
          @endif
        </div>



        <div class="row">

          <h3 class="fs-2">Otros díscos de {{ $interprete->interprete }} </h3>

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
