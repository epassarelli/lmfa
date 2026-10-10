<script>
document.addEventListener('DOMContentLoaded', function () {
  const $venue = $('#venue_id');
  function showLocation() {
    const option = $venue.find(':selected')[0];
    if (!option) return;
    for (const [id, key] of [['city', 'city'], ['address', 'address'], ['province_id', 'province']]) {
      const input = document.getElementById(id);
      if (!input) continue;
      if (option.value && option.dataset[key] !== undefined) input.value = option.dataset[key];
      input.disabled = !!option.value;
    }
  }
  $venue.on('change', showLocation);
  showLocation();
});
</script>
