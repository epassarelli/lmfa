@props(['text'])

{{-- Ayuda de campo como ícono con tooltip. Va a continuación de la etiqueta (no dentro: un clic en el <label> activaría el campo). --}}
<span class="text-info ml-1" style="cursor:help" tabindex="0" role="img" data-toggle="tooltip" data-placement="top"
  title="{{ $text }}" aria-label="Ayuda: {{ $text }}"><i class="fas fa-question-circle"></i></span>
