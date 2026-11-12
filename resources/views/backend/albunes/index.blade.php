@extends('adminlte::page')

@section('metaTitle', 'Albumes')

@section('content_header')
  <h1>Gestion de Discos</h1>
@stop

@section('content')
  <x-backend.list-help section="album" />

  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Exito',
        text: '{{ session('success') }}'
      });
    </script>
  @endif

  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
      <h3 class="card-title">Listado de Discos</h3>
      <div class="card-tools">
        <a href="{{ route('backend.discos.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Crear Disco</a>
      </div>
    </div>
    <div class="card-body">
      <x-admin-listing-toolbar
        :action="route('backend.discos.index')"
        search-placeholder="Buscar por disco, anio, interprete o ID..."
        :sort-options="[
          'id' => 'Codigo',
          'album' => 'Album',
          'anio' => 'Anio',
          'visitas' => 'Visitas',
          'spotify' => 'Spotify',
          'canciones_count' => 'Canciones',
        ]"
      >
        <div class="col-md-3">
          <label for="interprete_id" class="small text-muted mb-1">Interprete</label>
          <select id="interprete_id" name="interprete_id" class="form-control">
            <option value="">Todos</option>
            @foreach ($filterInterpretes as $filterInterprete)
              <option value="{{ $filterInterprete->id }}" @selected((int) $interpreteId === $filterInterprete->id)>{{ $filterInterprete->interprete }}</option>
            @endforeach
          </select>
        </div>
      </x-admin-listing-toolbar>

      <div class="table-responsive">
        <table id="albums-table" class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>COD</th>
              <th style="width:60px">Foto</th>
              <th>Anio</th>
              <th>Album</th>
              <th>Visitas</th>
              <th>Spotify</th>
              <th>Canc's</th>
              <th>Interprete</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($albums as $album)
              <tr>
                <td>{{ $album->id }}</td>
                <td style="width:60px;padding:4px">
                  <div class="album-list-thumb">
                    @if ($album->images->isNotEmpty())
                      <x-optimized-image :image="$album->images->first()" variant="card" :minimal="true" />
                    @elseif($album->foto)
                      <img src="{{ asset('storage/albunes/' . $album->foto) }}" alt="Foto de {{ $album->album }}">
                    @else
                      <img src="{{ asset('img/no-image.jpg') }}" alt="Sin imagen">
                    @endif
                  </div>
                </td>
                <td>{{ $album->anio }}</td>
                <td>{{ $album->album }}</td>
                <td>{{ $album->visitas }}</td>
                <td>{{ $album->spotify ? 'Si' : '-' }}</td>
                <td>{{ $album->canciones_count }}</td>
                <td>{{ $album->interprete?->interprete ?? '-' }}</td>
                <td class="text-right" style="white-space: nowrap;">
                  <a href="{{ route('backend.discos.edit', $album) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                  @can('delete', $album)
                    <form action="{{ route('backend.discos.destroy', $album) }}" method="POST" style="display:inline-block;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger" onclick="return confirm('Estas seguro de eliminar este album?')"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No hay discos para los filtros seleccionados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer bg-white px-0 pb-0 pt-3">
        {{ $albums->links() }}
      </div>
    </div>
  </div>
@stop

@section('css')
  <style>
    .album-list-thumb { width: 50px; height: 50px; overflow: hidden; border-radius: 50%; flex-shrink: 0; }
    .album-list-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
  </style>
@stop

@section('js')
  <script>
    $(document).ready(function() {
      $('#imageModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var imageSrc = button.data('image');
        var modal = $(this);
        modal.find('#modalImage').attr('src', imageSrc);
      });
    });

    function confirmDelete(albumId) {
      confirmDialog('Esta accion no se puede deshacer', function() {
        document.getElementById(`delete-form-${albumId}`).submit();
      });
    }
  </script>
  @include('sweetalert::alert')
  @include('components.confirm_delete')
@stop
