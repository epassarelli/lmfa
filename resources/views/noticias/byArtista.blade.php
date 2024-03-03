@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>

  <div class="w-full px-4">
    @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  </div>

  <!-- Listado de noticias en cards -->
  <div class="flex flex-wrap justify-center">
    <?php foreach($noticias as $noticia): ?>

    <div class="w-full sm:w-1/2 md:w-1/3 p-4">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
        <a href="noticias/<?php echo $noticia->slug; ?>">
          <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
            class="w-full h-48 object-cover">
          <div class="p-4">
            <h3 class="font-bold text-xl mb-2"><?php echo $noticia->titulo; ?></h3>
          </div>
        </a>
      </div>
    </div>

    <?php endforeach; ?>
  </div>

  <!-- Links del paginado -->
  <div class="flex justify-center p-4">
    <div class="mt-8">
      {{ $noticias->links() }}
    </div>
  </div>

</x-app-layout>
