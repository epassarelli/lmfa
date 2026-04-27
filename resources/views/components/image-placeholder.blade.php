@props([
    'label' => 'Sin imagen', // null = modo compacto (solo ícono, sin texto)
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center bg-gray-100 text-gray-300']) }}>
    <svg xmlns="http://www.w3.org/2000/svg"
         class="{{ $label ? 'h-10 w-10 mb-1' : 'h-6 w-6' }}"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
    @if($label)
        <span class="text-xs text-gray-400">{{ $label }}</span>
    @endif
</div>
