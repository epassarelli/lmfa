@extends('adminlte::page')
@section('title', 'Nuevo programa')
@section('content_header')<h1>Nuevo programa</h1>@stop
@section('content')<div class="card card-primary"><form method="POST" action="{{ route('backend.radios.programs.store') }}">@csrf<div class="card-body">@include('backend.radios.programs.form')</div><div class="card-footer"><button class="btn btn-primary">Guardar programa</button></div></form></div>@stop
