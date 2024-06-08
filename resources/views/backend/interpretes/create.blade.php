@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Interpretes</h1>
@stop

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1 class="mb-3">Crear Interprete</h1>
        <form action="{{ route('interpretes.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="interprete" class="form-label">Interprete</label>
            <input type="text" name="interprete" class="form-control" id="interprete" value="{{ old('interprete') }}"
              required>
          </div>
          <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" id="slug" value="{{ old('slug') }}" required>
          </div>
          <div class="mb-3">
            <label for="foto" class="form-label">Foto</label>
            <input type="text" name="foto" class="form-control" id="foto" value="{{ old('foto') }}" required>
          </div>
          <div class="mb-3">
            <label for="biografia" class="form-label">Biografia</label>
            <textarea name="biografia" class="form-control" id="biografia" required>{{ old('biografia') }}</textarea>
          </div>
          <div class="mb-3">
            <label for="telefono" class="form-label">Telefono</label>
            <input type="text" name="telefono" class="form-control" id="telefono" value="{{ old('telefono') }}"
              required>
          </div>
          <div class="mb-3">
            <label for="correo" class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" id="correo" value="{{ old('correo') }}" required>
          </div>
          <div class="mb-3">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" name="instagram" class="form-control" id="instagram" value="{{ old('instagram') }}">
          </div>
          <div class="mb-3">
            <label for="twitter" class="form-label">Twitter</label>
            <input type="text" name="twitter" class="form-control" id="twitter" value="{{ old('twitter') }}">
          </div>
          <div class="mb-3">
            <label for="youtube" class="form-label">YouTube</label>
            <input type="text" name="youtube" class="form-control" id="youtube" value="{{ old('youtube') }}">
          </div>
          <div class="mb-3">
            <label for="visitas" class="form-label">Visitas</label>
            <input type="number" name="visitas" class="form-control" id="visitas" value="{{ old('visitas') }}"
              required>
          </div>
          <div class="mb-3">
            <label for="publicar" class="form-label">Fecha de Publicación</label>
            <input type="datetime-local" name="publicar" class="form-control" id="publicar"
              value="{{ old('publicar') }}" required>
          </div>
          <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" class="form-control" id="estado" required>
              <option value="1" {{ old('estado') == 1 ? 'selected' : '' }}>Activo</option>
              <option value="0" {{ old('estado') == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="user_id" class="form-label">Usuario</label>
            <input type="number" name="user_id" class="form-control" id="user_id" value="{{ old('user_id') }}">
          </div>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
@endsection


<script src="https://cdn.ckeditor.com/ckeditor5/34.1.0/classic/ckeditor.js"></script>
<script>
  ClassicEditor
    .create(document.querySelector('#biografia'))
    .catch(error => {
      console.error(error);
    });
</script>
