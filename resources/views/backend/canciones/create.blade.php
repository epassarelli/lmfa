@extends('adminlte::page')

@section('title', 'Crear Canción')

@section('content_header')
  <h1>Crear Canción</h1>
@stop

@section('content')
  <form action="{{ route('backend.canciones.store') }}" method="POST">
    @csrf
    <div class="card">
      <div class="card-body">
        @include('backend.canciones.form')
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
@stop

@section('js')
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
