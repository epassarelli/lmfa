@extends('adminlte::page')

@section('title', 'Noticias')

@section('content_header')
  <h1><i class="fas fa-newspaper mr-2"></i>Gestión de Noticias</h1>
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
      <table id="news-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Publicación</th>
            <th style="width:60px">Título</th>
            <th>Intérprete Principal</th>
            <th>Visitas</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          @foreach ($news as $item)
            <tr>
              <td data-order="{{ $item->published_at }}">
                {{ $item->published_at ? $item->published_at->format('d-m-Y') : '—' }}
              </td>
              <td style="width:60px;padding:4px">
                <div class="d-flex align-items-center">
                    @if ($item->images->isNotEmpty())
                        <x-optimized-image :image="$item->images->first()" variant="card" :minimal="true" style="width:40px;height:40px;object-fit:cover;flex-shrink:0" class="rounded-circle mr-2 shadow-sm" />
                    @elseif($item->foto)
                        <img src="{{ asset('storage/noticias/' . $item->foto) }}" alt="{{ $item->title }}"
                          style="width:40px;height:40px;object-fit:cover;flex-shrink:0" class="rounded-circle mr-2 shadow-sm">
                    @endif
                    <strong>{{ $item->title }}</strong>
                </div>
              </td>
              <td>{{ $item->interprete->interprete ?? '—' }}</td>
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
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar noticia?')">
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
    $(document).ready(function() {
      $('#news-table').DataTable({
        "order": [[0, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
        }
      });
    });
  </script>
@stop
