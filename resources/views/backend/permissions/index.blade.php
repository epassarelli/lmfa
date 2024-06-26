@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestión de Permisos</h1>
@stop

@section('content')

  <div class="card">

    <div class="card-header text-right">
      {{-- @can('create noticia') --}}
      <a href="{{ route('permissions.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Permiso</a>
      {{-- @endcan --}}
    </div>

    <div class="card-body">

      <table id="permissions-table" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
@endsection

@section('js')
  <script>
    $(function() {
      $('#permissions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('permissions.index') }}',
        columns: [{
            data: 'name',
            name: 'name'
          },
          {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
          }
        ],
        order: [
          [0, 'desc']
        ]
      });
    });
  </script>
@endsection
