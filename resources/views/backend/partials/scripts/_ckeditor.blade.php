@once
  <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const editorEl = document.querySelector('#editor');
      if (editorEl) {
        const profile = editorEl.dataset.ckeditorProfile;
        const editorConfig = {};

        if (profile === 'knowledge-article' || profile === 'editorial-body') {
          editorConfig.heading = {
            options: [
              { model: 'paragraph', title: 'Parrafo', class: 'ck-heading_paragraph' },
              { model: 'heading2', view: 'h2', title: 'Titulo 2', class: 'ck-heading_heading2' },
              { model: 'heading3', view: 'h3', title: 'Titulo 3', class: 'ck-heading_heading3' }
            ]
          };
        }

        ClassicEditor.create(editorEl, editorConfig).catch(error => console.error(error));
      }
    });
  </script>
@endonce
