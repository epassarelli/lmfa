@extends('adminlte::page')

@section('metaTitle', 'Crear Interprete')

@section('content_header')
  <x-backend.form-header mode="create">Crear Intérprete</x-backend.form-header>
@endsection

@section('css')
  @include('backend.partials.styles._compact-form')
@endsection

@section('content')

  <form action="{{ route('backend.interpretes.store') }}" method="POST" enctype="multipart/form-data" class="form-compact">
    @csrf

    <div class="card">
      <div class="card-body">
        @include('backend.interpretes.form')
      </div>

      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.interpretes.index')" />
      </div>

    </div>
  </form>

@endsection

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
@endsection

