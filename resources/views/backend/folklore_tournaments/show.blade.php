@extends('adminlte::page')

@section('title', $tournament->name)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-trophy mr-2"></i>{{ $tournament->name }}</h1>
    <div class="btn-group" role="group" aria-label="Acciones del torneo">
      <a href="{{ route('backend.folklore-tournaments.edit', $tournament) }}" class="btn btn-warning">
        <i class="fas fa-edit mr-1"></i> Editar torneo
      </a>
      <a href="{{ route('backend.folklore-tournaments.matches', $tournament) }}" class="btn btn-info">
        <i class="fas fa-list mr-1"></i> Ver partidos
      </a>
    </div>
  </div>
@stop

@section('content')
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row">
    <div class="col-md-4">
      <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">Datos generales</h3></div>
        <div class="card-body">
          <p><strong>Slug:</strong> {{ $tournament->slug }}</p>
          <p><strong>Anio:</strong> {{ $tournament->year }}</p>
          <p><strong>Estado:</strong> <span class="badge badge-secondary text-uppercase">{{ $tournament->status }}</span></p>
          <p><strong>Inicio:</strong> {{ $tournament->starts_at?->format('d/m/Y H:i') ?? '-' }}</p>
          <p><strong>Fin:</strong> {{ $tournament->ends_at?->format('d/m/Y H:i') ?? '-' }}</p>
          <p class="mb-0"><strong>Descripcion:</strong><br>{{ $tournament->description ?: '-' }}</p>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">Zonas y participantes</h3></div>
        <div class="card-body">
          <div class="row">
            @foreach ($tournament->groups as $group)
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header py-2">
                    <strong>{{ $group->name }}</strong>
                  </div>
                  <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                      @foreach ($group->participants as $participant)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <span>{{ $participant->display_name }}</span>
                          @if($participant->artist)
                            <small class="text-muted">ID artista {{ $participant->artist->id }}</small>
                          @endif
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Resumen de partidos</h3></div>
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead>
          <tr>
            <th>Fase</th>
            <th>Zona</th>
            <th>Jornada</th>
            <th>Fecha</th>
            <th>Partido</th>
            <th>Estado</th>
            <th>Instagram</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($tournament->matches as $match)
            <tr>
              <td>{{ $match->phaseLabel() }}</td>
              <td>{{ $match->group?->name ?? '-' }}</td>
              <td>{{ $match->matchday ?? '-' }}</td>
              <td>{{ $match->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</td>
              <td>{{ $match->participant1?->display_name }} vs {{ $match->participant2?->display_name }}</td>
              <td><span class="badge badge-light border">{{ $match->statusLabel() }}</span></td>
              <td>
                @if($match->instagram_url)
                  <a href="{{ $match->instagram_url }}" target="_blank" rel="noopener noreferrer">Ver post</a>
                @else
                  -
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop
