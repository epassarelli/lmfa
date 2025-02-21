@extends('adminlte::page')

@section('title', 'Crear Noticia')

@section('css')
  <!-- Estilos de Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

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
              @error('titulo')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="slug">Slug</label>
              <input type="text" name="slug" class="form-control" required>
              @error('slug')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="noticia">Noticia</label>
          <textarea name="noticia" id="noticia" class="form-control" required></textarea>
          @error('noticia')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="row">



          <div class="col-md-6">
            <div class="form-group">
              <label for="interprete_id">Intérpretes</label>
              {{-- <select name="interprete_id[]" class="form-control select2" multiple required> --}}
              <select class="js-example-basic-multiple form-control" name="interprete_id[]" multiple="multiple">
                @foreach ($interpretes as $interprete)
                  <option value="{{ $interprete->id }}">{{ $interprete->interprete }}</option>
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
              <input type="file" name="foto" class="form-control" required>
              @error('foto')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>

        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="categoria_id">Categoría</label>
              <select name="categoria_id" id="categoria_id" class="form-control" required>
                <option value="">Seleccione una categoría</option>
                @foreach ($categorias as $categoria)
                  <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                @endforeach
              </select>
              @error('categoria_id')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="col-md-4">

          </div>
          <div class="col-md-4">

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

  <!-- Scripts de Select2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(".js-example-basic-multiple").select2({
      theme: "classic"
    });
  </script>

@stop
