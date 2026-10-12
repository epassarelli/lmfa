@props(['profile'])
@php($spec = config("backoffice_help.images.$profile"))
@if ($spec)
  <small class="form-text text-muted" data-image-hint="{{ $profile }}"
    data-min-w="{{ $spec['min_w'] }}" data-min-h="{{ $spec['min_h'] }}" data-ratio="{{ $spec['ratio'][0] }}:{{ $spec['ratio'][1] }}"
    data-mode="{{ $spec['mode'] }}" data-max-side="{{ $spec['max_side'] }}" data-max-mb="{{ $spec['max_mb'] }}">
    Sugerido: <strong>{{ $spec['shape'] }}</strong>, desde <strong>{{ $spec['min_w'] }} × {{ $spec['min_h'] }} px</strong>
    (más chica puede verse borrosa; más de {{ $spec['max_side'] }} px no mejora nada).
    JPG, PNG o WebP, hasta {{ $spec['max_mb'] }} MB. {{ $spec['tip'] }}
    <span class="d-block" data-image-hint-warning></span>
  </small>
  @once
    <script>
      // Aviso NO bloqueante al elegir una imagen: compara con las medidas sugeridas. Nunca impide guardar,
      // salvo el peso máximo, que el servidor sí rechaza.
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-image-hint]').forEach(function (hint) {
          var node = hint.parentElement, input = null;
          while (node && !input) { input = node.querySelector('input[type="file"]'); node = node.parentElement; }
          if (!input) { return; }
          var out = hint.querySelector('[data-image-hint-warning]');
          var num = function (k) { return parseFloat(hint.dataset[k]); };
          var ratio = hint.dataset.ratio.split(':').map(Number);
          var target = ratio[0] / ratio[1];

          input.addEventListener('change', function () {
            out.textContent = '';
            out.className = 'd-block';
            Array.prototype.forEach.call(input.files, function (file) {
              var notes = [];
              if (file.size > num('maxMb') * 1048576) {
                notes.push('pesa ' + (file.size / 1048576).toFixed(1) + ' MB y el máximo es ' + num('maxMb') + ' MB: no se va a poder guardar');
              }
              var url = URL.createObjectURL(file), img = new Image();
              img.onload = function () {
                var w = img.naturalWidth, h = img.naturalHeight, r = w / h;
                if (w < num('minW') || h < num('minH')) {
                  notes.push('mide ' + w + ' × ' + h + ' px, menos que lo sugerido (' + num('minW') + ' × ' + num('minH') + '): se guarda igual, pero puede verse borrosa');
                }
                if (Math.max(w, h) > num('maxSide')) {
                  notes.push('mide ' + w + ' × ' + h + ' px, más de lo necesario: se guarda igual, pero tarda más en subir');
                }
                if (hint.dataset.mode === 'portrait' ? r > 1.15 : Math.abs(r - target) / target > 0.25) {
                  notes.push(hint.dataset.mode === 'portrait' ? 'es apaisada: para artistas conviene vertical o cuadrada' : 'tiene otra proporción que la sugerida (' + hint.dataset.ratio + '): se recorta al centro');
                }
                URL.revokeObjectURL(url);
                show(file, notes);
              };
              img.onerror = function () { URL.revokeObjectURL(url); show(file, notes); };
              img.src = url;
            });
          });

          function show(file, notes) {
            if (!notes.length) { return; }
            out.className = 'd-block text-warning font-weight-bold';
            out.insertAdjacentText('beforeend', '⚠ ' + file.name + ': ' + notes.join('; ') + '. ');
          }
        });
      });
    </script>
  @endonce
@endif
