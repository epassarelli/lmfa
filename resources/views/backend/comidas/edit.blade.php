@extends('adminlte::page')

@section('title', 'Editar Comida')

@section('content_header')
  <h1>Editar Comida</h1>
@stop

@section('content')
  <form action="{{ route('backend.comidas.update', $comida) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
      <div class="card-body">
        @include('backend.comidas.form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>
    </div>
  </form>
@stop

@section('js')
  <script src="{{ asset('vendor/ckeditor5/build/ckeditor.js') }}"></script>
  <script>
    $(function() {
      ClassicEditor
        .create(document.querySelector('#receta'))
        .catch(error => {
          console.error(error);
        });

      @if ($errors->any())
        Swal.fire({
          icon: 'error',
          title: 'Errores de validación',
          html: '<ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>'
        });
      @endif
    });
  </script>
@stop