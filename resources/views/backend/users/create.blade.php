@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content')
  <div class="container">
    <h1>Crear Usuario</h1>
    <form action="{{ route('users.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="name">Nombre</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirmar Contraseña</label>
        <input type="password" name="password_confirmation" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="roles">Roles</label>
        <select name="roles[]" class="form-control" multiple required>
          @foreach ($roles as $role)
            <option value="{{ $role->name }}">{{ $role->name }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
  </div>
@endsection
