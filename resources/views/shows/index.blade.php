@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container">

    <h1>Shows y Eventos del Folklore Argentino</h1>
    <p>Explora nuestra sección de shows y eventos del folklore argentino para mantenerte al tanto de las presentaciones en
      vivo y festivales que celebran la música folklórica argentina. Aquí encontrarás información detallada sobre los
      próximos conciertos, festivales y eventos especiales donde podrás disfrutar de la música de tus artistas y cantantes
      favoritos.</p>
    <p>Obtén detalles sobre fechas, ubicaciones y horarios de los eventos más importantes, así como información sobre la
      compra de entradas y recomendaciones para disfrutar al máximo de cada espectáculo. Ya sea un concierto íntimo en una
      peña local o un gran festival folklórico, nuestra sección te mantendrá informado sobre todas las oportunidades para
      vivir la música folklórica argentina en vivo.</p>
    <p>No te pierdas ninguna ocasión para celebrar y disfrutar del folklore argentino. Nuestra sección de shows y eventos
      te conecta con las experiencias en vivo más emocionantes, permitiéndote formar parte de la rica tradición musical de
      Argentina. Desde festivales anuales hasta presentaciones exclusivas, te ofrecemos una guía completa para que no te
      falte nada.</p>


    <div class="d-flex flex-wrap px-4 p-4">
      @foreach ($shows as $evento)
        <div class="col-12 col-md-6 col-lg-3 px-4 mb-4">
          <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="position-relative">
              <img class="w-100" src="{{ asset('storage/interpretes/' . $evento->interprete->foto) }}"
                alt="{{ $evento->interprete->interprete }}">
              <div class="position-absolute bottom-0 start-0 w-100 h-25 bg-gradient bg-gradient-opacity">
              </div>
              <div class="position-absolute bottom-0 start-0 p-4">
                <h2 class="text-white fs-4 fw-bold">{{ $evento->titulo }}</h2>
                <p class="text-gray-300">{{ $evento->interprete->interprete }}</p>
              </div>
            </div>
            <div class="p-4">
              <p class="text-gray-600 fs-6">{{ \Carbon\Carbon::parse($evento->fecha)->format('Y-m-d') }}</p>
              <p class="text-gray-800 fs-5 fw-bold mb-2">{{ $evento->titulo }}</p>
              <p class="text-gray-700">{{ $evento->interprete->interprete }}</p>
              <p class="text-gray-600">{{ $evento->lugar }}, {{ $evento->direccion }}</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>


  </div>

@endsection
