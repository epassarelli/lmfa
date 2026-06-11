@extends('adminlte::page')

@section('title', 'Torneos de Folklore')

@section('content_header')
  <h1><i class="fas fa-trophy mr-2"></i>Torneos de Folklore</h1>
@stop

@section('content')
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Listado de torneos</h3>
    </div>

    <div class="card-body">
      <table id="folklore-tournaments-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Torneo</th>
            <th>Año</th>
            <th>Estado</th>
            <th>Zonas</th>
            <th>Participantes</th>
            <th>Partidos</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($tournaments as $tournament)
            <tr>
              <td>{{ $tournament->name }}</td>
              <td>{{ $tournament->year }}</td>
              <td><span class="badge badge-secondary text-uppercase">{{ $tournament->status }}</span></td>
              <td>{{ $tournament->groups_count }}</td>
              <td>{{ $tournament->participants_count }}</td>
              <td>{{ $tournament->matches_count }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.folklore-tournaments.show', $tournament) }}" class="btn btn-sm btn-primary" title="Ver detalle">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('backend.folklore-tournaments.edit', $tournament) }}" class="btn btn-sm btn-warning" title="Editar torneo">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="{{ route('backend.folklore-tournaments.matches', $tournament) }}" class="btn btn-sm btn-info" title="Ver partidos">
                  <i class="fas fa-list"></i>
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
      $('#folklore-tournaments-table').DataTable({
        order: [[1, 'desc']]
      });
    });
  </script>
@stop
