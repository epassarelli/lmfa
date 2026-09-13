<div>
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-1 border-gray-200">Artistas más leídos</h3>
    <ul class="text-sm text-gray-700 space-y-2">
      @foreach ($interpretes as $interprete)
        <li>
          <a href="{{ route('artista.show', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="hover:text-[#ff661f] transition-colors block">
            {{ $interprete->interprete }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</div>
