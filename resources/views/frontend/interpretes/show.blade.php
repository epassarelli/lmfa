@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    {{-- Contenido principal --}}
    <h1 class="text-2xl font-semibold mb-6">Biografía de {{ $interprete->interprete }}</h1>

    <div class="prose max-w-none prose-lg prose-slate">
      {!! $interprete->biografia !!}
    </div>


  </section>

  {{-- Muestro las redes p/ compartir --}}
  <div class="redes">
    <x-compartir-redes :titulo="$interprete->interprete" :url="Request::url()" />
  </div>
  {{-- Selector de intérprete --}}
  {{-- @include('layouts.partials.select-interprete') --}}



@endsection

@section('sidebar')
  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  <br>
  <x-sidebar.social-links />

@endsection
