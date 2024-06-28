@extends('adminlte::page')

@section('title', 'Editar Canción')

@section('content_header')
  <h1>Editar Canción</h1>
@stop

@section('content')
  <form action="{{ route('backend.canciones.update', $cancion) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card">
      <div class="card-body">
        @include('canciones.form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Actualizar</button>
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

      // Inicializar la carga de álbumes en la edición
      var interpreteId = $('#interprete_id').val();
      if (interpreteId) {
        $.ajax({
          url: '{{ url('albums') }}/' + interpreteId,
          type: 'GET',
          success: function(data) {
            $('#album_id').html(data).val('{{ $cancion->album_id }}');
          }
        });
      }
    });
  </script>
@stop
