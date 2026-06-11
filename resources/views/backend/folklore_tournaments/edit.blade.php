@extends('adminlte::page')

@section('title', 'Editar torneo')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-edit mr-2"></i>Editar {{ $tournament->name }}</h1>
    <a href="{{ route('backend.folklore-tournaments.show', $tournament) }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
  </div>
@stop

@section('content')
  <div class="card card-outline card-primary">
    <form action="{{ route('backend.folklore-tournaments.update', $tournament) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="card-body">
        @include('backend.folklore_tournaments.form')
      </div>

      <div class="card-footer text-right">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save mr-1"></i> Guardar torneo
        </button>
      </div>
    </form>
  </div>
@stop

@section('js')
  @include('backend.partials.scripts._select2')
  <script>
    $(document).ready(function () {
      $('.folklore-interprete-select').select2({
        theme: 'classic',
        width: '100%'
      });
    });
  </script>
@stop
