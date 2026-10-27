@extends('adminlte::page')

@section('title', 'Editar Permiso')

@section('content_header')
  <x-backend.form-header mode="edit">Editar Permiso: {{ $permission->name }}</x-backend.form-header>
@stop

@section('content')
  <div class="card">
    <form action="{{ route('permissions.update', $permission->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card-body">
        <div class="form-group">
          <label for="name">Nombre del Permiso</label>
          <input type="text" name="name" id="name" class="form-control" value="{{ $permission->name }}" required>
        </div>
      </div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('permissions.index')" />
      </div>
    </form>
  </div>
@endsection
