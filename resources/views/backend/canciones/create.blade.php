@extends('adminlte::page')

@section('title', 'Crear Canción')

@section('content_header')
  <x-backend.form-header mode="create">Crear Canción</x-backend.form-header>
@stop

@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
  <form action="{{ route('backend.canciones.store') }}" method="POST" class="form-compact">
    @csrf
    <div class="card">
      <div class="card-body">
        @include('backend.canciones.form')
      </div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.canciones.index')" />
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._select2')
  @include('backend.partials.scripts._tooltips')
  <script>
    $(function() {
      @if ($errors->any())
        Swal.fire({
          icon: 'error',
          title: 'Errores de validación',
          html: '<ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>'
        });
      @endif

      $('#interprete_id').change(function() {
        var interpreteId = $(this).val();
        if (interpreteId) {
          $.ajax({
            url: '{{ url('albums') }}/' + interpreteId,
            type: 'GET',
            success: function(data) {
              $('#album_id').html(data);
            }
          });
        }
      });
    });
  </script>
@stop
