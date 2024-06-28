@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestión de Usuarios</h1>
@stop

@section('content')

  <div class="card">

    <div class="card-header text-right">
      @can('create noticia')
        <a href="{{ route('users.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Usuario</a>
      @endcan
    </div>


    <div class="card-body">
      <table id="users-table" class="table table-striped table-bordered table-hover">

        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Acciones</th>
          </tr>
        </thead>
        {{-- <tbody></tbody> --}}

        <tbody>
          @foreach ($data as $user)
            <tr>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger"
                    onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>

      </table>
    </div>
  </div>
@endsection

@section('js')

  <script>
    $(document).ready(function() {
      $('#users-table').DataTable({
        // Aquí puedes configurar DataTables sin server-side
        processing: true,
        // No uses server-side aquí
        order: [
          [0, 'desc']
        ]
      });
    });
  </script>
  {{-- <script>
    $(function() {
      $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('users.index') }}',
        columns: [{
            data: 'name',
            name: 'name'
          },
          {
            data: 'email',
            name: 'email'
          },
          {
            data: 'roles',
            name: 'roles',
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
  </script> --}}
@endsection
