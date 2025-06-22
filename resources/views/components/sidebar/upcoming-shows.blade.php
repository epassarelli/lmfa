<div>
  <!-- Very little is needed to make a happy life. - Marcus Aurelius -->
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-1 border-gray-200">Próximos Shows</h3>
    <ul class="space-y-3 text-sm text-gray-700">
      @foreach ($eventos as $evento)
        <li>
          <a href="{{ route('shows.show', $evento->slug) }}" class="block hover:text-[#ff661f]">
            <div class="font-semibold">{{ $evento->titulo }}</div>
            <div class="text-gray-500 text-xs">{{ $evento->fecha->format('d/m') }} · {{ $evento->provincia->nombre }}
            </div>
          </a>
        </li>
      @endforeach
    </ul>
  </div>

</div>
