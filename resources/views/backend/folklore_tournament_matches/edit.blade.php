@extends('adminlte::page')

@section('title', 'Editar partido')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-edit mr-2"></i>Editar partido</h1>
    <a href="{{ route('backend.folklore-tournaments.matches', $match->tournament) }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Volver a partidos
    </a>
  </div>
@stop

@section('content')
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">{{ $match->participant1?->display_name }} vs {{ $match->participant2?->display_name }}</h3>
    </div>
    <form action="{{ route('backend.folklore-tournament-matches.update', $match) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="card-body">
        @include('backend.folklore_tournament_matches.form')
      </div>

      <div class="card-footer text-right">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save mr-1"></i> Guardar cambios
        </button>
      </div>
    </form>
  </div>
@stop
