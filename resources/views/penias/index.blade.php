@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto py-8">
    <div class="flex flex-wrap justify-center">
      @foreach ($penias as $penia)
        <div class="w-full sm:w-1/2 md:w-1/3 p-4">
          <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <a href="{{ route('noticias.show', $penia->slug) }}">
              <img src="{{ asset('storage/noticias/' . $penia->foto) }}" alt="{{ $penia->titulo }}"
                class="w-full h-48 object-cover">
              <div class="p-4">
                <h3 class="font-bold text-xl mb-2">{{ $penia->titulo }}</h3>
              </div>
            </a>
          </div>
        </div>
      @endforeach
    </div>

  </div>


@endsection
