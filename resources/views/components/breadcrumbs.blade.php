@props([
    'items' => [] // Array de ['label' => '...', 'url' => '...']
])

@php
    $items = collect($items)
        ->filter(fn ($item) => filled($item['label'] ?? null))
        ->values();
@endphp

<nav aria-label="Breadcrumb" class="flex mb-4 overflow-x-auto whitespace-nowrap text-sm text-gray-600 bg-white p-3 rounded-lg shadow-sm">
    <ol class="flex list-none p-0">
        <li class="flex items-center">
            <a href="{{ route('home') }}" class="hover:text-amber-800 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>

            </a>
            @if(count($items) > 0)
                <span class="mx-2 text-gray-400">/</span>
            @endif
        </li>
        @foreach($items as $item)
            <li class="flex items-center">
                @if(!$loop->last)
                    @if (filled($item['url'] ?? null))
                        <a href="{{ $item['url'] }}" class="hover:text-amber-800 transition-colors">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-gray-700">
                            {{ $item['label'] }}
                        </span>
                    @endif
                    <span class="mx-2 text-gray-400">/</span>
                @else
                    <span class="text-gray-900 font-medium truncate max-w-[200px] md:max-w-xs" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

{{-- JSON-LD para Breadcrumbs --}}
@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Inicio",
      "item": "{{ route('home') }}"
    }
    @foreach($items as $index => $item)
    ,{
      "@type": "ListItem",
      "position": {{ $index + 2 }},
      "name": "{{ $item['label'] }}",
      "item": "{{ isset($item['url']) ? \App\Support\CanonicalUrl::normalize($item['url']) : \App\Support\CanonicalUrl::current() }}"
    }
    @endforeach
  ]
}
</script>
@endpush
