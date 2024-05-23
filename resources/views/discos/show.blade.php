@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container">

    <div class="row">

      <div class="col-md-3">
        <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->titulo }}" class="card-img-top">
        <h1>{{ $disco->titulo }}</h1>
        <p class="text-lg mb-4"><span class="font-bold">Año:</span> {{ $disco->anio }}</p>
        <p class="text-lg mb-4"><span class="font-bold">Intérprete:</span> <a
            href="{{ route('interprete.show', $disco->interprete->slug) }}">{{ $disco->interprete->interprete }}</a></p>

        @if ($disco->spotify !== '')
          <div class="mb-4">
            <iframe src="https://open.spotify.com/embed/playlist/{{ $disco->spotify }}" width="100%" height="380"
              frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
          </div>
        @endif



      </div>

      <div class="col-md-9">
        <h2 class="mb-4">Canciones</h2>
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

  </div>

@endsection
