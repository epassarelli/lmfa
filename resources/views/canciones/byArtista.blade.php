@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

      <div class="col-md-9">
        <h1>Canciones por {{ $interprete->interprete }}</h1>

        <div class="row">
          <ul>
            @foreach ($canciones as $cancion)
              <div class="col-md-6">

                <li class="text-lg mb-2">
                  <a href="{{ route('canciones.show', [$interprete->slug, $cancion->slug]) }}"
                    class="h-100 shadow-sm text-decoration-none">
                    {{ $cancion->interprete->interprete }} - {{ $cancion->cancion }}
                  </a>
                </li>

              </div>
            @endforeach
          </ul>
        </div>

      </div>
    </div>

  @endsection
