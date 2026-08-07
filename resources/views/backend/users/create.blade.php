@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
  <h1><i class="fas fa-user-plus mr-2"></i>Crear Usuario</h1>
@stop

@section('content')
  <div class="row">
    <div class="col-md-10 mx-auto">
      <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="card card-outline card-success">
          <div class="card-header">
            <h3 class="card-title">Información General</h3>
          </div>
          <div class="card-body">
            @include('backend.users._form')
          </div>
          <div class="card-footer text-right">
            <a href="{{ route('users.index') }}" class="btn btn-default mr-2">
              Cancelar
            </a>
            <button type="submit" class="btn btn-primary shadow-sm">
              <i class="fas fa-save mr-1"></i> Crear Usuario
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
@stop
