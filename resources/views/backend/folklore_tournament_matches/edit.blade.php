@extends('adminlte::page')

@section('title', 'Editar partido')

@section('content_header')
  <x-backend.form-header mode="edit">Editar partido</x-backend.form-header>
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

      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.folklore-tournaments.matches', $match->tournament)" />
      </div>
    </form>
  </div>
@stop
