@extends('adminlte::page')
@section('title', 'Editar señal')
@section('content_header')
  <x-backend.form-header mode="edit">Editar señal: {{ $signal->title }}</x-backend.form-header>
@stop
@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
<div class="row"><div class="col-12"><div class="card card-primary">
  <form method="POST" action="{{ route('backend.radios.signals.update', $signal) }}" class="form-compact">
    @csrf @method('PUT')
    <div class="card-body">@include('backend.radios.signals.form')</div>
    <div class="card-footer"><x-backend.form-actions :back="$canManageEditorialState && $signal->editorial_status === 'draft' ? route('backend.moderation.index') : route('backend.radios.signals.index')" /></div>
  </form>
</div></div></div>
@stop

@section('js')
  @include('backend.partials.scripts._tooltips')
@stop
