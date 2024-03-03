@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4">

    @foreach ($canciones as $cancion)
      <a href="{{ route('interprete.cancion.show', [$cancion->interprete->slug, $cancion->slug]) }}"
        class="bg-white rounded-lg shadow-md overflow-hidden flex">
        <img class="w-24 h-auto object-cover" src="{{ asset('storage/interpretes/' . $cancion->interprete->foto) }}"
          alt="{{ $cancion->cancion }}">
        <div class="p-4 flex flex-col">
          <h2 class="text-lg font-medium text-gray-800 mb-2 hover:text-blue-600">
            {{ $cancion->cancion }}
          </h2>
          <p class="text-gray-500 text-sm mb-2">
            {{ $cancion->interprete->interprete }}
          </p>
        </div>
      </a>
    @endforeach
  </div>

  <!-- Links del paginado -->
  <div class="flex justify-center">
    <div class="mt-8">
      {{ $canciones->links() }}
    </div>
  </div>


</x-app-layout>
