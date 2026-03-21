@props([
    'image',         // Instancia del modelo App\Models\Image
    'variant',       // Nombre de la variante (e.g., 'card', 'detail')
    'sizes' => '100vw', // Atributo sizes para el navegador
    'alt' => null,
    'class' => '',
    'loading' => 'lazy'
])

@php
    $variants = $image->variants_json[$variant] ?? [];
    ksort($variants); // Asegurar que los tamaños estén ordenados de menor a mayor

    $srcset = [];
    foreach ($variants as $width => $url) {
        $srcset[] = "{$url} {$width}w";
    }
    $srcsetImage = implode(', ', $srcset);
    
    // El src por defecto será la variante más grande disponible para esa variante específica
    $defaultSrc = end($variants);
    
    // Obtener las dimensiones para evitar layout shift (opcionalmente podemos calcularlas)
    $width = key($variants); // tomamos el primer width como referencia o el original
    $height = null;
    
    // Si tenemos el original_width y original_height en el modelo, podemos calcular el aspect ratio
    if ($image->original_width && $image->original_height) {
        $aspectRatio = $image->original_height / $image->original_width;
        // Tomamos el width más grande de la variante para el tag img descriptivo
        $maxWidth = array_key_last($variants);
        $maxHeight = (int) round($maxWidth * $aspectRatio);
    }
@endphp

@if(!empty($variants))
    <img 
        src="{{ $defaultSrc }}" 
        srcset="{{ $srcsetImage }}" 
        sizes="{{ $sizes }}" 
        alt="{{ $alt ?? $image->alt }}" 
        class="{{ $class }}"
        loading="{{ $loading }}"
        @if(isset($maxWidth)) width="{{ $maxWidth }}" height="{{ $maxHeight }}" @endif
    >
@else
    <!-- Fallback si no hay variantes para este perfil/variante -->
    <img src="{{ $image->original_path }}" alt="{{ $alt ?? $image->alt }}" class="{{ $class }}" loading="{{ $loading }}">
@endif
