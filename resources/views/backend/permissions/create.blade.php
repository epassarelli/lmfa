@extends('adminlte::page')

@section('title', 'Crear Permiso')

@section('content_header')
  <x-backend.form-header mode="create">Crear Permiso</x-backend.form-header>
@stop

@section('content')
  <div class="card">
    <form action="{{ route('permissions.store') }}" method="POST">
      @csrf
      <div class="card-body">
        <div class="form-group">
          <label for="name">Nombre del Permiso</label>
          <input type="text" name="name" id="name" class="form-control" required>
        </div>
      </div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('permissions.index')" />
      </div>
    </form>
  </div>
@endsection
