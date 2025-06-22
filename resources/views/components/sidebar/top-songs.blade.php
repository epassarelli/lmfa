<div>
  <!-- The only way to do great work is to love what you do. - Steve Jobs -->
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-1 border-gray-200">Canciones más leídas</h3>
    <ul class="space-y-2 text-sm text-gray-700">
      @foreach ($canciones as $cancion)
        <li>
          <a href="{{ route('canciones.show', $cancion->slug) }}" class="hover:text-[#ff661f] block">
            {{ Str::limit($cancion->titulo, 60) }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>

</div>
