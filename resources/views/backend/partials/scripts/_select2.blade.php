@once
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    window.addEventListener('load', function() {
      $(".js-example-basic-multiple").select2({
        theme: "classic"
      });
    });
  </script>
@endonce
