@extends('adminlte::page')
@section('title', 'Nueva señal')
@section('content_header')
  <x-backend.form-header mode="create">Nueva señal</x-backend.form-header>
@stop
@section('content')
<div class="card card-primary">
  <form method="POST" action="{{ route('backend.radios.signals.store') }}">
    @csrf 
    <div class="card-body">@include('backend.radios.signals.form')</div>
    <div class="card-footer"><x-backend.form-actions :back="route('backend.radios.signals.index')" /></div>
  </form>
</div>
@stop
