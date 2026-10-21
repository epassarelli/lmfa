@extends('adminlte::page')

@section('title', 'Editar Peña')

@section('content_header')
  <x-backend.form-header mode="edit">Editar Peña: {{ $profile->title }}</x-backend.form-header>
@stop

@section('css')
  @include('backend.partials.styles._compact-form')
@stop

@section('content')
  <form method="POST" action="{{ route('backend.penia-profiles.update', $profile) }}" class="form-compact">
    @csrf
    @method('PUT')
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-body">@include('backend.penia-profiles.form')</div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.penia-profiles.index')" />
      </div>
    </div>
  </form>
@stop

@section('js')
  @include('backend.partials.scripts._ckeditor')
  @include('backend.partials.scripts._slug')
  @include('backend.partials.scripts._select2')
  @include('backend.partials.scripts._tooltips')
@stop
