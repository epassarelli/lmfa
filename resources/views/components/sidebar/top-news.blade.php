<div>
  <!-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead -->
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-1 border-gray-200">Noticias más leídas</h3>
    <ul class="text-sm text-gray-700 space-y-2">
      @foreach ($noticias as $noticia)
        <li>
          <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}"
            class="hover:text-[#ff661f] transition-colors block">
            {{ Str::limit($noticia->titulo, 60) }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>

</div>
