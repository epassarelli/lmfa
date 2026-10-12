@props(['profile'])
@php
  $spec = config("backoffice_help.images.$profile");
  // Ancho máximo útil = variante más grande del perfil de imágenes (más no se puede mostrar).
  $outW = $spec ? (int) collect(config("image_profiles.{$spec['profile']}.variants"))->flatMap(fn ($v) => $v['sizes'] ?? [])->max() : 0;
  $outH = $spec ? (int) round($outW * $spec['ratio'][1] / $spec['ratio'][0]) : 0;
@endphp
@if ($spec)
  <small class="form-text text-muted" data-image-hint="{{ $profile }}"
    data-min-w="{{ $spec['min_w'] }}" data-min-h="{{ $spec['min_h'] }}" data-ratio="{{ $spec['ratio'][0] }}:{{ $spec['ratio'][1] }}"
    data-out-w="{{ $outW }}" data-out-h="{{ $outH }}" data-max-mb="{{ $spec['max_mb'] }}">
    Sugerido: <strong>{{ $spec['shape'] }}</strong>, desde <strong>{{ $spec['min_w'] }} × {{ $spec['min_h'] }} px</strong>
    (más chica puede verse borrosa). Si es más grande, se reduce sola a {{ $outW }} px de ancho; si tiene otra proporción,
    vas a poder recortarla antes de subirla. JPG, PNG o WebP, hasta {{ $spec['max_mb'] }} MB. {{ $spec['tip'] }}
    <span class="d-block" data-image-hint-warning></span>
  </small>
  @once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <div class="modal fade" id="image-editor-modal" tabindex="-1" data-backdrop="static" data-keyboard="false" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Ajustá la imagen</h5></div>
          <div class="modal-body">
            <p class="small text-muted" data-editor-help></p>
            <div style="max-height:60vh"><img alt="Imagen a recortar" data-editor-image style="max-width:100%"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-editor-cancel>Cancelar</button>
            <button type="button" class="btn btn-primary" data-editor-ok>Usar este recorte</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      // Ajuste obligatorio de imágenes en el navegador. Al elegir un archivo:
      //  - si ya cumple (proporción y ancho), se sube sin tocar;
      //  - si es más ancho que lo que el sitio puede mostrar, se reduce sola (sin agrandar nunca);
      //  - si tiene otra proporción, se abre el editor de recorte: hay que confirmarlo o elegir otra imagen.
      // El servidor valida tipo y peso, y acota el original guardado al mismo ancho (ImageUploadService).
      document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('image-editor-modal');
        if (modalEl) { document.body.appendChild(modalEl); } // el modal va al nivel del body, fuera de formularios y columnas
        var canEdit = !!(window.Cropper && window.jQuery && jQuery.fn.modal && modalEl && window.DataTransfer);

        function loadImage(file) {
          return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file), img = new Image();
            img.onload = function () { resolve({ img: img, url: url }); };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('imagen ilegible')); };
            img.src = url;
          });
        }

        // Abre el editor con la proporción fija. Devuelve {x, y, width, height} en píxeles de la imagen, o null si cancela.
        function openEditor(loaded, hint) {
          return new Promise(function (resolve) {
            var imgEl = modalEl.querySelector('[data-editor-image]'), cropper = null, done = false;
            var $modal = jQuery(modalEl);
            modalEl.querySelector('[data-editor-help]').textContent = 'Esta imagen no tiene la proporción ' + hint.dataset.ratio +
              ' que usa el sitio. Elegí qué parte mostrar (arrastrá para mover, rueda o pellizco para acercar). Se va a guardar a ' +
              hint.dataset.outW + ' px de ancho como máximo.';
            var ratio = hint.dataset.ratio.split(':').map(Number);

            function finish(result) {
              if (done) { return; }
              done = true;
              if (cropper) { cropper.destroy(); }
              $modal.off('.imgEditor').modal('hide');
              imgEl.removeAttribute('src');
              resolve(result);
            }

            $modal.one('shown.bs.modal.imgEditor', function () {
              cropper = new Cropper(imgEl, { aspectRatio: ratio[0] / ratio[1], viewMode: 1, autoCropArea: 1, dragMode: 'move', background: false });
            });
            imgEl.src = loaded.url;
            modalEl.querySelector('[data-editor-ok]').onclick = function () { finish(cropper ? cropper.getData(true) : null); };
            modalEl.querySelector('[data-editor-cancel]').onclick = function () { finish(null); };
            $modal.modal({ backdrop: 'static', keyboard: false, show: true });
          });
        }

        function toJpeg(canvas, name, maxBytes) {
          var qualities = [0.9, 0.8, 0.7, 0.6, 0.5];
          return new Promise(function (resolve) {
            (function attempt(i) {
              canvas.toBlob(function (blob) {
                if (!blob) { resolve(null); return; }
                if (blob.size <= maxBytes || i >= qualities.length - 1) {
                  resolve(new File([blob], name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                } else {
                  attempt(i + 1);
                }
              }, 'image/jpeg', qualities[i]);
            })(0);
          });
        }

        document.querySelectorAll('[data-image-hint]').forEach(function (hint) {
          var node = hint.parentElement, input = null;
          while (node && !input) { input = node.querySelector('input[type="file"]'); node = node.parentElement; }
          if (!input) { return; }

          var out = hint.querySelector('[data-image-hint-warning]');
          var num = function (k) { return parseFloat(hint.dataset[k]); };
          var ratio = hint.dataset.ratio.split(':').map(Number), target = ratio[0] / ratio[1];
          var maxBytes = num('maxMb') * 1048576;

          function note(text, cls) {
            var line = document.createElement('span');
            line.className = 'd-block ' + cls;
            line.textContent = text;
            out.appendChild(line);
          }

          async function processFile(file) {
            if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { return file; }
            var loaded;
            try { loaded = await loadImage(file); } catch (e) { return file; }
            var w = loaded.img.naturalWidth, h = loaded.img.naturalHeight;
            var ratioOk = Math.abs(w / h - target) / target <= 0.03;
            var oversize = w > num('outW');
            var heavy = file.size > maxBytes * 0.9;
            var result = file;

            if (w < num('minW') || h < num('minH')) {
              note(file.name + ' mide ' + w + ' × ' + h + ' px, menos que lo sugerido (' + num('minW') + ' × ' + num('minH') + '): se sube igual, pero puede verse borrosa.', 'text-warning font-weight-bold');
            }

            if (ratioOk && !oversize && !heavy) { URL.revokeObjectURL(loaded.url); return file; }

            var crop = { x: 0, y: 0, width: w, height: h };
            if (!ratioOk) {
              if (!canEdit) {
                note(file.name + ': no se pudo abrir el editor de recorte; se sube sin ajustar (el sitio la recorta al centro).', 'text-warning');
                URL.revokeObjectURL(loaded.url);
                return file;
              }
              crop = await openEditor(loaded, hint);
              if (!crop) {
                URL.revokeObjectURL(loaded.url);
                note(file.name + ': cancelaste el recorte. Elegí la imagen de nuevo para poder guardar.', 'text-danger font-weight-bold');
                return null;
              }
            }

            var scale = Math.min(1, num('outW') / crop.width);
            var canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(crop.width * scale));
            canvas.height = Math.max(1, Math.round(crop.height * scale));
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(loaded.img, crop.x, crop.y, crop.width, crop.height, 0, 0, canvas.width, canvas.height);
            URL.revokeObjectURL(loaded.url);

            var adjusted = await toJpeg(canvas, file.name, maxBytes);
            if (!adjusted) { return file; }
            if (adjusted.size > maxBytes) {
              note(file.name + ' sigue pesando ' + (adjusted.size / 1048576).toFixed(1) + ' MB y el máximo es ' + num('maxMb') + ' MB: no se va a poder guardar.', 'text-danger font-weight-bold');
            }
            note(file.name + ': ajustada a ' + canvas.width + ' × ' + canvas.height + ' px (' + (adjusted.size / 1048576).toFixed(2) + ' MB).', 'text-success');
            return adjusted;
          }

          input.addEventListener('change', async function () {
            out.textContent = '';
            var files = Array.prototype.slice.call(input.files);
            if (!files.length || !window.DataTransfer) { return; }

            input.disabled = true;
            var kept = new DataTransfer();
            for (var i = 0; i < files.length; i++) {
              var result = await processFile(files[i]);
              if (result) { kept.items.add(result); }
            }
            input.disabled = false;
            input.files = kept.files;

            // Los selectores de archivo estilo AdminLTE muestran el nombre en su etiqueta.
            var label = input.parentElement.querySelector('.custom-file-label');
            if (label) { label.textContent = kept.files.length ? Array.prototype.map.call(kept.files, function (f) { return f.name; }).join(', ') : 'Elegir imagen...'; }
          });
        });
      });
    </script>
  @endonce
@endif
