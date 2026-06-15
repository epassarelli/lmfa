@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
    @foreach ($interpretes as $interprete)
      <x-biografia-card :interprete="$interprete" />
    @endforeach
  </div>

  <div class="my-6">
    {{ $interpretes->links() }}
  </div>

  <section class="bg-white p-2 rounded shadow-sm mt-4 mb-4">
    <x-alpha-filter
      title="Buscar por Orden Alfabético"
      description="Encuentra fácilmente a tu intérprete favorito de folklore argentino utilizando nuestro índice alfabético."
      route-name="interpretes.letra"
      :letters="$alphabet"
    />
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.donate />
  <x-sidebar.social-links />
@endsection
