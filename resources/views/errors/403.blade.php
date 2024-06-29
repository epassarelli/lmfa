@extends('layouts.app')

@section('title', 'Acceso prohibido')

@section('content')
  <div class="container text-center">
    <h1 class="display-1">403</h1>
    <p class="lead">No tienes permiso para acceder a esta página.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a>
  </div>
@endsection
