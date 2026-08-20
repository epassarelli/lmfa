@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', 'noindex,follow')

@section('content')
  <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 md:p-8">
    <header class="mb-8 border-b border-gray-200 pb-6">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#ff661f]">Estado de solicitud</p>
      <h1 class="mt-3 text-3xl font-bold text-gray-900">Estado de eliminacion de datos</h1>
      <p class="mt-3 text-base leading-7 text-gray-600">
        Esta pagina muestra informacion minima de seguimiento para una solicitud recibida por Mi Folklore Argentino.
      </p>
    </header>

    <dl class="grid gap-4 md:grid-cols-2">
      <div class="rounded-2xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <dt class="text-sm font-semibold uppercase tracking-wide text-gray-500">Codigo de confirmacion</dt>
        <dd class="mt-2 break-all text-base font-semibold text-gray-900">{{ $deletionRequest->confirmation_code }}</dd>
      </div>
      <div class="rounded-2xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <dt class="text-sm font-semibold uppercase tracking-wide text-gray-500">Estado</dt>
        <dd class="mt-2 text-base font-semibold text-gray-900">{{ $statusLabel }}</dd>
      </div>
      <div class="rounded-2xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <dt class="text-sm font-semibold uppercase tracking-wide text-gray-500">Fecha de solicitud</dt>
        <dd class="mt-2 text-base text-gray-900">{{ optional($deletionRequest->requested_at)->format('d/m/Y H:i') ?? 'No disponible' }}</dd>
      </div>
      <div class="rounded-2xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <dt class="text-sm font-semibold uppercase tracking-wide text-gray-500">Fecha de finalizacion</dt>
        <dd class="mt-2 text-base text-gray-900">{{ optional($deletionRequest->completed_at)->format('d/m/Y H:i') ?? 'Aun no finalizada' }}</dd>
      </div>
    </dl>

    <div class="mt-6 rounded-2xl border border-orange-200 bg-orange-50 p-5 text-sm leading-7 text-gray-700">
      {{ $statusMessage }}
    </div>
  </article>
@endsection
