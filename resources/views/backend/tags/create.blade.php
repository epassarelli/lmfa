{{-- resources/views/admin/tags/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Nueva Etiqueta')

@section('content_header')
    <x-backend.form-header mode="create">Crear Etiqueta</x-backend.form-header>
@stop

@section('content')
<div class="card">
    <form action="{{ route('backend.tags.store') }}" method="POST">
        <div class="card-body">
            @include('backend.tags.form')
        </div>
        <div class="card-footer">
            <x-backend.form-actions :back="route('backend.tags.index')" />
        </div>
    </form>
</div>
@stop
