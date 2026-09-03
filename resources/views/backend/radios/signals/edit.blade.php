@extends('adminlte::page')
@section('title', 'Editar señal')
@section('content_header')<h1>Editar señal</h1>@stop
@section('content')<div class="card card-primary"><form method="POST" action="{{ route('backend.radios.signals.update',$signal) }}">@csrf @method('PUT')<div class="card-body">@include('backend.radios.signals.form')</div><div class="card-footer"><button class="btn btn-primary">Guardar cambios</button></div></form></div>@stop
