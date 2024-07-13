@extends('adminlte::page')

@section('title', 'Crear Noticia')

@section('content_header')
  <span>Crear Noticia</span>
@stop

@section('content')

  <div class="card">
    <div class="card-body">

      <form action="{{ route('backend.noticias.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="titulo">Título</label>
              <input type="text" name="titulo" class="form-control" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="slug">Slug</label>
              <input type="text" name="slug" class="form-control" required>
            </div>
          </div>
        </div>


        <div class="form-group">
          <label for="noticia">Noticia</label>
          <textarea name="noticia" id="editor" class="form-control" required></textarea>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="interprete_id">Intérprete</label>
              <select name="interprete_id" class="form-control" required>
                @foreach ($interpretes as $interprete)
                  <option value="{{ $interprete->id }}">{{ $interprete->interprete }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="foto">Foto</label>
              <input type="file" name="foto" class="form-control" required>
            </div>
          </div>
        </div>
    </div>

    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Guardar
      </button>
      <button type="reset" class="btn btn-secondary">
        <i class="fas fa-undo"></i> Reset
      </button>
      <a href="{{ url()->previous() }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al listado
      </a>
    </div>

    </form>


  </div>

@stop

@section('js')
  <script src="{{ asset('path/to/ckeditor.js') }}"></script>
  <script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
  </script>
@stop
