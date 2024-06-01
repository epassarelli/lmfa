@extends('adminlte::page')

@section('title', 'Editar Noticia')

@section('content_header')
  <h1>Editar Noticia</h1>
@stop

@section('content')
  <div class="card">
    <div class="card-body">
      <form action="{{ route('noticias.update', $noticia) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label for="titulo">Título</label>
          <input type="text" name="titulo" class="form-control" value="{{ $noticia->titulo }}" required>
        </div>
        <div class="form-group">
          <label for="slug">Slug</label>
          <input type="text" name="slug" class="form-control" value="{{ $noticia->slug }}" required>
        </div>
        <div class="form-group">
          <label for="noticia">Noticia</label>
          <textarea name="noticia" id="editor" class="form-control" required>{{ $noticia->noticia }}</textarea>
        </div>
        <div class="form-group">
          <label for="interprete_id">Intérprete</label>
          <select name="interprete_id" class="form-control" required>
            @foreach ($interpretes as $interprete)
              <option value="{{ $interprete->id }}" {{ $noticia->interprete_id == $interprete->id ? 'selected' : '' }}>
                {{ $interprete->interprete }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="foto">Foto</label>
          <input type="file" name="foto" class="form-control">
          @if ($noticia->foto)
            <img src="{{ asset('storage/' . $noticia->foto) }}" alt="Foto actual" class="img-fluid mt-2">
          @endif
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
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
