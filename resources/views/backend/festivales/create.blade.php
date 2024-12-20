@extends('adminlte::page')

@section('title', 'Crear Festival')

@section('content_header')
  <h1>Crear Festival</h1>
@stop

@section('content')
  <form action="{{ route('backend.festivales.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card">
      <div class="card-body">
        @include('backend.festivales.form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </form>
@stop

@section('js')
  <script src="https://cdn.ckeditor.com/ckeditor5/29.0.0/classic/ckeditor.js"></script>
  <script>
    $(function() {
      @if ($errors->any())
        Swal.fire({
          icon: 'error',
          title: 'Errores de validación',
          html: '<ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>'
        });
      @endif

      ClassicEditor
        .create(document.querySelector('#detalle'))
        .catch(error => {
          console.error(error);
        });
    });
  </script>
@stop
