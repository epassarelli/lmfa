@extends('adminlte::page')

@section('title', 'Partidos - ' . $tournament->name)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-list mr-2"></i>Partidos de {{ $tournament->name }}</h1>
    <a href="{{ route('backend.folklore-tournaments.show', $tournament) }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Volver al torneo
    </a>
  </div>
@stop

@section('content')
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Listado de partidos</h3>
    </div>
    <div class="card-body">
      <p class="text-muted">
        La fase de grupos se genera automaticamente. Cuando una fase queda completa y finalizada, el sistema crea la siguiente llave y puedes corregir participantes o fechas desde esta pantalla.
      </p>

      <table id="folklore-matches-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Fase</th>
            <th>Zona</th>
            <th>Jornada</th>
            <th>Fecha</th>
            <th>Partido</th>
            <th>Resultado</th>
            <th>Estado</th>
            <th>Instagram</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($matches as $match)
            <tr>
              <td>{{ $match->phaseLabel() }}</td>
              <td>{{ $match->group?->name ?? '-' }}</td>
              <td>{{ $match->matchday ?? '-' }}</td>
              <td>{{ $match->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</td>
              <td>{{ $match->participant1?->display_name }} vs {{ $match->participant2?->display_name }}</td>
              <td>
                @if($match->isFinished())
                  {{ $match->participant_1_votes }} - {{ $match->participant_2_votes }}
                @else
                  <span class="text-muted">Pendiente</span>
                @endif
              </td>
              <td><span class="badge badge-light border">{{ $match->statusLabel() }}</span></td>
              <td>
                @if($match->instagram_url)
                  <a href="{{ $match->instagram_url }}" target="_blank" rel="noopener noreferrer">Ver post</a>
                @else
                  -
                @endif
              </td>
              <td class="text-right">
                <a href="{{ route('backend.folklore-tournament-matches.edit', $match) }}" class="btn btn-sm btn-warning" title="Editar partido">
                  <i class="fas fa-edit"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop

@section('js')
  <script>
    $(document).ready(function() {
      $('#folklore-matches-table').DataTable({
        order: [[0, 'asc'], [2, 'asc']]
      });
    });
  </script>
@stop
