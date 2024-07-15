@extends('adminlte::page')

@section('metaTitle', 'Crear Interprete')

@section('content_header')
  <span>Interpretes</span>
@stop

@section('content')

  <form action="{{ route('backend.interpretes.store') }}" method="POST">
    @csrf

    @csrf
    <div class="card">
      <div class="card-body">
        @include('backend.interpretes.form')
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

    </div>
  </form>

@endsection


<script src="https://cdn.ckeditor.com/ckeditor5/34.1.0/classic/ckeditor.js"></script>
<script>
  ClassicEditor
    .create(document.querySelector('#biografia'))
    .catch(error => {
      console.error(error);
    });
</script>
