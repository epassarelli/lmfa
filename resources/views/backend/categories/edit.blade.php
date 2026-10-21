@extends('adminlte::page')

@section('title', 'Editar Categoría')

@section('content_header')
    <x-backend.form-header mode="edit">Editar Categoría: {{ $category->name }}</x-backend.form-header>
@stop

@section('content')
<div class="card">
    <form action="{{ route('backend.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('backend.categories.form')
        </div>
        <div class="card-footer">
            <x-backend.form-actions :back="route('backend.categories.index')" />
        </div>
    </form>
</div>
@stop
