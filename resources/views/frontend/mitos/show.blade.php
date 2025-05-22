@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto py-8">
    <div class="row">
      <div class="col-md-9">
        <h1 class="text-3xl font-bold mb-4">{{ $mito->titulo }}</h1>
        {{-- <img src="{{ asset('storage/mitos/' . $mito->foto) }}" alt="{{ $mito->titulo }}" class="mb-4 rounded-lg shadow-lg"> --}}
        <p class="text-lg mb-4">{!! $mito->mito !!}</p>
        <p class="text-gray-600">Visitas: {{ $mito->visitas }}</p>
      </div>

      <div class="col-md-3">
        <h3>Últimos mitos</h3>

        <ul class="list-group">
          @foreach ($ultimos_mitos as $ultimo_mito)
            <li class="list-group-item">
              <a href="{{ route('mitos.show', $ultimo_mito->slug) }}"
                class="text-decoration-none">{{ $ultimo_mito->titulo }}</a>
            </li>
          @endforeach
        </ul>

      </div>
    </div>
  </div>


@endsection
