<div>
  <!-- The best way to take care of the future is to take care of the present moment. - Thich Nhat Hanh -->
  @props(['festival'])

  <a href="{{ route('festivales.show', $festival->slug) }}"
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">
    <div class="overflow-hidden">
      <img src="{{ asset('storage/festivales/' . $festival->foto) }}" alt="{{ $festival->titulo }}"
        class="w-full h-48 object-cover transition-transform duration-300 ease-in-out hover:scale-105">
    </div>

    <div class="p-4 flex flex-col justify-between flex-grow">
      <h2 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">
        {{ $festival->titulo }}
      </h2>

      <p class="text-sm text-gray-500 mb-1">
        {{ $festival->provincia?->nombre }}
      </p>

      <div class="text-sm text-[#ff661f] font-medium mb-2">
        {{ \Carbon\Carbon::parse($festival->fecha)->format('d M Y') }}
      </div>

      <p class="text-sm text-gray-500 line-clamp-2">
        {!! Str::limit(strip_tags($festival->detalle), 80) !!}
      </p>
    </div>
  </a>

</div>
