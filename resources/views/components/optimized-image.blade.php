@props([
    'image',         // Instancia del modelo App\Models\Image
    'variant',       // Nombre de la variante (e.g., 'card', 'detail')
    'sizes' => '100vw', // Atributo sizes para el navegador
    'alt' => null,
    'class' => '',
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
    'minimal' => false // Para usar solo el tamaño más pequeño
])

@php
    $allVariants = is_array($image->variants_json) ? $image->variants_json : (json_decode($image->variants_json, true) ?? []);

    // Buscar la variante pedida; si no existe, usar la primera disponible
    if (!empty($allVariants[$variant])) {
        $variants = $allVariants[$variant];
    } elseif (!empty($allVariants)) {
        // Fallback: primera variante (ej. 'main' si no hay 'card')
        $variants = reset($allVariants);
    } else {
        $variants = [];
    }

    ksort($variants); // Asegurar que los tamaños estén ordenados de menor a mayor

    $srcset = [];
    foreach ($variants as $width => $url) {
        $srcset[] = "{$url} {$width}w";
    }
    $srcsetImage = implode(', ', $srcset);
    
    // minimal = solo el tamaño más pequeño, sin srcset pesado
    $defaultSrc = $minimal ? reset($variants) : end($variants);
    
    if ($minimal) {
        $srcsetImage = '';
    }
    
    // Dimensiones para evitar layout shift
    if ($image->original_width && $image->original_height) {
        $aspectRatio = $image->original_height / $image->original_width;
        $maxWidth = array_key_last($variants);
        $maxHeight = (int) round($maxWidth * $aspectRatio);
    }
@endphp

@if(!empty($variants))
    @if($minimal)
        {{-- Modo minimal: solo la URL más pequeña, sin srcset, sin width/height automático --}}
        <img
            src="{{ reset($variants) }}"
            alt="{{ $alt ?? $image->alt }}"
            class="{{ $class }}"
            loading="{{ $loading }}"
        >
    @else
        <img
            src="{{ end($variants) }}"
            srcset="{{ $srcsetImage }}"
            sizes="{{ $sizes }}"
            alt="{{ $alt ?? $image->alt }}"
            class="{{ $class }}"
            loading="{{ $loading }}"
            fetchpriority="{{ $fetchpriority }}"
            @if(isset($maxWidth)) width="{{ $maxWidth }}" height="{{ $maxHeight }}" @endif
        >
    @endif
@else
    {{-- Fallback si no hay variantes para este perfil/variante --}}
    <img src="{{ $image->original_path }}" alt="{{ $alt ?? $image->alt }}" class="{{ $class }}" loading="{{ $loading }}">
@endif
