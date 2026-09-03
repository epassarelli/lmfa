@extends('adminlte::page')
@section('title', 'Editar programa')
@section('content_header')<h1>Editar programa</h1>@stop
@section('content')<div class="card card-primary"><form method="POST" action="{{ route('backend.radios.programs.update',$program) }}">@csrf @method('PUT')<div class="card-body">@include('backend.radios.programs.form')</div><div class="card-footer"><button class="btn btn-primary">Guardar cambios</button></div></form></div>@stop
