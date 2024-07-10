@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container">

    <h1>Shows y Eventos del Folklore Argentino</h1>
    <p class="lead">Explora nuestra sección de shows y eventos del folklore argentino para mantenerte al tanto de las
      presentaciones en
      vivo y festivales que celebran la música folklórica argentina. Aquí encontrarás información detallada sobre los
      próximos conciertos, festivales y eventos especiales donde podrás disfrutar de la música de tus artistas y cantantes
      favoritos.</p>
    <p class="lead">Obtén detalles sobre fechas, ubicaciones y horarios de los eventos más importantes, así como
      información sobre la
      compra de entradas y recomendaciones para disfrutar al máximo de cada espectáculo. Ya sea un concierto íntimo en una
      peña local o un gran festival folklórico, nuestra sección te mantendrá informado sobre todas las oportunidades para
      vivir la música folklórica argentina en vivo.</p>
    <p class="lead">No te pierdas ninguna ocasión para celebrar y disfrutar del folklore argentino. Nuestra sección de
      shows y eventos
      te conecta con las experiencias en vivo más emocionantes, permitiéndote formar parte de la rica tradición musical de
      Argentina. Desde festivales anuales hasta presentaciones exclusivas, te ofrecemos una guía completa para que no te
      falte nada.</p>

    <div class="row">
      @if ($shows->isEmpty())
        <div class="warning"></div>
        <div class="alert alert-warning" role="alert">
          No hay shows disponibles aún.
        </div>
      @else
        @foreach ($shows as $evento)
          <div class="col-md-6 mb-4">
            <div class="card h-100"
              style="background-image: url('{{ asset('storage/interpretes/' . $evento->interprete->foto) }}'); background-size: cover; background-position: center;">
              <div class="row h-100">
                <div class="col-md-4 text-center d-flex align-items-center justify-content-center"
                  style="background-color: rgba(0, 0, 0, 0.7); color: white;">
                  <div>
                    <h2 class="mb-1">{{ date('d', strtotime($evento->fecha)) }}</h2>
                    <p class="mb-1">{{ date('M', strtotime($evento->fecha)) }}</p>
                    <p>{{ date('Y', strtotime($evento->fecha)) }}</p>
                  </div>
                </div>
                <div class="col-md-8 p-4" style="background-color: rgba(255, 255, 255, 0.8);">
                  <div class="d-flex align-items-center mb-3">
                    <img src="{{ asset('storage/interpretes/' . $evento->interprete->foto) }}"
                      alt="{{ $evento->interprete->interprete }}" class="rounded-circle me-3"
                      style="width: 50px; height: 50px;">
                    <h5 class="card-title m-0">{{ $evento->interprete->interprete }}</h5>
                  </div>
                  <h6 class="card-subtitle mb-3 text-muted"><strong>Show:</strong> {{ $evento->show }}</h6>
                  <p class="card-text"><i class="fas fa-info-circle me-2"></i><strong>Detalles:</strong>
                    {!! $evento->detalle !!}</p>
                  <p class="card-text"><i class="fas fa-map-marker-alt me-2"></i><strong>Ubicación:</strong>
                    {{ $evento->lugar }}</p>
                  <p class="card-text"><i class="fas fa-align-left me-2"></i>{{ $evento->descripcion }}</p>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      @endif
    </div>



  </div>

@endsection
