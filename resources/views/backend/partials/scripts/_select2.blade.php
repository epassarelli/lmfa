@once
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container {
      width: 100% !important;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    window.addEventListener('load', function() {
      $('.select2, .js-example-basic-multiple').each(function () {
        const $element = $(this);
        const isMultiple = $element.prop('multiple');
        const placeholder = $element.data('placeholder') || (isMultiple ? 'Seleccionar opciones' : 'Seleccionar una opción');

        $element.select2({
          theme: 'classic',
          width: '100%',
          placeholder: placeholder,
          allowClear: !isMultiple,
        });
      });
    });
  </script>
@endonce
