@props(['paginator'])

@if ($paginator->hasPages())
  {{ $paginator->withQueryString()->onEachSide(1)->links('pagination.public') }}
@endif
