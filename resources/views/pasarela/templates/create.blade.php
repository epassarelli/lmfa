@extends('adminlte::page')

@section('title', 'Nuevo Template')

@section('content_header')
    <h1>Nuevo Template de Publicación</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('pasarela.templates.store') }}">
            @csrf
            @include('pasarela.templates._form', ['template' => null])

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Guardar template</button>
                <a href="{{ route('pasarela.templates.index') }}" class="btn btn-link">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
