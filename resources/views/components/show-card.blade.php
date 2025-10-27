<div>
  <!-- Live as if you were to die tomorrow. Learn as if you were to live forever. - Mahatma Gandhi -->
  @props(['show'])

  <a href="#"
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">
    <div class="overflow-hidden">
      <img src="{{ asset('storage/interpretes/' . $show->interprete->foto) }}" alt="{{ $show->interprete->interprete }}"
        class="w-full h-96 object-cover transition-transform duration-300 ease-in-out hover:scale-105">
    </div>

    <div class="p-4 flex flex-col justify-between flex-grow">
      <h2 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">
        {{ $show->interprete->interprete }}
      </h2>

      <p class="text-sm text-gray-500">{{ $show->lugar }}</p>

      <div class="text-sm text-gray-700 mt-2">
        <span class="font-medium text-[#ff661f]">
          {{ \Carbon\Carbon::parse($show->fecha)->format('d M Y') }}
        </span>
      </div>

      <p class="text-sm text-gray-500 mt-2 line-clamp-2">
        {!! Str::limit(strip_tags($show->detalle), 80) !!}
      </p>
    </div>
  </a>

</div>
