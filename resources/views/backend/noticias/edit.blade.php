@extends('adminlte::page')

@section('title', 'Editar Noticia')

@section('content_header')
  <span>Editar Noticia</span>
@stop

@section('content')

  <div class="card">
    <div class="card-body">

      <form action="{{ route('backend.noticias.update', $noticia) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="titulo">Título</label>
              <input type="text" name="titulo" class="form-control" value="{{ $noticia->titulo }}" required>
              @error('titulo')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="slug">Slug</label>
              <input type="text" name="slug" class="form-control" value="{{ $noticia->slug }}" required>
              @error('slug')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="noticia">Noticia</label>
          <textarea name="noticia" id="editor" class="form-control" required>{{ $noticia->noticia }}</textarea>
          @error('noticia')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="row">

          <div class="col-md-6">
            <div class="form-group">
              <label for="interprete_id">Intérpretes</label>
              <select name="interprete_id[]" class="form-control" multiple required>
                @foreach ($interpretes as $interprete)
                  <option value="{{ $interprete->id }}"
                    {{ in_array($interprete->id, $noticia->interpretes->pluck('id')->toArray()) ? 'selected' : '' }}>
                    {{ $interprete->interprete }}
                  </option>
                @endforeach
              </select>
              @error('interprete_id')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>


          <div class="col-md-6">
            <div class="form-group">
              <label for="foto">Foto</label>
              <input type="file" name="foto" class="form-control">
              @if ($noticia->foto)
                <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="Foto actual" class="img-fluid mt-2">
              @endif
              @error('foto')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md">
            <div class="form-group">
              <label for="categoria_id">Categoría</label>
              <select name="categoria_id" id="categoria_id" class="form-control" required>
                <option value="">Seleccione una categoría</option>
                @foreach ($categorias as $categoria)
                  <option value="{{ $categoria->id }}" {{ $noticia->categoria_id == $categoria->id ? 'selected' : '' }}>
                    {{ $categoria->nombre }}
                  </option>
                @endforeach
              </select>
              @error('categoria_id')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-md">

          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="estado" class="form-label">Estado</label>
              <select name="estado" class="form-control" id="estado" required>
                <option value="1" {{ old('estado', $noticia->estado ?? '') == 1 ? 'selected' : '' }}>Activo
                </option>
                <option value="0" {{ old('estado', $noticia->estado ?? '') == 0 ? 'selected' : '' }}>Inactivo
                </option>
              </select>
              @error('estado')
                <div class="text-danger">{{ $message }}</div>
              @enderror
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
  <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
  <script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
  </script>
@stop
