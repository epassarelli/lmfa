@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

      {{-- Contenido principal --}}
      <div class="w-full lg:w-3/4">
        <h1 class="text-3xl font-bold mb-6">Biografía de {{ $interprete->interprete }}</h1>

        <div class="prose max-w-none prose-lg prose-slate">
          {!! $interprete->biografia !!}
        </div>

        {{-- Selector de intérprete --}}
        @include('layouts.partials.select-interprete')
      </div>

      {{-- Sidebar --}}
      <div class="w-full lg:w-1/4">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>
  </div>

@endsection
