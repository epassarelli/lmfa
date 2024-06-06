@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Interpretes</h1>
@stop

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1 class="mb-3">Mostrar Interprete</h1>
        <div class="mb-3">
          <label for="interprete" class="form-label">Interprete</label>
          <p id="interprete">{{ $interprete->interprete }}</p>
        </div>
        <div class="mb-3">
          <label for="slug" class="form-label">Slug</label>
          <p id="slug">{{ $interprete->slug }}</p>
        </div>
        <div class="mb-3">
          <label for="foto" class="form-label">Foto</label>
          <p id="foto">{{ $interprete->foto }}</p>
        </div>
        <div class="mb-3">
          <label for="biografia" class="form-label">Biografia</label>
          <p id="biografia">{{ $interprete->biografia }}</p>
        </div>
        <div class="mb-3">
          <label for="telefono" class="form-label">Telefono</label>
          <p id="telefono">{{ $interprete->telefono }}</p>
        </div>
        <div class="mb-3">
          <label for="correo" class="form-label">Correo</label>
          <p id="correo">{{ $interprete->correo }}</p>
        </div>
        <div class="mb-3">
          <label for="instagram" class="form-label">Instagram</label>
          <p id="instagram">{{ $interprete->instagram }}</p>
        </div>
        <div class="mb-3">
          <label for="twitter" class="form-label">Twitter</label>
          <p id="twitter">{{ $interprete->twitter }}</p>
        </div>
        <div class="mb-3">
          <label for="youtube" class="form-label">YouTube</label>
          <p id="youtube">{{ $interprete->youtube }}</p>
        </div>
        <div class="mb-3">
          <label for="visitas" class="form-label">Visitas</label>
          <p id="visitas">{{ $interprete->visitas }}</p>
        </div>
        <div class="mb-3">
          <label for="publicar" class="form-label">Fecha de Publicación</label>
          <p id="publicar">{{ $interprete->publicar }}</p>
        </div>
        <div class="mb-3">
          <label for="estado" class="form-label">Estado</label>
          <p id="estado">{{ $interprete->estado == 1 ? 'Activo' : 'Inactivo' }}</p>
        </div>
        <div class="mb-3">
          <label for="user_id" class="form-label">Usuario</label>
          <p id="user_id">{{ $interprete->user_id }}</p>
        </div>
        <a href="{{ route('crud.interpretes.index') }}" class="btn btn-secondary">Volver</a>
      </div>
    </div>
  </div>
@endsection
