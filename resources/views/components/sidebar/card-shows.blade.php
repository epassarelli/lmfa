<div>
  <!-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci -->
  <section class="bg-white p-2 rounded-2xl shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Próximos shows
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($shows as $show)
        <article
          class="flex gap-4 items-start bg-white border border-gray-100 p-2 rounded-lg shadow-sm hover:shadow transition">
          <a href="{{ route('shows.show', $show->slug) }}" class="shrink-0">
            @if ($show->images->isNotEmpty())
              <x-optimized-image :image="$show->images->first()" variant="card" :minimal="true"
                class="w-16 h-16 object-cover rounded-md" :alt="$show->interprete->interprete ?? 'Artista'" />
            @elseif ($show->interprete && $show->interprete->images->isNotEmpty())
              <x-optimized-image :image="$show->interprete->images->first()" variant="card" :minimal="true"
                class="w-16 h-16 object-cover rounded-md" :alt="$show->interprete->interprete" />
            @elseif ($show->interprete && $show->interprete->foto && file_exists(public_path('storage/interpretes/' . $show->interprete->foto)))
              <img src="{{ asset('storage/interpretes/' . $show->interprete->foto) }}"
                alt="{{ $show->interprete->interprete ?? 'Artista' }}" class="w-16 h-16 object-cover rounded-md">
            @else
              <x-image-placeholder :label="null" class="w-16 h-16 rounded-md" />
            @endif
          </a>
          <div class="flex-1">
            <h4 class="font-semibold leading-tight">
              <a href="{{ route('shows.show', $show->slug) }}" class="hover:text-[#ff661f] transition">
                {{ Str::limit($show->titulo, 60) }}
              </a>
            </h4>
            <span class="text-xs text-gray-500">{{ $show->fecha->format('d/m/Y') }}</span>
          </div>
        </article>
      @endforeach
    </div>
  </section>

</div>
