@extends('adminlte::page')

@section('title', 'Editar Canción')

@section('content_header')
  <x-backend.form-header mode="edit">Editar Canción: {{ $cancion->cancion }}</x-backend.form-header>
@stop

@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
  <form action="{{ route('backend.canciones.update', $cancion) }}" method="POST" class="form-compact">
    @csrf
    @method('PUT')

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
