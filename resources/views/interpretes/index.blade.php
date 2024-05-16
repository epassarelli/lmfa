@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <!-- Listado de interpretes en cards -->
  <div class="flex flex-wrap justify-center">

    @foreach ($interpretes as $interprete)
      <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 p-4 mb-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <a href="{{ route('interprete.show', $interprete->slug) }}">
            <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
              class="w-full h-64 object-cover">
            <div class="p-4">
              <h3 class="font-bold text-lg mb-2">{{ $interprete->interprete }}</h3>
            </div>
          </a>
        </div>
      </div>
    @endforeach

    <!-- Links del paginado -->
    <div class="flex justify-center p-4">
      {{ $interpretes->links() }}
    </div>

  </div>

@endsection
