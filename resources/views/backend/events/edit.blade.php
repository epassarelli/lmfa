@extends('adminlte::page')

@section('title', 'Editar Evento')

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Editar Evento: {{ $event->title }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <form action="{{ route('backend.events.update', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    @include('backend.events.form')
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Imagen Destacada</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            @if ($event->images->isNotEmpty())
                                <p><strong>Imagen actual:</strong></p>
                                <x-optimized-image :image="$event->images->first()" variant="card" class="img-thumbnail shadow-sm mb-3" style="max-height: 200px;" />
                            @else
                                <div class="alert alert-light border text-muted">
                                    <i class="fas fa-image fa-2x mb-2 d-block"></i>
                                    Sin imagen cargada.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="imagen_destacada">Cambiar Imagen</label>
                                <div class="custom-file">
                                    <input type="file" name="imagen_destacada" class="custom-file-input" id="imagen_destacada" accept="image/*">
                                    <label class="custom-file-label" for="imagen_destacada">Elegir nuevo archivo...</label>
                                </div>
                                <small class="text-muted">Si no seleccionas un archivo, se mantendrá la imagen actual.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('backend.events.index') }}" class="btn btn-default mr-2">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-save mr-1"></i> Actualizar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
    <script>
        $(document).ready(function () {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@stop
