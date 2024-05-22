{{-- <div class="card"> --}}

<img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" class="card-img-top"
  alt="{{ $interprete->interprete }}">

<div class="list-group">

  <a href="{{ route('interprete.show', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="list-group-item list-group-item-action">Biografía</a>

  <a href="{{ route('interprete.noticias', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="list-group-item list-group-item-action">Noticias</a>

  <a href="{{ route('interprete.shows', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="list-group-item list-group-item-action">Shows</a>

  <a href="{{ route('interprete.discografia', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="list-group-item list-group-item-action">Discos</a>

  <a href="{{ route('interprete.canciones', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="list-group-item list-group-item-action">Canciones</a>

  {{-- <a href="{{ route('interprete.noticias', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="list-group-item list-group-item-action">Videos</a>

  <a href="{{ route('interprete.noticias', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="list-group-item list-group-item-action">Entrevistas</a> --}}

</div>

{{-- </div> --}}






{{-- 
    <div class="flex flex-col items-center space-y-4">
  <div class="flex">
    <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
      class="h-auto w-auto">
  </div>
  <h1 class="text-2xl font-semibold">{{ $interprete->interprete }}</h1>
  <div class="flex flex-wrap space-x-4">
    <div class="flex items-center">
      <i class="fas fa-map-marker-alt mr-2"></i>
      <span>{{ $interprete->location }}</span>
    </div>
    <div class="flex items-center">
      <i class="fas fa-phone mr-2"></i>
      <span>{{ $interprete->phone }}</span>
    </div>
    <div class="flex items-center">
      <i class="fas fa-envelope mr-2"></i>
      <span>{{ $interprete->email }}</span>
    </div>
  </div>

  @if ($interprete->facebook || $interprete->twitter || $interprete->instagram)
    <div class="flex space-x-4">
      @if ($interprete->facebook)
        <a href="{{ $interprete->facebook }}" target="_blank" class="text-blue-600 hover:text-blue-800">
          <i class="fab fa-facebook-f"></i>
        </a>
      @endif
      @if ($interprete->twitter)
        <a href="{{ $interprete->twitter }}" target="_blank" class="text-blue-400 hover:text-blue-600">
          <i class="fab fa-twitter"></i>
        </a>
      @endif
      @if ($interprete->instagram)
        <a href="{{ $interprete->instagram }}" target="_blank" class="text-pink-600 hover:text-pink-800">
          <i class="fab fa-instagram"></i>
        </a>
      @endif
    </div>
  @endif
 --}}
