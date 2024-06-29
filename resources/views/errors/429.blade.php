@extends('layouts.app')

@section('title', 'Demasiadas solicitudes')

@section('content')
  <div class="container text-center">
    <h1 class="display-1">429</h1>
    <p class="lead">Estás enviando demasiadas solicitudes en un corto período de tiempo. Por favor, intenta más tarde.
    </p>
    <a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a>
  </div>
@endsection
