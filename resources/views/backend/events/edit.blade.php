@extends('adminlte::page')

@section('title', 'Editar Evento')

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>{{ $event->program_id ? 'Editar jornada' : 'Editar evento' }}: {{ $event->title }}</h1>
@stop

@section('css')
    @include('backend.partials.styles._compact-form')
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(!$event->program_id)
<div class="card"><div class="card-body"><p>¿Este evento se realiza en más de una fecha? Agregá jornadas con sus propios horarios y artistas. La fecha actual se conserva como primera jornada.</p>
<form method="POST" action="{{ route('backend.events.sessions', $event) }}">@csrf<button class="btn btn-outline-primary">Agregar jornadas al evento</button></form></div></div>
@endif
<div class="row">
    <div class="col-12">
        <form action="{{ route('backend.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="form-compact">
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

            <div class="mb-3 text-right">
                <a href="{{ route('backend.events.index') }}" class="btn btn-default mr-2">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="fas fa-save mr-1"></i> Actualizar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
    @include('backend.partials.scripts._select2')
    @include('backend.partials.scripts._tooltips')
    @include('backend.partials.scripts._ckeditor')
    @include('backend.events._venue_script')
    <script>
        $(document).ready(function () {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

        });
    </script>
@stop
