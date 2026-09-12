@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <h1 class="text-3xl font-bold mb-2 text-gray-900">Letras de Canciones Folklóricas</h1>
  <p class="text-lg text-gray-700 mb-8">Ya reunimos <strong>{{ $totalCanciones }}</strong> letras en el cancionero popular.</p>

  @if ($masVisitadas->isNotEmpty())
    <section class="mb-16">
      <h2 class="text-2xl font-semibold text-gray-800 mb-2">Canciones folklóricas con más visitas</h2>
      <p class="text-gray-700 mb-6">
        Las letras que más consultaron nuestros visitantes, desde clásicos inolvidables hasta nuevos éxitos.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($masVisitadas as $letra)
          <x-letra-card :letra="$letra" />
        @endforeach
      </div>
    </section>
  @endif

  @if ($ultimasAgregadas->isNotEmpty())
    <section class="mb-16">
      <h2 class="text-2xl font-semibold text-gray-800 mb-2">Últimas letras agregadas</h2>
      <p class="text-gray-700 mb-6">
        Las incorporaciones más recientes a nuestra colección de letras del folklore argentino.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimasAgregadas as $letra)
          <x-letra-card :letra="$letra" />
        @endforeach
      </div>
    </section>
  @endif

  {{-- Todas las letras, orden alfabético --}}
  <section class="mb-16">
    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Todas las letras (A-Z)</h2>
    <p class="text-gray-700 mb-6">
      Explorá el cancionero completo del folklore argentino en orden alfabético.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
      @foreach ($canciones as $letra)
        <x-letra-card :letra="$letra" />
      @endforeach
    </div>

    <x-public-pagination :paginator="$canciones" />

    <!-- Índice alfabético -->
    <section class="mb-12 mt-12 bg-white p-4 rounded shadow-sm">
      <h2 class="text-xl font-semibold mb-2 text-gray-800 border-b-2 border-[#ff661f] pb-2">Buscar por Orden Alfabético</h2>
      <p class="text-base text-gray-600 mb-4 mt-2">
        Encuentra fácilmente una canción de folklore argentino utilizando nuestro índice alfabético.
      </p>

      <ul class="flex flex-wrap justify-center gap-2 text-sm mt-4">
        @foreach (range('a', 'z') as $letra)
          <li>
            <a href="{{ route('canciones.letra', $letra) }}"
              class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-[#ff661f] hover:text-white transition uppercase font-semibold">
              {{ $letra }}
            </a>
          </li>
        @endforeach
      </ul>
    </section>
  </section>

  {{-- Texto final --}}
  <div class="space-y-6 text-lg text-gray-700 leading-relaxed">
    <p>
      En nuestra sección de letras de canciones folklóricas podrás explorar las letras
      de las canciones más emblemáticas de la música folklórica argentina. Desde clásicos inmortales hasta las
      composiciones contemporáneas, aquí encontrarás una vasta colección de letras que reflejan la riqueza y diversidad
      de nuestro folklore.
    </p>
    <p>
      Cada letra está cuidadosamente transcrita y organizada por artista y álbum, facilitándote la búsqueda
      de tus canciones favoritas. Descubre el significado profundo y las historias detrás de cada letra, y cómo han
      influido en
      la cultura y la tradición del folklore argentino. Nuestra colección incluye letras de los artistas y cantantes más
      destacados, así como de talentos emergentes que están dando forma al futuro de la música folklórica.
    </p>
    <p>
      Sumérgete en las palabras que han dado vida a la música folklórica argentina y conecta con las emociones y relatos
      que cada canción transmite. Ya sea que estés buscando una letra específica o simplemente quieras explorar el vasto
      repertorio de nuestro folklore, nuestra sección de letras de canciones es el lugar perfecto para ti.
    </p>
  </div>


@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.advertisement />
  <x-sidebar.invite-to-publish />

@endsection
