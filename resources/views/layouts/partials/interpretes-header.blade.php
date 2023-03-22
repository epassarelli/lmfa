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
    <div class="flex space-x-4">
        <a href="{{ $interprete->facebook }}" target="_blank" class="text-blue-600 hover:text-blue-800">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="{{ $interprete->twitter }}" target="_blank" class="text-blue-400 hover:text-blue-600">
            <i class="fab fa-twitter"></i>
        </a>
        <a href="{{ $interprete->instagram }}" target="_blank" class="text-pink-600 hover:text-pink-800">
            <i class="fab fa-instagram"></i>
        </a>
    </div>
    <div class="flex flex-wrap space-x-4">
        <a href="{{ route('interprete.show', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="text-blue-600 hover:text-blue-800">Biografía</a>
        <a href="{{ route('interprete.shows', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="text-blue-600 hover:text-blue-800">Shows</a>
        <a href="{{ route('interprete.discografia', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="text-blue-600 hover:text-blue-800">Discografía</a>
        <a href="{{ route('interprete.noticias', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="text-blue-600 hover:text-blue-800">Noticias</a>
        <a href="{{ route('interprete.canciones', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="text-blue-600 hover:text-blue-800">Letras</a>
        {{-- <a href="{{ route('interprete.videos', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="text-blue-600 hover:text-blue-800">Videos</a> --}}
        {{-- <a href="{{ route('interprete.entrevistas', str_replace('biografia-de-', '', $interprete->slug)) }}"
            class="text-blue-600 hover:text-blue-800">Entrevistas</a> --}}
    </div>
</div>
