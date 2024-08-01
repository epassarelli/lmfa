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

  <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>

  <script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));

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
