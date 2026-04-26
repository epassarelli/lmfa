@extends('adminlte::page')

@section('title', 'Editar Etiqueta')

@section('content_header')
    <h1>Editar Etiqueta: {{ $tag->name }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('backend.tags.update', $tag) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.tags.form')

            <button type="submit" class="btn btn-primary mt-3">Actualizar Cambios</button>
            <a href="{{ route('backend.tags.index') }}" class="btn btn-default mt-3">Regresar</a>
        </form>
    </div>
</div>
@stop
