@extends('adminlte::page')

@section('title', 'Editar Noticia')

@section('content_header')
  <h1>Editar Interprete</h1>
@stop

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-12">

        <form action="{{ route('backend.interpretes.update', $interprete->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label for="interprete" class="form-label">Interprete</label>
            <input type="text" name="interprete" class="form-control" id="interprete"
              value="{{ old('interprete', $interprete->interprete) }}" required>
          </div>
          <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" id="slug"
              value="{{ old('slug', $interprete->slug) }}" required>
          </div>
          <div class="mb-3">
            <label for="foto" class="form-label">Foto</label>
            <input type="text" name="foto" class="form-control" id="foto"
              value="{{ old('foto', $interprete->foto) }}" required>
          </div>
          <div class="mb-3">
            <label for="biografia" class="form-label">Biografia</label>
            <textarea name="biografia" class="form-control" id="editor" required>{{ old('biografia', $interprete->biografia) }}</textarea>
          </div>
          <div class="mb-3">
            <label for="telefono" class="form-label">Telefono</label>
            <input type="text" name="telefono" class="form-control" id="telefono"
              value="{{ old('telefono', $interprete->telefono) }}" required>
          </div>
          <div class="mb-3">
            <label for="correo" class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" id="correo"
              value="{{ old('correo', $interprete->correo) }}" required>
          </div>
          <div class="mb-3">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" name="instagram" class="form-control" id="instagram"
              value="{{ old('instagram', $interprete->instagram) }}">
          </div>
          <div class="mb-3">
            <label for="twitter" class="form-label">Twitter</label>
            <input type="text" name="twitter" class="form-control" id="twitter"
              value="{{ old('twitter', $interprete->twitter) }}">
          </div>
          <div class="mb-3">
            <label for="youtube" class="form-label">YouTube</label>
            <input type="text" name="youtube" class="form-control" id="youtube"
              value="{{ old('youtube', $interprete->youtube) }}">
          </div>
          <div class="mb-3">
            <label for="visitas" class="form-label">Visitas</label>
            <input type="number" name="visitas" class="form-control" id="visitas"
              value="{{ old('visitas', $interprete->visitas) }}" required>
          </div>
          <div class="mb-3">
            <label for="publicar" class="form-label">Fecha de Publicación</label>
            <input type="datetime-local" name="publicar" class="form-control" id="publicar"
              value="{{ old('publicar', $interprete->publicar) }}" required>
          </div>
          <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" class="form-control" id="estado" required>
              <option value="1" {{ old('estado', $interprete->estado) == 1 ? 'selected' : '' }}>Activo</option>
              <option value="0" {{ old('estado', $interprete->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="user_id" class="form-label">Usuario</label>
            <input type="number" name="user_id" class="form-control" id="user_id"
              value="{{ old('user_id', $interprete->user_id) }}">
          </div>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
@stop

@section('js')
  <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
  <script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
  </script>
@stop
