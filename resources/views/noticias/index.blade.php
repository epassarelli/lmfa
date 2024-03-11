{{-- Tengo la siguiente view, index.show y tengo el inconveniente que tanto sea por el aspecto de las imagenes como por el
largo del texto del titulo las cards tienen distintas alturas.

Como puedo hacer para que queden todas con la misma altura? --}}

{{-- Si en lugar de mostrar la lista de noticias tal como la tengo, paginada, quisiera hacer un scroll infinito con Livewire
se puede hacer? Me dices como? --}}
@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>
  <div class="flex flex-wrap justify-center">
    {{ auth()->user() }}
    <br>
    @if (!empty($administrados))
      @foreach ($administrados as $inte)
        {{ $inte }}
      @endforeach
    @else
      {{ 'No posee interpretes administrados' }}
    @endif


  </div>
  <!-- Listado de noticias en cards -->
  <div class="flex flex-wrap justify-center">
    <?php foreach($noticias as $noticia): ?>

    <div class="w-full sm:w-1/2 md:w-1/3 p-4">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
        <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}">
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
