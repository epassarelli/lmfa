@extends('adminlte::page')

@section('title', 'Nuevo Evento')

@section('content_header')
    <h1><i class="fas fa-calendar-plus mr-2"></i>{{ $event->program_id ? 'Agregar jornada al evento' : 'Crear Nuevo Evento' }}</h1>
@stop

@section('content')
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row">
    <div class="col-md-10 mx-auto">
        <form action="{{ route('backend.events.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($event->program_id)<p class="alert alert-info">Esta jornada forma parte de un evento. <a href="{{ route('backend.event-programs.edit', $event->program_id) }}">Volver a sus datos comunes y jornadas</a>.</p>@endif
            @if(!$event->program_id)
            <div class="card"><div class="card-body">
                <fieldset><legend class="h6">¿Cómo se realiza el evento?</legend>
                <label class="mr-4"><input type="radio" name="event_format" value="single" @checked(old('event_format', request('format', 'single')) !== 'multiple')> Una fecha</label>
                <label><input type="radio" name="event_format" value="multiple" @checked(old('event_format', request('format')) === 'multiple')> Varias jornadas</label>
                <p class="text-muted mb-0">Si se realiza en varios días, agregá una jornada por fecha con sus horarios y artistas. El nombre, detalle, lugar e imagen se cargan una sola vez.</p></fieldset>
            </div></div>
            @endif

            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    @include('backend.events.form')
                </div>
            </div>

            @if(!$event->program_id)
            <fieldset id="multiple-event-fields" hidden disabled>
                @include('backend.events._sessions')
            </fieldset>
            @endif

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
    @include('backend.partials.scripts._select2')
    @include('backend.partials.scripts._ckeditor')
    @include('backend.events._venue_script')
    @include('backend.events._sessions_script')
    <script>
        $(document).ready(function () {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

        });
    </script>
@stop
