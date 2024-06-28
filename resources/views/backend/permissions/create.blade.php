@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content')
  <div class="container">
    <h1>Crear Permiso</h1>
    <form action="{{ route('permissions.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="name">Nombre del Permiso</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
  </div>
@endsection
