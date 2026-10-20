@extends('adminlte::page')

@section('title', 'Crear Festival')

@section('content_header')
  <h1>Crear Festival</h1>
@stop

@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
  <form action="{{ route('backend.festivales.store') }}" method="POST" enctype="multipart/form-data" class="form-compact">
    @csrf
    <div class="card">
      <div class="card-body">
        @include('backend.festivales.form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('backend.festivales.index') }}" class="btn btn-default">Cancelar</a>
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._ckeditor')
  @include('backend.partials.scripts._slug')
  @include('backend.partials.scripts._select2')
  @include('backend.partials.scripts._tooltips')
  <script>
    $(document).ready(function () {
      $('.custom-file-input').on('change', function () {
        $(this).next('.custom-file-label').addClass('selected').text($(this).val().split('\\').pop());
      });
    });
  </script>
@stop
