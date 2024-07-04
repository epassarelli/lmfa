@extends('layouts.app')

@section('title', 'Sesión expirada')

@section('content')
  <div class="container text-center">
    <h1 class="display-1">419</h1>
    <p class="lead">Tu sesión ha expirado. Por favor, refresca la página e intenta nuevamente.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a>
  </div>
@endsection
