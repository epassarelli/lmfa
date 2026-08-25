@extends('adminlte::page')

@section('title', 'Noticias')

@section('content_header')
  <h1><i class="fas fa-newspaper mr-2"></i>Gestion de Noticias</h1>
@stop

@section('content')

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Listado de Noticias</h3>
      <div class="card-tools">
        @can('create', App\Models\News::class)
          <a href="{{ route('backend.news.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Nueva Noticia
          </a>
        @endcan
      </div>
    </div>

    <div class="card-body">
      <x-admin-listing-toolbar
        :action="route('backend.news.index')"
        search-placeholder="Buscar por titulo, interprete, categoria o estado"
        :sort-options="[
          'published_at' => 'Publicacion',
          'title' => 'Titulo',
          'visitas' => 'Visitas',
          'estado' => 'Estado',
        ]"
      />

      <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Publicacion</th>
            <th>Titulo</th>
            <th>Interprete Principal</th>
            <th>Visitas</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          @foreach ($news as $item)
            <tr>
              <td>
                {{ $item->published_at ? $item->published_at->format('d-m-Y') : '-' }}
              </td>
              <td>
                <strong>{{ $item->title }}</strong>
              </td>
              <td>{{ $item->interprete->interprete ?? '-' }}</td>
              <td><span class="badge badge-light border">{{ number_format($item->visitas) }}</span></td>
              <td>
                @if($item->estado)
                  <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Activo</span>
                @else
                  <span class="badge badge-secondary">Inactivo</span>
                @endif
              </td>
              <td class="text-right" style="white-space: nowrap;">
                @can('update', $item)
                  <a href="{{ route('backend.news.edit', $item) }}" class="btn btn-sm btn-warning" title="Editar">
                    <i class="fas fa-edit"></i>
                  </a>
                @endcan

                @can('delete', $item)
                  <form action="{{ route('backend.news.destroy', $item) }}" method="POST" class="d-inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar noticia?')">
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
        {{ $news->links() }}
      </div>
    </div>
  </div>
@stop
