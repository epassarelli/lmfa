@extends('adminlte::page')

@section('title', 'Editar Interprete')

@section('content_header')
  <span>Editar Interprete</span>
@stop

@section('content')


  <form action="{{ route('backend.interpretes.update', $interprete->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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
  @stop

  @section('js')
    <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
    <script>
      ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
    </script>
  @stop
