@extends('adminlte::page')

@section('metaTitle', 'Listado de Festivales')

@section('content_header')
  <h1>Gestion de Festivales</h1>
@stop

@section('content')
  <div class="card">
    <div class="card-header text-right">
      <a href="{{ route('backend.festivales.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Festival</a>
    </div>
    <div class="card-body">
      <table id="festivales-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Titulo</th>
            <th>Provincia</th>
            <th>Localidad</th>
            <th>Mes</th>
            <th>Publicado</th>
            <th>Estado</th>
            <th>Relaciones</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($festivales as $festival)
            <tr>
              <td>{{ $festival->title }}</td>
              <td>{{ $festival->provincia?->nombre ?? '-' }}</td>
              <td>{{ $festival->locality?->name ?? '-' }}</td>
              <td>{{ $festival->mes?->nombre ?? '-' }}</td>
              <td>{{ optional($festival->published_at)->format('d-m-Y') ?? '-' }}</td>
              <td>{{ $festival->status }}</td>
              <td>
                N: {{ $festival->noticias_count ?? 0 }} |
                E: {{ $festival->events_count ?? 0 }} |
                A: {{ $festival->interpretes_count ?? 0 }} |
                K: {{ $festival->knowledge_articles_count ?? 0 }}
              </td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.festivales.edit', $festival) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @can('delete', $festival)
                  <form action="{{ route('backend.festivales.destroy', $festival) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Estas seguro de eliminar este festival?')">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                @endcan
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
    $(function() {
      $('#festivales-table').DataTable();
    });
  </script>
@stop
