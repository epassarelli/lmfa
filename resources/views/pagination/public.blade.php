@if ($paginator->hasPages())
  @php
    $numbered = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $control = 'inline-flex min-h-[44px] items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold leading-5';
    $enabled = 'border-gray-300 bg-white text-gray-700 transition-colors hover:border-orange-600 hover:bg-orange-50 hover:text-orange-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-700';
    $disabled = 'cursor-default border-gray-200 bg-gray-50 text-gray-500';
  @endphp

  <nav aria-label="Paginación" data-public-pagination class="my-6 w-full border-t border-gray-200 pt-5">
    <p class="mb-3 text-center text-sm leading-6 text-gray-600">
      Página <span class="font-semibold text-gray-900">{{ $paginator->currentPage() }}</span>@if ($numbered) de <span class="font-semibold text-gray-900">{{ $paginator->lastPage() }}</span>@endif
      @if ($numbered && $paginator->count())
        <span class="block sm:inline"><span class="hidden sm:inline" aria-hidden="true"> · </span>Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados</span>
      @endif
    </p>

    <div class="flex flex-wrap items-center justify-center gap-2">
      @if ($paginator->onFirstPage())
        <span aria-disabled="true" class="{{ $control }} {{ $disabled }}"><span aria-hidden="true">←</span> Anterior</span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Ir a la página anterior" class="{{ $control }} {{ $enabled }}"><span aria-hidden="true">←</span> Anterior</a>
      @endif

      @if ($numbered)
        <div class="order-last hidden w-full flex-wrap items-center justify-center gap-1 sm:flex xl:order-none xl:w-auto">
          @foreach ($elements as $element)
            @if (is_string($element))
              <span class="inline-flex min-h-[44px] items-center justify-center px-2 text-gray-500" aria-hidden="true">…</span>
            @elseif (is_array($element))
              @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                  <span aria-current="page" aria-label="Página {{ $page }}, página actual" class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg border border-orange-700 bg-orange-700 px-3 py-2 text-sm font-semibold text-white">{{ $page }}</span>
                @else
                  <a href="{{ $url }}" aria-label="Ir a la página {{ $page }}" class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg border px-3 py-2 text-sm font-semibold {{ $enabled }}">{{ $page }}</a>
                @endif
              @endforeach
            @endif
          @endforeach
        </div>
      @endif

      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Ir a la página siguiente" class="{{ $control }} {{ $enabled }}">Siguiente <span aria-hidden="true">→</span></a>
      @else
        <span aria-disabled="true" class="{{ $control }} {{ $disabled }}">Siguiente <span aria-hidden="true">→</span></span>
      @endif
    </div>
  </nav>
@endif
