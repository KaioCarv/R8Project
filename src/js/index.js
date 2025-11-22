
    document.addEventListener('click', function (ev) {
      const btn = ev.target.closest('[data-action]');
      if (!btn) return;
      console.log('clicou em:', btn.dataset.action);
    });