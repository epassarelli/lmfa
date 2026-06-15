@props([
    'title' => 'Buscar por Orden Alfabético',
    'description' => null,
    'routeName',
    'activeLetter' => null,
    'letters' => range('a', 'z'),
])

<section {{ $attributes->class(['bg-white p-2 rounded shadow-sm mt-4 mb-4']) }}>
  <h2 class="text-xl font-semibold mb-4 border-b-2 border-[#ff661f]">{{ $title }}</h2>

  @if ($description)
    <p class="text-base text-gray-600 mb-4">
      {{ $description }}
    </p>
  @endif

  <div class="border-t border-gray-200 my-4"></div>
  <nav class="flex flex-wrap justify-center gap-2 text-sm" aria-label="{{ $title }}">
    @foreach ($letters as $letter)
      @php $normalizedLetter = strtolower($letter); @endphp
      <a href="{{ route($routeName, $normalizedLetter) }}"
        class="px-4 py-2 rounded uppercase font-semibold transition {{ strtolower((string) $activeLetter) === $normalizedLetter ? 'bg-[#ff661f] text-white' : 'bg-gray-100 text-gray-800 hover:bg-[#ff661f] hover:text-white' }}">
        {{ strtoupper($normalizedLetter) }}
      </a>
    @endforeach
  </nav>
</section>
