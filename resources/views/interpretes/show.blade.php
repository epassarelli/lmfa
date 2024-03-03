@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>

  <div class="w-full px-4">
    @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  </div>

  <div class="max-w-xl mx-auto py-8">
    <div class="flex items-center mb-8">
      <img class="w-24 h-24 rounded-full mr-4" src="{{ $interprete->foto }}" alt="{{ $interprete->nombre }}">
      <h1 class="text-3xl font-bold">{{ $interprete->interprete }}</h1>
    </div>

    <div class="mb-8">
      <p class="text-lg">{!! $interprete->biografia !!}</p>
    </div>

    <div class="mb-8">
      <h2 class="text-xl font-bold mb-2">Contacto</h2>
      <p>{{ $interprete->correo }}</p>
      <p>{{ $interprete->telefono }}</p>
      <p>{{ $interprete->direccion }}</p>
    </div>

    <div class="mb-8">
      <h2 class="text-xl font-bold mb-2">Redes sociales</h2>
      <ul class="list-inline">
        <li><a href="{{ $interprete->facebook }}" target="_blank"><i class="fab fa-facebook fa-lg"></i></a></li>
        <li><a href="{{ $interprete->twitter }}" target="_blank"><i class="fab fa-twitter fa-lg"></i></a></li>
        <li><a href="{{ $interprete->instagram }}" target="_blank"><i class="fab fa-instagram fa-lg"></i></a>
        </li>
      </ul>
    </div>





    {{-- @foreach ($recursos as $nombre => $cantidad)
            <div class="rounded-lg bg-white shadow-lg p-4 mb-4">
                <div class="flex items-center justify-center mb-4">
                    @switch($nombre)
                        @case('Noticias')
                            <i class="far fa-newspaper fa-2x mr-2"></i>
                        @break

                        @case('Shows')
                            <i class="fas fa-ticket-alt fa-2x mr-2"></i>
                        @break

                        @case('Discos')
                            <i class="fas fa-compact-disc fa-2x mr-2"></i>
                        @break

                        @case('Canciones')
                            <i class="fas fa-music fa-2x mr-2"></i>
                        @break

                        @case('Fotos')
                            <i class="far fa-images fa-2x mr-2"></i>
                        @break

                        @case('Videos')
                            <i class="fab fa-youtube fa-2x mr-2"></i>
                        @break
                    @endswitch
                    <span class="text-xl font-bold">{{ $nombre }}</span>
                </div>
                @if ($cantidad > 0)
                    <a href="{{ route($nombre . '-de-' . $interprete->slug) }}"
                        class="text-blue-600 hover:text-blue-800">{{ $cantidad }}
                        {{ Str::plural($nombre, $cantidad) }}</a>
                @else
                    <span class="text-gray-400">Sin {{ Str::plural($nombre) }}</span>
                @endif
            </div>
        @endforeach --}}





    {{-- <form action="{{ route('interprete.show', $interprete->slug) }}" method="get">
            <label for="salto">Saltar a:</label>
            <select name="salto" id="salto">
                @foreach ($interpretes as $i)
                    <option value="{{ $i->slug }}">{{ $i->interprete }}</option>
                @endforeach
            </select>
            <button type="submit">Ir</button>
        </form> --}}




  </div>




  <!-- Agregar en el body de la vista -->
  {{-- 
      <script>
        $(document).ready(function() {
            // Inicializar el campo select con Select2
            $('#salto').select2({
                placeholder: 'Buscar intérprete...',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('interpretes.busqueda') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // término de búsqueda ingresado por el usuario
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data // datos de los intérpretes encontrados
                        };
                    },
                    cache: true
                }
            });
        });
    </script> 
    --}}


</x-app-layout>
