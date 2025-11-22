// src/js/header.js
(function () {
  function closeAllMenus() {
    document
      .querySelectorAll('[data-user-menu-dropdown].is-open')
      .forEach(function (menu) {
        menu.classList.remove('is-open');
      });

    document
      .querySelectorAll('[data-user-menu-toggle][aria-expanded="true"]')
      .forEach(function (btn) {
        btn.setAttribute('aria-expanded', 'false');
      });
  }

  document.addEventListener('click', function (ev) {
    var toggle = ev.target.closest('[data-user-menu-toggle]');
    var menuRoot = ev.target.closest('[data-user-menu]');

    // Clique no botão da bolinha
    if (toggle) {
      var dropdown = menuRoot
        ? menuRoot.querySelector('[data-user-menu-dropdown]')
        : null;

      if (!dropdown) return;

      var isOpen = dropdown.classList.contains('is-open');
      closeAllMenus();

      if (!isOpen) {
        dropdown.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
      }
      return;
    }

    
    if (!ev.target.closest('[data-user-menu]')) {
      closeAllMenus();
    }
  });

  
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape') {
      closeAllMenus();
    }
  });
})();
