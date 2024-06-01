@extends('adminlte::page')

@section('title', 'Crear Noticia')

@section('content_header')
  <h1>Crear Noticia</h1>
@stop

@section('content')
  <div class="card">
    <div class="card-body">
      <form action="{{ route('noticias.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label for="titulo">Título</label>
          <input type="text" name="titulo" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="slug">Slug</label>
          <input type="text" name="slug" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="noticia">Noticia</label>
          <textarea name="noticia" id="editor" class="form-control" required></textarea>
        </div>
        <div class="form-group">
          <label for="interprete_id">Intérprete</label>
          <select name="interprete_id" class="form-control" required>
            @foreach ($interpretes as $interprete)
              <option value="{{ $interprete->id }}">{{ $interprete->interprete }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="foto">Foto</label>
          <input type="file" name="foto" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </form>
    </div>
  </div>
@stop

@section('js')
  <script src="{{ asset('path/to/ckeditor.js') }}"></script>
  <script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
  </script>
@stop
