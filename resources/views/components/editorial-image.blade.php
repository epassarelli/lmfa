@props([
    'entity',
    'variant' => 'card',
    'class' => '',
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
    'sizes' => '100vw',
    'minimal' => false,
])

@inject('editorialImageResolver', 'App\\Services\\EditorialImageResolver')

@php
    $resolvedImage = $editorialImageResolver->resolve($entity);
@endphp

@if ($resolvedImage->isMedia())
    <x-optimized-image
        :image="$resolvedImage->media"
        :variant="$variant"
        :class="$class"
        :alt="$resolvedImage->alt"
        :loading="$loading"
        :fetchpriority="$fetchpriority"
        :sizes="$sizes"
        :minimal="$minimal"
    />
@else
    <img
        src="{{ $resolvedImage->url }}"
        alt="{{ $resolvedImage->alt }}"
        class="{{ $class }}"
        @if ($minimal && $resolvedImage->isFallback()) style="object-fit: contain;" @endif
        loading="{{ $loading }}"
        fetchpriority="{{ $fetchpriority }}"
        decoding="async"
    >
@endif
