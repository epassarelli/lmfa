@props([
    'image',
    'variant',
    'sizes' => '100vw',
    'alt' => null,
    'class' => '',
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
    'minimal' => false,
])

@php
    $allVariants = is_array($image->variants_json) ? $image->variants_json : (json_decode($image->variants_json, true) ?? []);

    if (! empty($allVariants[$variant])) {
        $variants = $allVariants[$variant];
    } elseif (! empty($allVariants)) {
        $variants = reset($allVariants);
    } else {
        $variants = [];
    }

    ksort($variants);

    $srcset = [];
    foreach ($variants as $width => $url) {
        $srcset[] = "{$url} {$width}w";
    }

    $srcsetImage = implode(', ', $srcset);

    $defaultWidthsByVariant = [
        'sidebar' => 120,
        'card' => 480,
        'main' => 600,
        'detail' => 1024,
        'hero' => 1024,
    ];

    $defaultWidth = $defaultWidthsByVariant[$variant] ?? array_key_last($variants);
    $defaultSrc = $minimal ? reset($variants) : end($variants);

    if (! $minimal) {
        foreach ($variants as $width => $url) {
            if ((int) $width >= $defaultWidth) {
                $defaultSrc = $url;
                break;
            }
        }

        if (! $defaultSrc) {
            $defaultSrc = reset($variants);
        }
    }

    if ($minimal) {
        $srcsetImage = '';
    }

    if ($image->original_width && $image->original_height && ! empty($variants)) {
        $aspectRatio = $image->original_height / $image->original_width;
        $maxWidth = array_key_last($variants);
        $maxHeight = (int) round($maxWidth * $aspectRatio);
    }
@endphp

@if (! empty($variants))
    @if ($minimal)
        <img
            src="{{ reset($variants) }}"
            alt="{{ $alt ?? $image->alt }}"
            class="{{ $class }}"
            loading="{{ $loading }}"
            decoding="async"
        >
    @else
        <img
            src="{{ $defaultSrc }}"
            srcset="{{ $srcsetImage }}"
            sizes="{{ $sizes }}"
            alt="{{ $alt ?? $image->alt }}"
            class="{{ $class }}"
            loading="{{ $loading }}"
            fetchpriority="{{ $fetchpriority }}"
            decoding="async"
            @if (isset($maxWidth)) width="{{ $maxWidth }}" height="{{ $maxHeight }}" @endif
        >
    @endif
@else
    <img
        src="{{ $image->original_path }}"
        alt="{{ $alt ?? $image->alt }}"
        class="{{ $class }}"
        loading="{{ $loading }}"
        decoding="async"
    >
@endif
