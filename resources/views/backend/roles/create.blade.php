@extends('adminlte::page')

@section('title', 'Crear Rol')

@section('content_header')
  <x-backend.form-header mode="create">Crear Rol</x-backend.form-header>
@stop

@section('content')
  <div class="card">
    <form action="{{ route('roles.store') }}" method="POST">
      @csrf
      <div class="card-body">
        <div class="form-group">
          <label for="name">Nombre del Rol</label>
          <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="permissions">Permisos</label>
          <select name="permissions[]" id="permissions" class="form-control" multiple required>
            @foreach ($permissions as $permission)
              <option value="{{ $permission->name }}">{{ $permission->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('roles.index')" />
      </div>
    </form>
  </div>
@endsection
