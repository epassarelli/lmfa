@extends('adminlte::page')
@section('title', 'Nuevo programa')
@section('content_header')
  <x-backend.form-header mode="create">Nuevo programa</x-backend.form-header>
@stop
@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
<div class="card card-primary">
  <form method="POST" action="{{ route('backend.radios.programs.store') }}" class="form-compact">
    @csrf 
    <div class="card-body">@include('backend.radios.programs.form')</div>
    <div class="card-footer"><x-backend.form-actions :back="route('backend.radios.programs.index')" /></div>
  </form>
</div>
@stop

@section('js')
  @include('backend.partials.scripts._tooltips')
@stop
