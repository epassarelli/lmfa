@once
  <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const editorEl = document.querySelector('#editor');
      if (editorEl) {
        ClassicEditor.create(editorEl).catch(error => console.error(error));
      }
    });
  </script>
@endonce
