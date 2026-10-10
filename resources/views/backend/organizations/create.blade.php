@extends('adminlte::page')

@section('metaTitle', 'Nueva organización')

@section('content_header')
  <h1>Nueva organización</h1>
@stop

@section('content')
  <form action="{{ route('backend.organizations.store') }}" method="POST">
    @csrf
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-body">
        @include('backend.organizations._form')
        <p class="text-muted mb-0">Después de guardar vas a poder vincular fichas y agregar miembros.</p>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
        <a href="{{ route('backend.organizations.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._select2')
@stop
