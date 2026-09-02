@props(['title', 'module' => null, 'sourceType' => null, 'sourceId' => null, 'items' => collect()])

<section class="bg-white rounded-xl shadow-sm p-6 mb-6" @if($module) data-journey-list data-module="{{ $module }}" data-source-type="{{ $sourceType }}" data-source-id="{{ $sourceId }}" data-item-ids="{{ $items->pluck('id')->join(',') }}" @endif>
  <h2 class="text-xl font-semibold text-slate-900 mb-4">{{ $title }}</h2>
  {{ $slot }}
</section>
