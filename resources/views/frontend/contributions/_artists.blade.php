<div class="artist-picker border rounded p-3" data-prefix="{{ $prefix }}">
  <label class="block font-medium">Artistas de esta jornada</label>
  <div class="flex gap-2 mt-2"><input type="text" list="event-artist-options" class="artist-search w-full border rounded p-2" aria-label="Buscar artista" placeholder="Escribí el nombre del artista"><button type="button" class="add-artist border rounded px-3 text-orange-700">Agregar</button></div>
  <div class="artist-chips flex flex-wrap gap-2 mt-2">
    @foreach($interpretes->whereIn('id', $selectedArtistIds) as $artist)<span class="artist-chip border rounded px-2 py-1" data-id="{{ $artist->id }}">{{ $artist->interprete }} <button type="button" class="remove-artist text-red-700" aria-label="Quitar {{ $artist->interprete }}">×</button><input type="hidden" name="{{ $prefix }}[]" value="{{ $artist->id }}"></span>@endforeach
  </div>
  <input type="hidden" name="{{ $prefix }}[]" value="">
  <p class="artist-error text-sm text-red-700" role="status"></p>
</div>
