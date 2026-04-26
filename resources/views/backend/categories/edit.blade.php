@extends('adminlte::page')

@section('title', 'Editar Categoría')

@section('content_header')
    <h1>Editar Categoría: {{ $category->name }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('backend.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            @include('backend.categories.form')

            <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
            <a href="{{ route('backend.categories.index') }}" class="btn btn-default mt-3">Cancelar</a>
        </form>
    </div>
</div>
@stop
