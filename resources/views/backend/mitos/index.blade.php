@extends('adminlte::page')

@section('title', 'Mitos')

@section('content_header')
  <h1>Gestión de Mitos</h1>
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
      <a href="{{ route('backend.mitos.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Mito</a>
    </div>
    <div class="card-body">
      <table id="mitos-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Título</th>
            {{-- <th>Usuario</th> --}}
            <th>Caract's</th>
            <th>Visitas</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($mitos as $mito)
            <tr>
              <td>{{ $mito->titulo }}</td>
              {{-- <td>{{ $mito->user->name }}</td> --}}
              <td>{{ strlen(strip_tags($mito->mito)) }}</td>
              <td>{{ $mito->visitas }}</td>
              <td>{{ $mito->estado == 1 ? 'Activo' : 'Inactivo' }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.mitos.edit', $mito) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @can('delete', $mito)
                  <form action="{{ route('backend.mitos.destroy', $mito) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                      onclick="return confirm('¿Estás seguro de eliminar este mito?')">
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
      $('#mitos-table').DataTable({
        language: {
          "decimal": "",
          "emptyTable": "No hay información",
          "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
          "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
          "infoFiltered": "(Filtrado de _MAX_ total entradas)",
          "infoPostFix": "",
          "thousands": ",",
          "lengthMenu": "Mostrar _MENU_ entradas",
          "loadingRecords": "Cargando...",
          "processing": "Procesando...",
          "search": "Buscar:",
          "zeroRecords": "No se encontraron resultados",
          "paginate": {
            "first": "Primero",
            "last": "Último",
            "next": "Siguiente",
            "previous": "Anterior"
          },
          "aria": {
            "sortAscending": ": activar para ordenar la columna en orden ascendente",
            "sortDescending": ": activar para ordenar la columna en orden descendente"
          }
        }
      });
    });
  </script>
@stop
