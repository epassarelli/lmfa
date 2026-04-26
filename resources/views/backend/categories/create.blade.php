@extends('adminlte::page')

@section('title', 'Nueva Categoría')

@section('content_header')
    <h1>Crear Categoría</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('backend.categories.store') }}" method="POST">
            @include('backend.categories.form')
            <button type="submit" class="btn btn-primary mt-3">Guardar</button>
            <a href="{{ route('backend.categories.index') }}" class="btn btn-default mt-3">Cancelar</a>
        </form>
    </div>
</div>
@stop
