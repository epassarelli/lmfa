<div>
  <!-- No surplus words or unnecessary actions. - Marcus Aurelius -->
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-1 border-gray-200">Artista del Mes</h3>
    <a href="{{ route('biografias.show', $artista->slug) }}" class="block group">
      <img src="{{ asset($artista->foto) }}" alt="{{ $artista->nombre }}"
        class="w-full rounded-lg mb-2 group-hover:opacity-90 transition">
      <div class="font-semibold text-[#ff661f] group-hover:underline">{{ $artista->nombre }}</div>
      <p class="text-sm text-gray-600 mt-1">{{ Str::limit($artista->bajada, 80) }}</p>
    </a>
  </div>

</div>
