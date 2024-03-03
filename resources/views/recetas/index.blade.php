@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>

  <!-- Listado de noticias en cards -->
  <div class="flex flex-wrap justify-center">
    <?php foreach($recetas as $receta): ?>


    <div class="w-full sm:w-1/2 md:w-1/4 p-4">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <a href="comidas/<?php echo $receta->slug; ?>">
          {{-- <img src="<?php echo $receta->foto; ?>" alt="<?php echo $receta->titulo; ?>" class="w-full h-48 object-cover"> --}}

          @if (file_exists(public_path('storage/recetas/' . $receta->foto)) and $receta->foto !== '')
            <img src="{{ asset('storage/recetas/' . $receta->foto) }}" alt="{{ $receta->titulo }}"
              class="w-full h-48 object-cover">
          @else
            <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
              class="w-full h-48 object-cover">
          @endif

          <div class="p-4">
            <h3 class="font-bold text-xl mb-2"><?php echo $receta->titulo; ?></h3>
          </div>
        </a>
      </div>
    </div>


    <?php endforeach; ?>
  </div>

  <!-- Links del paginado -->
  <div class="flex justify-center p-4">
    <div class="mt-8">
      {{ $recetas->links() }}
    </div>
  </div>

</x-app-layout>
