@extends('adminlte::page')

@section('title', 'Canciones')

@section('content_header')
  <h1>Gestión de Canciones</h1>
@stop

@section('content')
  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: '{{ session('
                                                                                                    success ') }}'
      });
    </script>
  @endif
  <div class="card">
    <div class="card-header text-right">
      <a href="{{ route('backend.canciones.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Canción</a>
    </div>

    <div class="card-body">
      <table id="canciones-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Canción</th>
            <th>Intérprete</th>
            <th>Visitas</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>


    {{-- <tbody>
          @foreach ($canciones as $cancion)
            <tr>
              <td>{{ $cancion->cancion }}</td>
              <td>{{ $cancion->slug }}</td>
              <td>{{ $cancion->visitas }}</td>
              <td>{{ $cancion->estado == 1 ? 'Activo' : 'Inactivo' }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.canciones.edit', $cancion) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @can('delete', $cancion)
                  <form action="{{ route('backend.canciones.destroy', $cancion) }}" method="POST"
                    style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                      onclick="return confirm('¿Estás seguro de eliminar esta canción?')">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                @endcan
              </td>
            </tr>
          @endforeach
        </tbody> --}}




    </table>
  </div>
  </div>
@stop

@section('js')
  <script>
    // $(function() {
    //   $('#canciones-table').DataTable();
    // });


    $(function() {
      $('#canciones-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('backend.canciones.get') }}',
        columns: [{
            data: 'cancion',
            name: 'cancion'
          },
          {
            data: 'interprete',
            name: 'interprete'
          },
          {
            data: 'visitas',
            name: 'visitas'
          },
          {
            data: 'estado',
            name: 'estado'
          },
          {
            data: 'acciones',
            name: 'acciones',
            orderable: false,
            searchable: false
          }
        ],
        // language: {
        //   url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
        // }
      });
    });
  </script>
@stop
