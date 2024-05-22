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
        <h1>Canciones por {{ $interprete->interprete }}</h1>

        <div class="row">
          <ul>
            @foreach ($canciones as $cancion)
              <div class="col-md-6">
                <a href="{{ route('interprete.cancion.show', [$interprete->slug, $cancion->slug]) }}"
                  class="h-100 shadow-sm text-decoration-none">
                  <li class="card-text text-muted mb-2">
                    {{ $cancion->interprete->interprete }}
                    <span class="card-title h6 text-dark mb-2 hover:text-primary">
                      {{ $cancion->cancion }}
                    </span>

                  </li>

                </a>
              </div>
            @endforeach
          </ul>
        </div>

      </div>


    @endsection
