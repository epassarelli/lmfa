{{-- Bloque de enlaces relacionados con tracking; no se renderiza si no hay ítems. Mismo patrón que la ficha de festival. --}}
@if ($items->isNotEmpty())
  <x-content-journey.section :title="$title" :module="$module" source-type="event" :source-id="$show->id" :items="$items">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach ($items as $item)
        <a href="{{ $url($item) }}" data-journey-link data-source-type="event" data-source-id="{{ $show->id }}" data-destination-type="{{ $destinationType }}" data-destination-id="{{ $item->id }}" data-module="{{ $module }}" data-position="{{ $loop->iteration }}" aria-label="{{ $label }}: {{ $item->title }}" class="rounded-lg border border-slate-200 p-4 font-semibold hover:text-orange-700">{{ $item->title }}</a>
      @endforeach
    </div>
  </x-content-journey.section>
@endif
