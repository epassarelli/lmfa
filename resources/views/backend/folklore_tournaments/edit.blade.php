@extends('adminlte::page')

@section('title', 'Editar torneo')

@section('content_header')
  <x-backend.form-header mode="edit">Editar torneo: {{ $tournament->name }}</x-backend.form-header>
@stop

@section('content')
  <div class="card card-outline card-primary">
    <form action="{{ route('backend.folklore-tournaments.update', $tournament) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="card-body">
        @include('backend.folklore_tournaments.form')
      </div>

      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.folklore-tournaments.show', $tournament)" />
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
