@extends('adminlte::page')

@section('title', 'Editar Etiqueta')

@section('content_header')
    <x-backend.form-header mode="edit">Editar Etiqueta: {{ $tag->name }}</x-backend.form-header>
@stop

@section('content')
<div class="card">
    <form action="{{ route('backend.tags.update', $tag) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('backend.tags.form')
        </div>
        <div class="card-footer">
            <x-backend.form-actions :back="route('backend.tags.index')" />
        </div>
    </form>
</div>
@stop
