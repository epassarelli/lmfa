{{-- resources/views/admin/tags/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Nueva Etiqueta')

@section('content_header')
    <h1>Crear Etiqueta</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('backend.tags.store') }}" method="POST">
            @include('backend.tags.form')

            <button type="submit" class="btn btn-primary mt-3">Guardar Etiqueta</button>
            <a href="{{ route('backend.tags.index') }}" class="btn btn-default mt-3">Cancelar</a>
        </form>
    </div>
</div>
@stop
