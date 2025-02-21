import './bootstrap';

import $ from 'jquery';
window.$ = window.jQuery = $;

import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.min.js';

// import Swal from 'sweetalert2';
// window.Swal = Swal;

import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

// Inicializar Select2 globalmente
$(document).ready(function () {
  if ($.fn.select2) {
    $(".select2").select2({
      placeholder: "Selecciona intérpretes...",
      allowClear: true
    });
  } else {
    console.error("Error: Select2 no está disponible.");
  }
});