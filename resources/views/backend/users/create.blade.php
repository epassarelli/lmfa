@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
  <x-backend.form-header mode="create">Crear Usuario</x-backend.form-header>
@stop

@section('content')
  <div class="row">
    <div class="col-12">
      <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="card card-outline card-success">
          <div class="card-header">
            <h3 class="card-title">Información General</h3>
          </div>
          <div class="card-body">
            @include('backend.users._form')
          </div>
          <div class="card-footer">
            <x-backend.form-actions :back="route('users.index')" />
          </div>
        </div>
      </form>
    </div>
  </div>
@stop
