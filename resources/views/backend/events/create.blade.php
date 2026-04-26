@extends('adminlte::page')

@section('title', 'Nuevo Evento')

@section('content_header')
    <h1><i class="fas fa-calendar-plus mr-2"></i>Crear Nuevo Evento</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <form action="{{ route('backend.events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card card-outline card-success">
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
                    <div class="form-group">
                        <label for="imagen_destacada">Seleccionar Imagen</label>
                        <div class="custom-file">
                            <input type="file" name="imagen_destacada" class="custom-file-input" id="imagen_destacada" accept="image/*">
                            <label class="custom-file-label" for="imagen_destacada">Elegir archivo...</label>
                        </div>
                        <small class="text-muted">Formatos aceptados: JPG, PNG, WEBP. Tamaño máximo recomendado: 2MB.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-save mr-1"></i> Guardar Evento
                    </button>
                    <a href="{{ route('backend.events.index') }}" class="btn btn-default float-right">
                        Cancelar
                    </a>
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
