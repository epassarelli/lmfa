@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

      <div class="col-md-9">
        <h1>Shows de {{ $interprete->interprete }}</h1>

        <div class="row">
          @if ($shows->isEmpty())
            <div class="warning"></div>
            <div class="alert alert-warning" role="alert">
              No hay shows disponibles para {{ $interprete->interprete }} aún.
            </div>
          @else
            @foreach ($shows as $evento)
              <div class="col-md-4 mb-4">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden position-relative">
                  <img class="w-100" src="{{ asset('storage/interpretes/' . $evento->interprete->foto) }}"
                    alt="{{ $evento->interprete->interprete }}">
                  <div class="position-absolute bottom-0 start-0 w-100 h-25 bg-gradient bg-gradient-opacity">
                  </div>
                  <div class="position-absolute bottom-0 start-0 p-4">
                    <h2 class="text-white fs-4 fw-bold">{{ $evento->titulo }}</h2>
                    <p class="text-gray-300">{{ $evento->interprete->interprete }}</p>
                  </div>
                  <div class="p-4">
                    <p class="text-gray-600 fs-6">{{ \Carbon\Carbon::parse($evento->fecha)->format('Y-m-d') }}</p>
                    <p class="text-gray-800 fs-5 fw-bold mb-2">{{ $evento->titulo }}</p>
                    <p class="text-gray-700">{{ $evento->interprete->interprete }}</p>
                    <p class="text-gray-600">{{ $evento->lugar }}, {{ $evento->direccion }}</p>
                  </div>
                </div>
              </div>
            @endforeach
          @endif
        </div>
      </div>

    </div>

  @endsection
