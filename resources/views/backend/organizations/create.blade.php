@extends('adminlte::page')

@section('metaTitle', 'Nueva organización')

@section('content_header')
  <x-backend.form-header mode="create">Nueva organización</x-backend.form-header>
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
        <x-backend.form-actions :back="route('backend.organizations.index')" />
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._select2')
@stop
