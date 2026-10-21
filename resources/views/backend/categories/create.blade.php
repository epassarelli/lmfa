@extends('adminlte::page')

@section('title', 'Nueva Categoría')

@section('content_header')
    <x-backend.form-header mode="create">Crear Categoría</x-backend.form-header>
@stop

@section('content')
<div class="card">
    <form action="{{ route('backend.categories.store') }}" method="POST">
        <div class="card-body">
            @include('backend.categories.form')
        </div>
        <div class="card-footer">
            <x-backend.form-actions :back="route('backend.categories.index')" />
        </div>
    </form>
</div>
@stop
