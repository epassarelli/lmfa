document.addEventListener('DOMContentLoaded', () => {
  const trackJourney = (event, payload) => {
    window.dataLayer = window.dataLayer || [];
    if (typeof window.gtag === 'function') window.gtag('event', event, payload);
    else window.dataLayer.push(['event', event, payload]);
  };

  document.querySelectorAll('[data-journey-list]').forEach((section) => {
    const ids = (section.dataset.itemIds || '').split(',').filter(Boolean).map((id) => ({ item_id: id }));
    if (ids.length) {
      trackJourney('view_item_list', {
        item_list_id: section.dataset.module,
        journey_source_type: section.dataset.sourceType,
        journey_source_id: section.dataset.sourceId,
        items: ids,
      });
    }
  });

  document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-journey-link]');
    if (!link) return;
    trackJourney('select_content', {
      content_type: `${link.dataset.sourceType}_to_${link.dataset.destinationType}`,
      item_id: link.dataset.destinationId,
      journey_source_type: link.dataset.sourceType,
      journey_source_id: link.dataset.sourceId,
      journey_module: link.dataset.module,
      journey_position: Number(link.dataset.position),
    });
  });

  const toggleButton = document.querySelector('[data-mobile-menu-toggle]');
  const menu = document.querySelector('[data-mobile-menu]');

  if (!toggleButton || !menu) {
    return;
  }

  const openMenu = () => {
    menu.classList.remove('hidden');
    toggleButton.setAttribute('aria-expanded', 'true');
  };

  const closeMenu = () => {
    menu.classList.add('hidden');
    toggleButton.setAttribute('aria-expanded', 'false');
  };

  toggleButton.addEventListener('click', () => {
    if (menu.classList.contains('hidden')) {
      openMenu();
      return;
    }

    closeMenu();
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
      menu.classList.remove('hidden');
      toggleButton.setAttribute('aria-expanded', 'false');
      return;
    }

    closeMenu();
  });
});


// Formularios que viven en paginas con full-page cache (ver
// App\Support\ResponseCache\PublicPagesCacheProfile) no pueden traer un
// @csrf renderizado por el servidor -- quedaria "congelado" en la copia
// cacheada y fallaria con 419 para cualquier visitante que no sea el que
// genero esa copia. En su lugar, el token se pide fresco por AJAX recien
// cuando la persona interactua con el form.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-csrf-refresh]').forEach((form) => {
    let tokenPromise = null;

    const fetchToken = () => {
      if (!tokenPromise) {
        tokenPromise = fetch('/csrf-refresh', { credentials: 'same-origin' })
          .then((response) => response.json())
          .then((data) => {
            const input = form.querySelector('input[name="_token"]');
            if (input) {
              input.value = data.token;
            }
            return data.token;
          })
          .catch(() => null);
      }
      return tokenPromise;
    };

    form.addEventListener('focusin', fetchToken, { once: true });

    form.addEventListener('submit', (event) => {
      const input = form.querySelector('input[name="_token"]');
      if (input && input.value) {
        return;
      }

      event.preventDefault();
      fetchToken().then(() => form.submit());
    });
  });
});
