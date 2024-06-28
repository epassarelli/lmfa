@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content')
  <div class="container">
    <h1>Editar Rol</h1>
    <form action="{{ route('roles.update', $role->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label for="name">Nombre del Rol</label>
        <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
      </div>
      <div class="form-group">
        <label for="permissions">Permisos</label>
        <select name="permissions[]" class="form-control" multiple required>
          @foreach ($permissions as $permission)
            <option value="{{ $permission->name }}" {{ in_array($permission->name, $rolePermissions) ? 'selected' : '' }}>
              {{ $permission->name }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
  </div>
@endsection
