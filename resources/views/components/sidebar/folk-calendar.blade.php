<div>
  <!-- Act only according to that maxim whereby you can, at the same time, will that it should become a universal law. - Immanuel Kant -->
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-1 border-gray-200">Calendario Folklórico</h3>
    <ul class="text-sm text-gray-700 space-y-2">
      @foreach ($eventos as $evento)
        <li>
          <a href="{{ route('shows.show', $evento->slug) }}" class="hover:text-[#ff661f]">
            {{ $evento->fecha->format('d/m') }} · {{ Str::limit($evento->titulo, 50) }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>

</div>
