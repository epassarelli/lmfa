@props(['festival', 'journey' => null])

<a href="{{ route('festivales.show', $festival->slug) }}"
  @if($journey)
    data-journey-link
    data-source-type="{{ $journey['sourceType'] }}"
    data-source-id="{{ $journey['sourceId'] }}"
    data-destination-type="festival"
    data-destination-id="{{ $festival->id }}"
    data-module="{{ $journey['module'] }}"
    data-position="{{ $journey['position'] }}"
    aria-label="Festival: {{ $festival->title }}"
  @endif
  class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">
  <div class="overflow-hidden">
    <x-editorial-image
      :entity="$festival"
      variant="card"
      class="w-full h-48 object-cover transition-transform duration-300 ease-in-out hover:scale-105"
      loading="lazy"
    />
  </div>

  <div class="p-4 flex flex-col justify-between flex-grow">
    <h2 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">
      {{ $festival->title }}
    </h2>

    <p class="text-sm text-gray-500 mb-1">
      {{ $festival->provincia?->nombre }}@if($festival->locality) · {{ $festival->locality->name }}@endif
    </p>

    <div class="text-sm text-[#ff661f] font-medium mb-2">
      {{ $festival->mes?->nombre ?? 'Mes sin definir' }}
    </div>

    <p class="text-sm text-gray-500 line-clamp-2">
      {{ \Illuminate\Support\Str::limit(\App\Support\SeoMetadata::clean($festival->excerpt ?: $festival->body), 110) }}
    </p>
  </div>
</a>
