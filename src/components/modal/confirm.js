// components/modal/confirm.js
(function () {
  var root = document.getElementById('modal-confirm');
  if (!root) return;

  var title  = root.querySelector('#c-modal-title');
  var body   = root.querySelector('#c-modal-message');
  var btnOk  = root.querySelector('[data-confirm-ok]');
  var btnNo  = root.querySelector('[data-confirm-cancel]');
  var closes = root.querySelectorAll('[data-confirm-close]');

  var resolver = null;
  var lastFocus = null;

  function open(opts) {
    opts = opts || {};

    title.textContent = opts.title   || 'Confirmação';
    body.textContent  = opts.message || 'Tem certeza desta ação?';

    btnOk.textContent = opts.confirmText || opts.okText || 'Confirmar';
    btnNo.textContent = opts.cancelText  || 'Cancelar';

    var variant = (opts.variant || opts.confirmVariant || 'danger');
    btnOk.classList.toggle('btn--danger',  variant === 'danger');
    btnOk.classList.toggle('btn--primary', variant === 'primary');

    lastFocus = document.activeElement;
    root.classList.add('is-open');
    root.setAttribute('aria-hidden', 'false');

    setTimeout(function () { try { btnNo.focus(); } catch(e) {} }, 10);

    return new Promise(function (resolve) { resolver = resolve; });
  }

  function close(result) {
    root.classList.remove('is-open');
    root.setAttribute('aria-hidden', 'true');
    if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch(e) {} }
    if (resolver) { resolver(!!result); resolver = null; }
  }

  btnOk.addEventListener('click',   function(){ close(true);  });
  btnNo.addEventListener('click',   function(){ close(false); });
  closes.forEach(function(el){ el.addEventListener('click', function(){ close(false); }); });

  document.addEventListener('keydown', function (ev) {
    if (!root.classList.contains('is-open')) return;
    if (ev.key === 'Escape') { ev.preventDefault(); close(false); }
  });

  window.openConfirm  = open;
  window.showConfirm  = window.showConfirm  || open;
  window.confirmModal = window.confirmModal || open;
})();
