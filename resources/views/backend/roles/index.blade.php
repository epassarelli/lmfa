@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestión de Roles</h1>
@stop

@section('content')

  <div class="card">

    <div class="card-header text-right">
      {{-- @can('create noticia') --}}
      <a href="{{ route('roles.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Rol</a>
      {{-- @endcan --}}
    </div>

    <div class="card-body">

      <table id="roles-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Permisos</th>
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
      $('#roles-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('roles.index') }}',
        columns: [{
            data: 'name',
            name: 'name'
          },
          {
            data: 'permissions',
            name: 'permissions',
            orderable: false,
            searchable: false
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
