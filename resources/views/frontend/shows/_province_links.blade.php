{{-- "Explorar por provincia": solo provincias con eventos públicos futuros, con su cantidad. --}}
@if (! empty($provinceLinks))
  <section class="{{ $wrapperClass ?? 'mb-6' }} bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Explorar por provincia</h2>
    <ul class="flex flex-wrap gap-3">
      @foreach ($provinceLinks as $provinceLink)
        <li>
          <a href="{{ $provinceLink['url'] }}" class="inline-flex items-center gap-2 rounded-full border border-orange-200 px-4 py-2 text-sm font-medium text-orange-700 hover:border-orange-300 hover:text-orange-800 transition">
            {{ $provinceLink['nombre'] }}
            <span class="rounded-full bg-orange-50 px-2 text-xs text-orange-800">{{ $provinceLink['count'] }}<span class="sr-only"> {{ $provinceLink['count'] === 1 ? 'evento' : 'eventos' }}</span></span>
          </a>
        </li>
      @endforeach
    </ul>
  </section>
@endif
