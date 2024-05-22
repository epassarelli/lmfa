@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Mitos y Leyendas Tradicionales</h1>
    <p>Bienvenidos a nuestra sección de mitos y leyendas tradicionales, donde exploramos las historias y relatos que
      forman parte del rico patrimonio cultural del folklore argentino. Aquí encontrarás una colección fascinante de mitos
      ancestrales y leyendas populares que han sido transmitidos de generación en generación, reflejando la sabiduría,
      creencias y valores de nuestra gente.</p>
    <p>Cada mito y leyenda está narrado con detalle, proporcionando contexto histórico y cultural para ayudarte a entender
      su significado y su relevancia en la actualidad. Descubre las historias de seres míticos, héroes legendarios y
      fenómenos sobrenaturales que han inspirado canciones, danzas y celebraciones en la música folklórica argentina.</p>
    <p>Sumérgete en el mundo mágico de los mitos y leyendas y conecta con las raíces profundas de nuestra identidad
      cultural. Nuestra sección es una ventana abierta a la imaginación y el misterio, invitándote a conocer y apreciar
      las narrativas que enriquecen el folklore argentino y que continúan alimentando la creatividad y la tradición de
      nuestro pueblo.</p>

    <div class="row justify-content-center">
      @foreach ($mitos as $mito)
        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
          <div class="card">
            <a href="{{ route('mitos.show', $mito->slug) }}">
              @if (file_exists(public_path('storage/mitos/' . $mito->foto)) && $mito->foto !== '')
                <img src="{{ asset('storage/mitos/' . $mito->foto) }}" alt="{{ $mito->titulo }}" class="card-img-top">
              @else
                <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                  class="card-img-top">
              @endif
              <div class="card-body">
                <h5 class="card-title">{{ $mito->titulo }}</h5>
              </div>
            </a>
          </div>
        </div>
      @endforeach
    </div>



  </div>


@endsection
