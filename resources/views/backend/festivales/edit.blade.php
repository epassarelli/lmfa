@extends('adminlte::page')

@section('title', 'Editar Festival')

@section('content_header')
  <h1>Editar Festival</h1>
@stop

@section('content')
  <form action="{{ route('backend.festivales.update', $festival) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
      <div class="card-body">
        @include('backend.festivales.form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('backend.festivales.index') }}" class="btn btn-default">Cancelar</a>
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._ckeditor')
  @include('backend.partials.scripts._slug')
  @include('backend.partials.scripts._select2')
@stop
