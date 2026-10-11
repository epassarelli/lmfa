<script>
document.addEventListener('DOMContentLoaded', function () {
  const options = Array.from(document.querySelectorAll('#event-artist-options option'));
  document.addEventListener('click', function (event) {
    const picker = event.target.closest('.artist-picker');
    if (!picker) return;
    if (event.target.closest('.remove-artist')) { event.target.closest('.artist-chip').remove(); return; }
    if (!event.target.closest('.add-artist')) return;
    const search = picker.querySelector('.artist-search');
    const match = options.find(option => option.value.toLocaleLowerCase() === search.value.trim().toLocaleLowerCase());
    const error = picker.querySelector('.artist-error');
    if (!match) { error.textContent = 'Seleccioná un artista de las sugerencias.'; return; }
    if (Array.from(picker.querySelectorAll('.artist-chip')).some(chip => chip.dataset.id === match.dataset.id)) { error.textContent = 'El artista ya está agregado.'; return; }
    const chip = document.createElement('span'); chip.className = 'artist-chip border rounded px-2 py-1'; chip.dataset.id = match.dataset.id;
    chip.append(document.createTextNode(match.value + ' '));
    const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'remove-artist text-red-700'; remove.textContent = '×'; remove.setAttribute('aria-label', 'Quitar ' + match.value); chip.append(remove);
    const hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = picker.dataset.prefix + '[]'; hidden.value = match.dataset.id; chip.append(hidden);
    picker.querySelector('.artist-chips').append(chip); search.value = ''; error.textContent = '';
  });
  const checkbox = document.getElementById('multiple-sessions');
  if (!checkbox) return;
  const single = document.getElementById('single-session');
  const multiple = document.getElementById('multiple-session-fields');
  function toggle() { single.hidden = checkbox.checked; multiple.hidden = !checkbox.checked;
    for (const [element, disabled] of [[single, checkbox.checked], [multiple, !checkbox.checked]]) element.querySelectorAll('input,select').forEach(input => input.disabled = disabled);
  }
  checkbox.addEventListener('change', toggle); toggle();
  const rows = document.getElementById('contribution-sessions');
  let next = Math.max(...Array.from(rows.children).map(row => Number(row.dataset.index)), -1) + 1;
  document.getElementById('add-contribution-session').addEventListener('click', function () {
    if (rows.children.length >= 60) return;
    rows.insertAdjacentHTML('beforeend', document.getElementById('contribution-session-template').innerHTML.replaceAll('__INDEX__', String(next++)));
    toggle();
  });
  rows.addEventListener('click', event => { if (event.target.closest('.remove-contribution-session') && rows.children.length > 1) event.target.closest('fieldset').remove(); });
});
</script>
