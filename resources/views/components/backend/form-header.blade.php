@props(['mode' => 'create'])
{{-- Encabezado estándar de los formularios del backoffice: ícono de crear (más) o editar (lápiz) y el mismo <h1>. --}}
<h1><i class="fas {{ $mode === 'edit' ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>{{ $slot }}</h1>
