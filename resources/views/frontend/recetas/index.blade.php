@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Recetas de Comidas Típicas</h1>
    <p class="lead">Bienvenidos a nuestra sección de recetas de comidas típicas, donde te invitamos a descubrir y
      preparar los platos
      más tradicionales de la gastronomía argentina. Explora una variedad de recetas que reflejan la riqueza y diversidad
      culinaria de nuestro país, desde clásicos del folklore argentino hasta delicias regionales.</p>
    <p class="lead">Cada receta está cuidadosamente detallada con ingredientes, pasos de preparación y consejos útiles
      para que puedas
      recrear en casa los sabores auténticos de Argentina. Aprende a cocinar platos emblemáticos como empanadas, locro,
      asado y muchas otras delicias que forman parte de nuestras fiestas y celebraciones.</p>
    <p class="lead">Nuestra sección de recetas de comidas típicas es tu guía culinaria para experimentar y disfrutar de
      la cocina
      argentina en su máxima expresión. Sumérgete en las tradiciones culinarias que acompañan la música folklórica y las
      festividades, y lleva a tu mesa los sabores que han deleitado a generaciones. Descubre y comparte la pasión por
      nuestra gastronomía y cultura.</p>

    <div class="row mb-4">
      <div class="col-12">
        <h2>Recetas de comidas más visitadas</h2>
        <p class="lead">
          Descubre las recetas de comidas típicas argentinas que más interés han despertado entre nuestros visitantes.
          Estos platos no solo son deliciosos, sino que también capturan la esencia de nuestra cultura culinaria. Desde el
          tradicional asado hasta la dulce delicia del alfajor, estas recetas son las favoritas de nuestra comunidad.
        </p>
        <p class="lead">
          Sumérgete en el sabor auténtico de la cocina argentina con nuestras recetas de comidas típicas. Explora una
          variedad de platos tradicionales que reflejan la rica herencia cultural de nuestro país. Desde empanadas jugosas
          hasta el clásico locro, cada receta está cuidadosamente seleccionada para ofrecerte una experiencia culinaria
          inigualable. Descubre los secretos de la gastronomía argentina y lleva a tu mesa los sabores que han acompañado
          a generaciones.
        </p>

        <div class="row justify-content-center">
          @foreach ($visitadas as $receta)
            <div class="col-md-4 mb-4">
              <a href="{{ route('comidas.show', $receta->slug) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="row g-0 h-100">
                  <div class="col-auto d-flex align-items-center justify-content-center p-3 bg-black">
                    <i class="fas fa-fw fa-utensils fa-3x"></i>
                  </div>
                  <div class="col">
                    <div class="card-body d-flex flex-column">
                      <p class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">
                        {{ $receta->titulo }}
                      </p>
                      <p class="card-text mt-auto">{{ number_format($receta->visitas, 0, '', ',') }} visitas</p>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          @endforeach
        </div>

      </div>
    </div>






    <div class="row mb-4">
      <div class="col-12">
        <h2>Buscar por Orden Alfabético</h2>
        <p class="lead">
          Encuentra fácilmente tus recetas favoritas de la cocina argentina utilizando nuestro índice alfabético. Esta
          sección te permite explorar una amplia gama de platos tradicionales, ordenados de la A a la Z, facilitando tu
          búsqueda y ayudándote a descubrir nuevas delicias gastronómicas que puedes preparar en casa.
        </p>
        <hr>
        <nav>
          <ul class="pagination pagination-sm justify-content-center">
            @foreach (range('a', 'z') as $letra)
              <li class="page-item"><a class="page-link mx-1 "
                  href="{{ route('comidas.letra', $letra) }}">{{ $letra }}</a></li>
            @endforeach
          </ul>
        </nav>
        <hr>
      </div>
    </div>






    <div class="row mb-4">
      <div class="col-12">
        <h2>Ulimas recetas de comidas típicas agregadas</h2>
        <p class="lead">
          Mantente al día con las novedades culinarias de nuestra cocina argentina. En esta sección, te presentamos las
          últimas recetas de comidas típicas que hemos agregado a nuestro portal. Descubre nuevos sabores y técnicas de
          preparación que enriquecerán tu repertorio gastronómico y te acercarán aún más a la tradición argentina.
        </p>

        <div class="row justify-content-center">
          @foreach ($ultimas as $receta)
            <div class="col-md-4 mb-4">
              <a href="{{ route('comidas.show', $receta->slug) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="row g-0 h-100">
                  <div class="col-auto d-flex align-items-center justify-content-center p-3 bg-black">
                    <i class="fas fa-fw fa-utensils fa-3x"></i>
                  </div>
                  <div class="col">
                    <div class="card-body d-flex flex-column">
                      <p class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">
                        {{ $receta->titulo }}
                      </p>
                      <p class="card-text mt-auto">{{ number_format($receta->visitas, 0, '', ',') }} visitas</p>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>

  </div>


@endsection
