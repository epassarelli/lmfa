@extends('adminlte::page')

@section('metaTitle', 'Listado de Festivales')

@section('content_header')
  <h1>Gestion de Festivales</h1>
@stop

@section('content')
  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
      <h3 class="card-title">Listado de Festivales</h3>
      <div class="card-tools">
        <a href="{{ route('backend.festivales.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Crear Festival</a>
      </div>
    </div>
    <div class="card-body">
      <x-admin-listing-toolbar
        :action="route('backend.festivales.index')"
        search-placeholder="Buscar por titulo, provincia, localidad, mes o estado"
        :sort-options="[
          'published_at' => 'Publicacion',
          'title' => 'Titulo',
          'status' => 'Estado',
          'noticias_count' => 'Noticias vinculadas',
          'events_count' => 'Eventos vinculados',
          'interpretes_count' => 'Artistas vinculados',
          'knowledge_articles_count' => 'Articulos vinculados',
        ]"
      />

      <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
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

      <div class="card-footer bg-white px-0 pb-0 pt-3">
        {{ $festivales->links() }}
      </div>
    </div>
  </div>
@stop
