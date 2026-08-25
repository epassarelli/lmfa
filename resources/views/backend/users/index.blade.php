@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestion de Usuarios</h1>
@stop

@section('content')

  <div class="card card-outline card-primary shadow-sm">

    <div class="card-header">
      <h3 class="card-title">Listado de Usuarios</h3>
      <div class="card-tools">
        @can('create noticia')
          <a href="{{ route('users.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Crear Usuario</a>
        @endcan
      </div>
    </div>


    <div class="card-body">
      <x-admin-listing-toolbar
        :action="route('users.index')"
        search-placeholder="Buscar por nombre, email o rol"
        :sort-options="[
          'name' => 'Nombre',
          'email' => 'Email',
          'id' => 'ID',
        ]"
      />

      <div class="table-responsive">
      <table class="table table-hover mb-0">

        <thead class="thead-light">
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
      </div>

      <div class="card-footer bg-white px-0 pb-0 pt-3">
        {{ $data->links() }}
      </div>
    </div>
  </div>
@endsection
