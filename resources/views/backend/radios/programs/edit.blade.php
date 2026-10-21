@extends('adminlte::page')
@section('title', 'Editar programa')
@section('content_header')
  <x-backend.form-header mode="edit">Editar programa</x-backend.form-header>
@stop
@section('content')
<div class="card card-primary">
  <form method="POST" action="{{ route('backend.radios.programs.update', $program) }}">
    @csrf @method('PUT')
    <div class="card-body">@include('backend.radios.programs.form')</div>
    <div class="card-footer"><x-backend.form-actions :back="route('backend.radios.programs.index')" /></div>
  </form>
</div>
@stop
