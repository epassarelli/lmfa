@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
<main class="container mx-auto py-8">
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <h1 class="mt-5 text-3xl font-bold">{{ $signal->title }}</h1>
  <p class="mt-2 text-slate-600">{{ $signal->provincia?->nombre }}{{ $signal->city ? ' · '.$signal->city : '' }}</p>

  <div class="mt-8 grid gap-8 lg:grid-cols-3">
    <article class="lg:col-span-2">
      <div class="prose max-w-none">{!! $signal->body !!}</div>
      @if($signal->programs->isNotEmpty())
        <section class="mt-8">
          <h2 class="text-2xl font-bold">Programación de folklore</h2>
          @foreach($signal->programs as $program)
            <div class="mt-3 rounded-lg border p-4">
              <h3 class="font-bold">{{ $program->title }}</h3>
              @foreach($program->slots as $slot)
                <p class="text-sm text-slate-600">{{ ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][$slot->weekday] }} {{ substr($slot->starts_at, 0, 5) }}</p>
              @endforeach
            </div>
          @endforeach
        </section>
      @endif
    </article>

    <aside class="rounded-xl bg-stone-100 p-5">
      <h2 class="text-xl font-bold">Escuchar</h2>
      @forelse($signal->listeningChannels as $channel)
        <div class="mt-3">
          <strong>{{ $channel->label }}</strong>
          @if($channel->frequency)
            <p>{{ $channel->frequency_band }} {{ $channel->frequency }}</p>
          @endif
          @if($channel->url)
            <a class="text-amber-800 underline" href="{{ $channel->url }}" target="_blank" rel="noopener noreferrer">Abrir enlace oficial</a>
          @endif
        </div>
      @empty
        <p>No hay canales disponibles.</p>
      @endforelse
    </aside>
  </div>
</main>
@endsection
