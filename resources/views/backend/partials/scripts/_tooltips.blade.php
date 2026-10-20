@once
  <style>
    .tooltip-inner { max-width: 320px; text-align: left; font-size: .8rem; }
  </style>
  <script>
    // Activa los tooltips de ayuda (data-toggle="tooltip"): al pasar el mouse o al enfocar con teclado.
    window.addEventListener('load', function () {
      if (window.jQuery && jQuery.fn.tooltip) {
        jQuery('[data-toggle="tooltip"]').tooltip({ container: 'body', trigger: 'hover focus' });
      }
    });
  </script>
@endonce
