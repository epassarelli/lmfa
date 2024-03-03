@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>

  <div class="flex flex-wrap py-4">

    <div class="w-full md:w-3/4 px-4">
      <h2 class="text-3xl font-bold mb-4">{{ $show->titulo }}</h2>
      <img src="{{ asset('storage/mitos/' . $show->foto) }}" alt="{{ $show->titulo }}" class="mb-4 rounded-lg shadow-lg">
      <p class="text-lg mb-4">{!! $show->receta !!}</p>
      <p class="text-gray-600">Visitas: {{ $show->visitas }}</p>
    </div>


    <div class="w-full md:w-1/4 px-4">
      <h3 class="text-2xl font-bold mb-4">Últimas recetas</h3>
      <ul class="border-t-4 border-b-4 border-gray-300 py-4">
        @foreach ($ultimos_shows as $show)
          <li class="py-2"><a href="{{ url('mitos/' . $show->slug) }}"
              class="text-lg hover:underline">{{ $show->titulo }}</a></li>
          <hr class="my-2">
        @endforeach
      </ul>
    </div>

  </div>

</x-app-layout>
