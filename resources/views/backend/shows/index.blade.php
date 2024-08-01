@extends('adminlte::page')

@section('title', 'Shows')

@section('content_header')
  <span>Gestión de Shows</span>
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
      <a href="{{ route('backend.shows.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Show</a>
    </div>
    <div class="card-body">
      <table id="shows-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Show</th>
            <th>Intérprete</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($shows as $show)
            <tr>
              <td>{{ $show->fecha }}</td>
              <td>{{ $show->show }}</td>
              <td>{{ $show->interprete->interprete }}</td>
              <td>{{ $show->estado == 1 ? 'Activo' : 'Inactivo' }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.shows.edit', $show) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @can('delete', $show)
                  <form action="{{ route('backend.shows.destroy', $show) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                      onclick="return confirm('¿Estás seguro de eliminar este show?')">
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
      $('#shows-table').DataTable();
    });
  </script>
@stop
