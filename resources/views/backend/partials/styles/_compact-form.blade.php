{{-- Controles compactos para formularios del backoffice. Opt-in: solo afecta a los que llevan la clase .form-compact. --}}
@once
  <style>
    .form-compact .form-group { margin-bottom: .6rem; }
    .form-compact label { font-size: .85rem; font-weight: 600; margin-bottom: .15rem; }
    .form-compact .form-control { height: calc(1.8125rem + 2px); padding: .2rem .5rem; font-size: .875rem; }
    .form-compact textarea.form-control { height: auto; }
    .form-compact .form-text,
    .form-compact small { font-size: .75rem; }

    /* Selector de archivo */
    .form-compact .custom-file,
    .form-compact .custom-file-input,
    .form-compact .custom-file-label { height: calc(1.8125rem + 2px); font-size: .875rem; }
    .form-compact .custom-file-label { padding: .2rem .5rem; line-height: 1.5; }
    .form-compact .custom-file-label::after { height: 1.8125rem; padding: .2rem .5rem; line-height: 1.5; }

    /* Select2 (tema classic) */
    .form-compact .select2-container--classic .select2-selection--single { height: calc(1.8125rem + 2px); }
    .form-compact .select2-container--classic .select2-selection--single .select2-selection__rendered { line-height: 1.7rem; font-size: .875rem; }
    .form-compact .select2-container--classic .select2-selection--single .select2-selection__arrow { height: 1.7rem; }
    .form-compact .select2-container--classic .select2-selection--multiple { min-height: calc(1.8125rem + 2px); }
    .form-compact .select2-container--classic .select2-selection--multiple .select2-selection__choice { font-size: .8rem; margin-top: 3px; padding: 0 5px; }

    /* Editor */
    .form-compact .ck-editor__editable_inline { min-height: 220px; }
  </style>
@endonce
