document.addEventListener('DOMContentLoaded', () => {
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
