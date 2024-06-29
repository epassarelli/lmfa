@extends('layouts.app')

@section('title', 'Error interno del servidor')

@section('content')
<div class="container text-center">
    <h1 class="display-1">500</h1>
    <p class="lead">Oops, algo salió mal en nuestro servidor.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a>
</div>
@endsection
