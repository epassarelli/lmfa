@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content')
  <div class="container">
    <h1>Editar Usuario</h1>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label for="name">Nombre</label>
        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        @error('name')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        @error('email')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label for="password">Contraseña (dejar en blanco para no cambiar)</label>
        <input type="password" name="password" class="form-control">
        @error('password')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirmar Contraseña</label>
        <input type="password" name="password_confirmation" class="form-control">
        @error('password_confirmation')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-group">
        <label for="roles">Roles</label>
        <select name="roles[]" class="form-control" multiple required>
          @foreach ($roles as $role)
            <option value="{{ $role->name }}" {{ in_array($role->name, $userRoles) ? 'selected' : '' }}>
              {{ $role->name }}</option>
          @endforeach
        </select>
        @error('roles')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
  </div>
@endsection
