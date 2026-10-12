@props(['section'])
@php
  $help = config("backoffice_help.sections.$section");
  $user = auth()->user();
  $isAdmin = $user?->hasRole('administrador');
  $openList = isset($help['scope']['member_open']) && $user && $user->hasRole('colaborador')
    && ! config('entity_management.scope_listings') && ! config('backoffice_access.enabled');
  $scopeText = $isAdmin ? $help['scope']['admin'] : ($openList ? $help['scope']['member_open'] : $help['scope']['member']);
@endphp
@if ($help)
  <details class="callout callout-info mb-3" data-list-help="{{ $section }}">
    <summary class="font-weight-bold" style="cursor:pointer"><i class="fas fa-question-circle mr-1"></i> ¿Por qué veo estos registros y no otros?</summary>
    <div class="mt-2 small">
      <p class="mb-2"><strong>{{ $scopeText }}</strong></p>
      @unless ($isAdmin)
        <ul class="pl-3 mb-0">
          <li>Si falta algo que esperabas ver, puede ser de otra persona o no estar vinculado a una ficha tuya.</li>
          <li>Para administrar un artista o festival que ya existe, pedí vincularte desde <a href="{{ route('backend.my-entities.index') }}">Mis fichas</a>.</li>
          <li>Lo que cargaste y todavía no se publicó figura acá como borrador o inactivo.</li>
        </ul>
      @endunless
    </div>
  </details>
@endif
