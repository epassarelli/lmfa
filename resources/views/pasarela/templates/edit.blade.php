@extends('adminlte::page')

@section('title', 'Editar Template')

@section('content_header')
    <h1>Editar Template — <code>{{ $template->provider }} / {{ $template->variant_name }}</code></h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('pasarela.templates.update', $template) }}">
            @csrf @method('PUT')
            @include('pasarela.templates._form', ['template' => $template])

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('pasarela.templates.index') }}" class="btn btn-link">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
