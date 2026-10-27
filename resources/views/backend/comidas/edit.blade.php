@extends('adminlte::page')

@section('title', 'Editar Comida')

@section('content_header')
  <x-backend.form-header mode="edit">Editar Comida: {{ $comida->titulo }}</x-backend.form-header>
@stop

@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
  <form action="{{ route('backend.comidas.update', $comida) }}" method="POST" enctype="multipart/form-data" class="form-compact">
    @csrf
    @method('PUT')
    <div class="card">
      <div class="card-body">
        @include('backend.comidas.form')
      </div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.comidas.index')" />
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._ckeditor')
  @include('backend.partials.scripts._tooltips')
  <script>
    $(document).on('change', '.custom-file-input', function () {
      $(this).next('.custom-file-label').addClass('selected').text($(this).val().split('\\').pop());
    });
    $(function() {
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
