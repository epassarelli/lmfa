@extends('adminlte::page')

@section('metaTitle', 'Álbumes')

@section('content_header')
  <h1>Gestión de Discos</h1>
@stop

@section('content')

  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: '{{ session('success') }}'
      });
    </script>
  @endif

  <div class="card">
    <div class="card-header text-right">
      <a href="{{ route('backend.discos.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Disco</a>
    </div>
    <div class="card-body">
      <table id="albums-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Álbum</th>
            <th>Año</th>
            <th>Intérprete</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($albums as $album)
            <tr>
              <td>
                <img src="{{ asset('storage/albunes/' . $album->foto) }}" alt="Foto de {{ $album->album }}"
                  style="max-height: 80px;">
              </td>
              <td>{{ $album->album }}</td>
              <td>{{ $album->anio }}</td>
              <td>{{ $album->interprete->interprete }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.discos.edit', $album) }}" class="btn btn-warning"><i
                    class="fas fa-edit"></i></a>
                @can('delete', $album)
                  <form action="{{ route('backend.discos.destroy', $album) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                      onclick="return confirm('¿Estás seguro de eliminar este álbum?')"><i
                        class="fas fa-trash-alt"></i></button>
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
      $('#albums-table').DataTable();

      $('#imageModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var imageSrc = button.data('image');
        var modal = $(this);
        modal.find('#modalImage').attr('src', imageSrc);
      });


    });

    function confirmDelete(albumId) {
      confirmDialog('Esta acción no se puede deshacer', function() {
        document.getElementById(`delete-form-${albumId}`).submit();
      });
    }
  </script>
  @include('sweetalert::alert')
  @include('components.confirm_delete')
@stop
