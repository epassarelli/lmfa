@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  @include('frontend.folklore_tournaments.partials.hero_nav')

  <article class="mt-8 rounded-lg bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-semibold text-stone-900">Reglamento</h1>
    <div class="prose mt-6 max-w-none prose-stone">
      <p>La Copa del Folklore Argentino 2026 es una propuesta participativa de Mi Folklore Argentino orientada a la comunidad del folklore argentino.</p>

      <h2>Formato general</h2>
      <ul>
        <li>32 interpretes</li>
        <li>8 zonas de 4 participantes</li>
        <li>todos contra todos en fase de grupos</li>
        <li>clasifican 2 por zona</li>
        <li>octavos, cuartos, semifinal y final</li>
      </ul>

      <h2>Sistema de puntaje</h2>
      <ul>
        <li>ganado: 3 puntos</li>
        <li>empatado: 1 punto</li>
        <li>perdido: 0 puntos</li>
      </ul>

      <h2>Criterios de desempate</h2>
      <ol>
        <li>puntos</li>
        <li>diferencia de votos</li>
        <li>votos a favor</li>
        <li>votos en contra</li>
        <li>resultado entre si</li>
        <li>orden manual si fuera necesario</li>
      </ol>

      <h2>Votacion y resultados</h2>
      <p>La votacion se realiza en publicaciones oficiales de Instagram. Los resultados reflejados en el sitio son cargados manualmente por la administracion del portal.</p>

      <blockquote>
        “La Copa del Folklore Argentino 2026 es una iniciativa editorial y recreativa de Mi Folklore Argentino. La votacion se realiza en publicaciones oficiales de Instagram durante el periodo indicado para cada cruce. Los resultados publicados en el sitio corresponden al conteo registrado por la administracion del portal. Instagram no patrocina, administra ni esta asociado a esta iniciativa.”
      </blockquote>

      @if($tournament->rules)
        <h2>Notas del torneo</h2>
        <p>{{ $tournament->rules }}</p>
      @endif
    </div>
  </article>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
