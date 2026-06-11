<section style="min-height: 480px;" class="relative mb-4 min-h-[520px] overflow-hidden rounded-lg bg-stone-900 shadow-sm">
  <img src="{{ asset('images/copa-folklore-2026/sidebar-banner-vertical.png') }}" alt="Copa del Folklore Argentino 2026" class="absolute inset-0 h-full w-full object-cover object-top">
  <div class="absolute inset-0 bg-gradient-to-b from-stone-950/90 via-stone-950/75 to-stone-900/40"></div>
  <div class="relative flex min-h-full items-end px-4 pb-6 pt-36 text-white">
    <div>
    <p class="text-xs font-semibold uppercase tracking-wide text-amber-300">Mi Folklore Argentino</p>
    <h2 class="mt-2 text-lg font-bold leading-tight">Copa del Folklore Argentino 2026</h2>
    <p class="mt-2 text-sm text-stone-200">
      Segui participantes, zonas y el avance completo del torneo en el portal.
    </p>
    <div class="mt-4 flex flex-col gap-2">
      <a href="{{ route('folklore.cup.index') }}"
        class="inline-flex items-center justify-center rounded-md bg-amber-500 px-3 py-2 text-sm font-semibold text-stone-950 transition hover:bg-amber-400">
        Ir a la copa
      </a>
      <a href="{{ route('folklore.cup.groups') }}"
        class="inline-flex items-center justify-center rounded-md border border-stone-300 px-3 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
        Ver zonas
      </a>
    </div>
    </div>
  </div>
</section>
