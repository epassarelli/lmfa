@extends('adminlte::page')

@section('title', 'Editar Álbum')

@section('content_header')
  <h1>Editar Álbum</h1>
@stop

@section('content')
  <form action="{{ route('backend.albums.update', $album) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
      <div class="card-body">
        @include('backend.albunes.form')
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
    });
  </script>
@stop
