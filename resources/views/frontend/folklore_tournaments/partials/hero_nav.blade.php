@php
  $cupLinks = [
    ['label' => 'Home', 'route' => route('folklore.cup.index')],
    ['label' => 'Participantes', 'route' => route('folklore.cup.participants')],
    ['label' => 'Fixture', 'route' => route('folklore.cup.fixture')],
    ['label' => 'Zonas', 'route' => route('folklore.cup.groups')],
    ['label' => 'Reglamento', 'route' => route('folklore.cup.rules')],
  ];
@endphp

<section class="relative overflow-hidden rounded-lg bg-stone-900 shadow-sm">
  <img src="{{ asset('images/copa-folklore-2026/hero-banner.png') }}" alt="Copa del Folklore Argentino 2026" class="absolute inset-0 h-full w-full object-cover">
  <div class="absolute inset-0 bg-gradient-to-r from-stone-950/90 via-stone-950/70 to-stone-900/20"></div>
  <div class="relative px-6 py-10 text-white md:px-10 md:py-14">
    <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-300">Mi Folklore Argentino</p>
    <h1 class="max-w-3xl text-3xl font-bold leading-tight md:text-5xl">Copa del Folklore Argentino 2026</h1>
    <p class="mt-4 max-w-3xl text-base text-stone-200 md:text-lg">
      Un torneo editorial y participativo entre 32 interpretes del folklore argentino, con votacion abierta en Instagram y seguimiento completo en el portal.
    </p>

    <div class="mt-6 flex flex-wrap gap-3">
      <a href="https://instagram.com/" target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-stone-950 transition hover:bg-amber-400">
        Ir a Instagram
      </a>
      <a href="{{ route('folklore.cup.fixture') }}"
        class="inline-flex items-center rounded-md border border-stone-300 px-4 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
        Ver fixture
      </a>
    </div>
  </div>
</section>

<nav class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-5" aria-label="Navegacion de la Copa del Folklore">
  @foreach ($cupLinks as $link)
    @php
      $isActive = url()->current() === $link['route'];
    @endphp
    <a
      href="{{ $link['route'] }}"
      class="flex min-h-[52px] items-center justify-center rounded-xl border-2 px-3 py-3 text-center text-sm font-semibold transition lg:w-full {{ $isActive ? 'border-amber-500 bg-amber-500 text-stone-950 shadow-md' : 'border-stone-300 bg-white text-stone-900 shadow-md hover:border-amber-400 hover:bg-amber-50 hover:text-stone-950' }}"
      aria-current="{{ $isActive ? 'page' : 'false' }}"
    >
      {{ $link['label'] }}
    </a>
  @endforeach
</nav>
