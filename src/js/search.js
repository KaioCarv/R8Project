
(function () {
  function goSearch(q) {
    q = (q || '').trim();
    if (!q) return;
    window.location.assign('?page=busca&q=' + encodeURIComponent(q));
  }

  document.addEventListener('click', function (ev) {
    const btn = ev.target.closest('[data-search-submit]');
    if (!btn) return;
    const input = document.querySelector('[data-search-input]') || document.querySelector('.search-input');
    if (input) goSearch(input.value);
  });

  document.addEventListener('keydown', function (ev) {
    if (!ev.target.matches('[data-search-input], .search-input')) return;
    if (ev.key !== 'Enter') return;
    ev.preventDefault();
    goSearch(ev.target.value);
  });

  document.addEventListener('keydown', function (ev) {
    const isIcon = ev.target.matches('[data-search-submit]');
    if (!isIcon || ev.key !== 'Enter') return;
    ev.preventDefault();
    const input = document.querySelector('[data-search-input], .search-input');
    if (input) goSearch(input.value);
  });
})();
