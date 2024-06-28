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
        @include('canciones.form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
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
