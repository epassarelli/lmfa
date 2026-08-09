@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestion de Usuarios</h1>
@stop

@section('content')

  <div class="card">

    <div class="card-header text-right">
      @can('create noticia')
        <a href="{{ route('users.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Usuario</a>
      @endcan
    </div>


    <div class="card-body">
      <table class="table table-striped table-bordered table-hover">

        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Acciones</th>
          </tr>
        </thead>

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
                    onclick="return confirm('Estas seguro de eliminar este usuario?')">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>

      </table>

      <div class="mt-3">
        {{ $data->links() }}
      </div>
    </div>
  </div>
@endsection
