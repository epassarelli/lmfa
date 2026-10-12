@props(['noun' => 'esta página'])
<div class="col-md-12" data-seo-box>
  <details class="mb-3">
    <summary class="font-weight-bold" style="cursor:pointer">Cómo aparece en Google (opcional)</summary>
    <p class="small text-muted mt-2">Es el título y el resumen que Google muestra cuando alguien busca {{ $noun }}. <strong>No hace falta completarlo</strong>: si lo dejás vacío se arma solo con el título y el texto que ya cargaste. Completalo solo si querés cambiar cómo se ve en los buscadores.</p>
    <div class="row">{{ $slot }}</div>
  </details>
</div>
