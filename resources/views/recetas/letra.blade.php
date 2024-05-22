@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Recetas de Comidas Típicas</h1>
    <p>Bienvenidos a nuestra sección de recetas de comidas típicas, donde te invitamos a descubrir y preparar los platos
      más tradicionales de la gastronomía argentina. Explora una variedad de recetas que reflejan la riqueza y diversidad
      culinaria de nuestro país, desde clásicos del folklore argentino hasta delicias regionales.</p>
    <p>Cada receta está cuidadosamente detallada con ingredientes, pasos de preparación y consejos útiles para que puedas
      recrear en casa los sabores auténticos de Argentina. Aprende a cocinar platos emblemáticos como empanadas, locro,
      asado y muchas otras delicias que forman parte de nuestras fiestas y celebraciones.</p>
    <p>Nuestra sección de recetas de comidas típicas es tu guía culinaria para experimentar y disfrutar de la cocina
      argentina en su máxima expresión. Sumérgete en las tradiciones culinarias que acompañan la música folklórica y las
      festividades, y lleva a tu mesa los sabores que han deleitado a generaciones. Descubre y comparte la pasión por
      nuestra gastronomía y cultura.</p>



    <div class="container mt-5">
      <div class="row mb-4">
        <div class="col-12">
          <h2>Recetas de comidas con la letra {{ $letra }}</h2>
          <p class="lead">
            Explora los perfiles de los intérpretes de folklore argentino que han capturado la mayor atención de nuestros
            visitantes. Estos artistas se destacan por su popularidad y su contribución significativa a la música
            folklórica. Sumérgete en la vida y obra de los cantantes y músicos más reconocidos de nuestro portal.
          </p>

          <div class="row">
            <!-- Repetir este bloque para cada intérprete -->
            @foreach ($comidas as $visitado2)
              <div class="col-md-4 col-lg-3">
                <div class="card mb-3">
                  <a href="{{ route('comidas.show', $visitado2->slug) }}" class="text-decoration-none">
                    {{-- @if (file_exists(public_path('storage/comidas/' . $visitado2->foto)) && $visitado2->foto !== '')
                      <img src="{{ asset('storage/comidas/' . $visitado2->foto) }}" alt="{{ $visitado2->interprete }}"
                        class="card-img-top">
                    @else
                      <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                        class="card-img-top">
                    @endif --}}

                    <div class="card-body">
                      <h5 class="card-title">{{ $visitado2->titulo }}</h5>
                      <p class="card-text">{{ number_format($visitado2->visitas, 0, '', ',') }} visitas</p>
                      {{-- <a href="#" class="btn btn-primary">Ver Perfil</a> --}}
                    </div>
                  </a>
                </div>
              </div>
            @endforeach
            <!-- Fin del bloque -->
          </div>
        </div>
      </div>
    </div>





    <div class="row mb-4">
      <div class="col-12">
        <h2>Recetas de comidas más visitadas</h2>

        <div class="row justify-content-center">
          @foreach ($visitadas as $receta)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
              <div class="card">
                <a href="{{ route('comidas.show', $receta->slug) }}">
                  {{-- @if (file_exists(public_path('storage/recetas/' . $receta->foto)) && $receta->foto !== '')
                    <img src="{{ asset('storage/recetas/' . $receta->foto) }}" alt="{{ $receta->titulo }}"
                      class="card-img-top">
                  @else
                    <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                      class="card-img-top">
                  @endif --}}
                  <div class="card-body">
                    <h5 class="card-title">{{ $receta->titulo }}</h5>
                  </div>
                </a>
              </div>
            </div>
          @endforeach
        </div>

      </div>
    </div>






    <div class="row mb-4">
      <div class="col-12">
        <h2>Buscar por Orden Alfabético</h2>
        <p class="lead">
          ...
        </p>

        <nav>
          <ul class="pagination pagination-sm justify-content-center">
            @foreach (range('A', 'Z') as $letra)
              <li class="page-item"><a class="page-link"
                  href="{{ route('comidas.letra', $letra) }}">{{ $letra }}</a></li>
            @endforeach
          </ul>
        </nav>
      </div>
    </div>






    <div class="row mb-4">
      <div class="col-12">
        <h2>Ulimas recetas de comidas típicas agregadas</h2>

        <div class="row justify-content-center">
          @foreach ($ultimas as $receta)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
              <div class="card">
                <a href="{{ route('comidas.show', $receta->slug) }}">
                  {{-- @if (file_exists(public_path('storage/recetas/' . $receta->foto)) && $receta->foto !== '')
                    <img src="{{ asset('storage/recetas/' . $receta->foto) }}" alt="{{ $receta->titulo }}"
                      class="card-img-top">
                  @else
                    <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                      class="card-img-top">
                  @endif --}}
                  <div class="card-body">
                    <h5 class="card-title">{{ $receta->titulo }}</h5>
                  </div>
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

  </div>


@endsection
