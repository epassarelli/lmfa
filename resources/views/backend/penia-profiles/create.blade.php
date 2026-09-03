@extends('adminlte::page')

@section('title', 'Crear Peña')

@section('content_header')
  <h1>Crear Peña</h1>
@stop

@section('content')
  <form method="POST" action="{{ route('backend.penia-profiles.store') }}">
    @csrf
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-body">@include('backend.penia-profiles.form')</div>
      <div class="card-footer">
        <button class="btn btn-primary">Guardar</button>
        <a class="btn btn-default" href="{{ route('backend.penia-profiles.index') }}">Cancelar</a>
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._ckeditor')
  @include('backend.partials.scripts._slug')
  @include('backend.partials.scripts._select2')
@stop
