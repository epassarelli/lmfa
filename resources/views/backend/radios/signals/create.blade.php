@extends('adminlte::page')
@section('title', 'Nueva señal')
@section('content_header')<h1>Nueva señal</h1>@stop
@section('content')<div class="card card-primary"><form method="POST" action="{{ route('backend.radios.signals.store') }}">@csrf<div class="card-body">@include('backend.radios.signals.form')</div><div class="card-footer"><button class="btn btn-primary">Guardar señal</button></div></form></div>@stop
