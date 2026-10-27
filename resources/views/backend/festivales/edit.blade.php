@extends('adminlte::page')

@section('title', 'Editar Festival')

@section('content_header')
  <x-backend.form-header mode="edit">Editar Festival: {{ $festival->title }}</x-backend.form-header>
@stop

@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
  <form action="{{ route('backend.festivales.update', $festival) }}" method="POST" enctype="multipart/form-data" class="form-compact">
    @csrf
    @method('PUT')
    <div class="card">
      <div class="card-body">
        @include('backend.festivales.form')
      </div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.festivales.index')" />
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
