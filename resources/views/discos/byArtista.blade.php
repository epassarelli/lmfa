@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>

  <div class="w-full px-4">
    @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4">
    @foreach ($discos as $disco)
      <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
        class="bg-white rounded-lg shadow-md overflow-hidden flex">
        <img class="w-24 h-auto object-cover" src="{{ asset('storage/albunes/' . $disco->foto) }}"
          alt="{{ $disco->album }}">
        <div class="p-4 flex flex-col">
          <h2 class="text-lg font-medium text-gray-800 mb-2 hover:text-blue-600">
            {{ $disco->album }}
          </h2>
          <p class="text-gray-500 text-sm mb-2">
            {{ $disco->interprete->interprete }}
          </p>
        </div>
      </a>
    @endforeach
  </div>

  <!-- Links del paginado -->
  <div class="flex justify-center  p-4">
    <div class="mt-8">
      {{ $discos->links() }}
    </div>
  </div>


</x-app-layout>
