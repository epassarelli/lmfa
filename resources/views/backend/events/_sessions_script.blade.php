<script>
document.addEventListener('DOMContentLoaded', function () {
  const fields = document.getElementById('multiple-event-fields');
  if (!fields) return;
  const rows = document.getElementById('sessions');
  function initialize(root) {
    $(root).find('.session-artists,.session-venue').each(function () {
      if (!$(this).hasClass('select2-hidden-accessible')) $(this).select2({width: '100%', theme: 'bootstrap4'});
    });
  }
  function setFormat() {
    const multiple = document.querySelector('[name="event_format"]:checked').value === 'multiple';
    fields.hidden = !multiple;
    fields.disabled = !multiple;
    document.querySelectorAll('[data-single-event]').forEach(function (block) {
      block.hidden = multiple;
      block.querySelectorAll('input,select,textarea').forEach(input => input.disabled = multiple);
    });
    if (multiple) initialize(rows);
    else $('#venue_id').trigger('change');
  }
  document.querySelectorAll('[name="event_format"]').forEach(input => input.addEventListener('change', setFormat));
  setFormat();
  let next = Math.max(...Array.from(rows.children).map(row => Number(row.dataset.index)), -1) + 1;
  document.getElementById('add-session').addEventListener('click', function () {
    if (rows.children.length >= 60) { alert('Máximo 60 jornadas por carga.'); return; }
    rows.insertAdjacentHTML('beforeend', document.getElementById('session-template').innerHTML.replaceAll('__INDEX__', String(next++)));
    initialize(rows.lastElementChild);
  });
  rows.addEventListener('click', function (event) {
    if (event.target.closest('.remove-session') && rows.children.length > 2) event.target.closest('.session-row').remove();
  });
});
</script>
