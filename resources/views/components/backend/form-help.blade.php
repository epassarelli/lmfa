{{-- La ayuda siempre arranca contraída: la persona la abre si quiere leerla. --}}
@props(['section'])
@php
  $help = config("backoffice_help.sections.$section");
  $user = auth()->user();
  $isAdmin = $user?->hasRole('administrador');
  $canPublish = $user?->canPublish();
  // El listado de artistas es completo para colaboradores mientras el acceso por fichas está apagado.
  $openList = isset($help['scope']['member_open']) && $user && $user->hasRole('colaborador')
    && ! config('backoffice_access.enabled');
  $scopeText = $isAdmin ? $help['scope']['admin'] : ($openList ? $help['scope']['member_open'] : $help['scope']['member']);
@endphp
@if ($help)
  <details class="callout callout-info mb-3" data-form-help="{{ $section }}">
    <summary class="font-weight-bold" style="cursor:pointer"><i class="fas fa-question-circle mr-1"></i> ¿Cómo funciona esto?</summary>
    <div class="mt-2 small">
      <p class="mb-2">{{ $help['what'] }}</p>
      <ul class="pl-3 mb-0">
        <li><strong>Qué ves:</strong> {{ $scopeText }}</li>
        <li><strong>Publicación:</strong> {{ $canPublish ? config('backoffice_help.publishing.can') : config('backoffice_help.publishing.cannot') }}</li>
        <li><strong>Obligatorios (<span class="text-danger">*</span>):</strong> {{ implode(', ', $help['required']) }}. El resto es opcional.</li>
        <li><strong>Guía completa:</strong> <a href="{{ route('publish.guide') }}" target="_blank" rel="noopener">Cómo publicar</a>, paso a paso.</li>
        <li><strong>Imágenes y avanzados:</strong> las medidas recomendadas figuran debajo de cada campo de imagen. «Cómo aparece en Google» es opcional: si lo dejás vacío se completa solo.</li>
      </ul>
    </div>
  </details>
  @once
    <script>
      // Marca con * los campos obligatorios (atributo required) que todavía no la tienen.
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form [required]').forEach(function (field) {
          if (field.type === 'hidden' || !field.id) { return; }
          var label = document.querySelector('label[for="' + field.id + '"]');
          if (label && !label.querySelector('.text-danger') && !/\*/.test(label.textContent)) {
            label.insertAdjacentHTML('beforeend', ' <span class="text-danger">*</span>');
          }
        });
      });
    </script>
  @endonce
@endif
