@extends('adminlte::page')

@section('metaTitle', 'Dashboard')

@section('content_header')
  <h1>Dashboard</h1>
@stop

@section('content')

  <section class="content">
    <div class="container-fluid">

      @if ($isAdmin && $attention)
        {{-- ================= Requiere tu atención ================= --}}
        <h5 class="mb-2">Requiere tu atención</h5>
        <div class="row">
          @foreach ($cards as $card)
            <div class="col-lg-3 col-sm-6">
              <div class="small-box bg-{{ $card['color'] }}" data-attention="{{ $card['key'] }}">
                <div class="inner">
                  <h3>{{ $card['count'] }}</h3>
                  <p class="mb-1"><strong>{{ $card['title'] }}</strong></p>
                  <p class="small mb-0">{{ $card['detail'] }}</p>
                </div>
                <div class="icon"><i class="{{ $card['icon'] }}"></i></div>
                <a href="{{ $card['url'] }}" class="small-box-footer">{{ $card['count'] > 0 ? 'Revisar' : 'Ver' }} <i class="fas fa-arrow-circle-right"></i></a>
              </div>
            </div>
          @endforeach
        </div>

        @if (count($attention['alerts']))
          <div class="alert alert-danger" role="alert" data-attention="alerts">
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle mr-1"></i> Alertas</h6>
            <ul class="mb-0">
              @foreach ($attention['alerts'] as $alert)
                <li>
                  @if ($alert['route']) <a href="{{ route($alert['route']) }}" class="alert-link">{{ $alert['label'] }}</a> @else {{ $alert['label'] }} @endif
                  <span class="badge badge-light">{{ $alert['count'] }}</span>
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- ================= Seguimiento de organizaciones ================= --}}
        <div class="card card-outline card-secondary shadow-sm" data-attention="organizations">
          <div class="card-header"><h3 class="card-title">Seguimiento de organizaciones</h3>
            <div class="card-tools"><a href="{{ route('backend.organizations.index') }}" class="btn btn-sm btn-outline-secondary">Ver organizaciones</a></div>
          </div>
          <div class="card-body py-2">
            <div class="row text-center">
              <div class="col-6 col-md-3"><div class="h4 mb-0">{{ $attention['organizations']['prospects'] }}</div><div class="small text-muted">Por contactar</div></div>
              <div class="col-6 col-md-3"><div class="h4 mb-0">{{ $attention['organizations']['with_email'] }}</div><div class="small text-muted">Con email obtenido</div></div>
              <div class="col-6 col-md-3"><div class="h4 mb-0">{{ $attention['organizations']['contacted'] }}</div><div class="small text-muted">Contactadas</div></div>
              <div class="col-6 col-md-3"><div class="h4 mb-0 {{ $attention['organizations']['stale'] > 0 ? 'text-danger' : '' }}">{{ $attention['organizations']['stale'] }}</div><div class="small text-muted">Sin respuesta (+{{ \App\Services\AdminAttentionService::STALE_CONTACT_DAYS }} días)</div></div>
            </div>
          </div>
        </div>

        {{-- ================= Últimos 7 días ================= --}}
        <div class="card card-outline card-secondary shadow-sm" data-attention="activity">
          <div class="card-header"><h3 class="card-title">Últimos 7 días</h3></div>
          <div class="card-body py-2">
            <div class="row text-center">
              <div class="col-6 col-md"><div class="h4 mb-0">+{{ $attention['activity']['users'] }}</div><div class="small text-muted">Usuarios nuevos</div></div>
              <div class="col-6 col-md"><div class="h4 mb-0">+{{ $attention['activity']['newsletter'] }}</div><div class="small text-muted">Suscriptores</div></div>
              <div class="col-6 col-md"><div class="h4 mb-0">+{{ $attention['activity']['claims'] }}</div><div class="small text-muted">Solicitudes</div></div>
              <div class="col-6 col-md"><div class="h4 mb-0">+{{ $attention['activity']['messages'] }}</div><div class="small text-muted">Mensajes</div></div>
              <div class="col-12 col-md"><div class="h4 mb-0">{{ $attention['activity']['events_upcoming'] }}</div><div class="small text-muted">Eventos próximos (7 días)</div></div>
            </div>
          </div>
        </div>
      @endif

      {{-- ================= Totales del portal ================= --}}
      <h5 class="mb-2 mt-3">Totales del portal</h5>
      @php
        $totals = [
          ['Noticias', 'news', 'fas fa-newspaper', 'bg-info', 'backend.news.index'],
          ['Intérpretes', 'interpretes', 'fas fa-microphone', 'bg-success', 'backend.interpretes.index'],
          ['Canciones', 'canciones', 'fas fa-music', 'bg-danger', 'backend.canciones.index'],
          ['Discos', 'discos', 'fas fa-record-vinyl', 'bg-teal', 'backend.discos.index'],
          ['Festivales', 'festivales', 'fas fa-map-marked-alt', 'bg-orange', 'backend.festivales.index'],
          ['Comidas', 'comidas', 'fas fa-utensils', 'bg-maroon', 'backend.comidas.index'],
          ['Eventos', 'events', 'fas fa-calendar-alt', 'bg-primary', 'backend.events.index'],
        ];
        if ($isAdmin) {
          $totals[] = ['Usuarios', 'usuarios', 'fas fa-users', 'bg-warning', 'users.index'];
        }
      @endphp
      <div class="row">
        @foreach ($totals as [$label, $key, $icon, $bg, $route])
          <div class="col-lg-3 col-6">
            <a href="{{ route($route) }}" class="text-white">
              <div class="info-box {{ $bg }} mb-3">
                <span class="info-box-icon"><i class="{{ $icon }}"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">{{ $label }}</span>
                  <span class="info-box-number">{{ $contadores[$key] ?? 0 }}</span>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>

    </div><!-- /.container-fluid -->
  </section>

@endsection
