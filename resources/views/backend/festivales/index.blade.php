@extends('adminlte::page')

@section('metaTitle', 'Listado de Festivales')

@section('content_header')
  <h1>Gestión de Festivales</h1>
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
      <a href="{{ route('backend.festivales.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear
        Festival</a>
    </div>
    <div class="card-body">
      <table id="festivales-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Título</th>
            <th>Caract's</th>
            <th>Provincia</th>
            <th>Mes</th>
            <th>Visitas</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($festivales as $festival)
            <tr>
              <td>{{ $festival->titulo }}</td>
              <td>{{ strlen(strip_tags($festival->detalle)) }}</td>
              <td>{{ $festival->provincia_id }}</td>
              <td>{{ $festival->mes_id }}</td>
              <td>{{ $festival->visitas }}</td>
              <td>{{ $festival->estado == 1 ? 'Activo' : 'Inactivo' }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.festivales.edit', $festival) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @can('delete', $festival)
                  <form action="{{ route('backend.festivales.destroy', $festival) }}" method="POST"
                    style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                      onclick="return confirm('¿Estás seguro de eliminar este festival?')">
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
