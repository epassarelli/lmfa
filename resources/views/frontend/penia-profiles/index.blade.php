@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Peñas folklóricas de Argentina</h1>
    <p class="text-base text-gray-700">
      Espacios culturales con información editorial verificada para planificar una salida y descubrir agenda folklórica. Ya reunimos <strong>{{ $penias->total() }}</strong> peñas en el portal.
    </p>
  </section>
  <section class="bg-white p-4 rounded shadow-sm mb-6">
    @include('frontend.penia-profiles._filters', ['filterFormClass' => 'grid gap-3 md:grid-cols-4'])
  </section>
  <section class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @forelse($penias as $penia)
      <a href="{{ $penia->getUrl() }}" class="rounded-xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><h2 class="text-xl font-semibold text-slate-900">{{ $penia->title }}</h2><p class="mt-2 text-sm text-slate-600">{{ $penia->provincia?->nombre }}@if($penia->city) · {{ $penia->city }}@endif</p><p class="mt-3 text-sm text-orange-700">Verificada {{ $penia->last_verified_at?->format('d/m/Y') }}</p></a>
    @empty
      <p class="md:col-span-3 rounded-xl bg-white p-6 text-slate-600">No hay Peñas verificadas para estos filtros.</p>
    @endforelse
  </section>
  <x-public-pagination :paginator="$penias" />
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.donate />
@endsection
