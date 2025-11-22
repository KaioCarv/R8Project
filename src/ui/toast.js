
(function () {
  const HOVER_MAX = 4000; 
  const root = document.getElementById('toast-root') || (() => {
    const d = document.createElement('div');
    d.id = 'toast-root';
    document.body.appendChild(d);
    return d;
  })();

  function icon(type){
    const base = 'stroke="currentColor" stroke-width="2" fill="none"';
    if (type === 'success') return `<svg width="20" height="20" viewBox="0 0 24 24" ${base}><path d="M20 6 9 17l-5-5"/></svg>`;
    if (type === 'warning') return `<svg width="20" height="20" viewBox="0 0 24 24" ${base}><path d="M12 9v4"/><path d="M12 17h.01"/><path d="m10.29 3.86-8 13.86A2 2 0 0 0 4 21h16a2 2 0 0 0 1.71-3.28l-8-13.86a2 2 0 0 0-3.42 0z"/></svg>`;
    if (type === 'error')   return `<svg width="20" height="20" viewBox="0 0 24 24" ${base}><path d="m18 6-12 12"/><path d="m6 6 12 12"/></svg>`;
    return `<svg width="20" height="20" viewBox="0 0 24 24" ${base}><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>`;
  }

  function reallyRemove(el){
    el.removeEventListener('animationend', reallyRemove);
    el.removeEventListener('transitionend', reallyRemove);
    clearTimeout(el._fallbackRemove);
    el.remove();
  }

  function closeToast(el){
    if (el.dataset.closing) return;
    el.dataset.closing = '1';
    clearTimeout(el._timerId);
    clearTimeout(el._hoverId);
    el.style.animation = 'toast-out .2s ease forwards';
    el.addEventListener('animationend', () => reallyRemove(el));
    el.addEventListener('transitionend', () => reallyRemove(el));
    el._fallbackRemove = setTimeout(() => reallyRemove(el), 350);
  }

  function showToast(msg, opts = {}){
    const { title = '', type = 'info', duration = 3000 } = opts;
    const persistent = duration <= 0;

    const el = document.createElement('div');
    el.className = `toast toast--${type}`;
    el.innerHTML = `
      <span class="toast__icon">${icon(type)}</span>
      <div>
        ${title ? `<div class="toast__title">${title}</div>` : ''}
        <div class="toast__msg">${String(msg ?? '')}</div>
      </div>
      <button class="toast__close" aria-label="Fechar">&times;</button>
      <div class="toast__bar"></div>
    `;

    const bar = el.querySelector('.toast__bar');
    const btn = el.querySelector('.toast__close');


    el._timerId = null;
    el._hoverId = null;
    el._birth   = Date.now();
  
    el._deadline = persistent ? Infinity : el._birth + duration + 1000;

    function startCountdown(){
      if (persistent) {
        if (bar) bar.style.animationPlayState = 'paused';
        return;
      }
      clearTimeout(el._timerId);
      const left = Math.max(50, el._deadline - Date.now());
      if (bar) {
        bar.style.animationDuration = `${left}ms`;
        bar.style.animationPlayState = 'running';
      }
      el._timerId = setTimeout(() => closeToast(el), left);
    }

    function pauseCountdown(){
      if (persistent) return;
      clearTimeout(el._timerId);
      if (bar) bar.style.animationPlayState = 'paused';
      clearTimeout(el._hoverId);
      el._hoverId = setTimeout(() => closeToast(el), HOVER_MAX);
    }

    function resumeCountdown(){
      if (persistent) return;
      clearTimeout(el._hoverId);
      startCountdown();
    }

    btn.addEventListener('click', () => closeToast(el));
    el.addEventListener('pointerenter', pauseCountdown);
    el.addEventListener('pointerleave', resumeCountdown);

    root.appendChild(el);

    if (bar) {
      bar.style.animationName = 'toast-bar';
      bar.style.animationTimingFunction = 'linear';
      bar.style.animationFillMode = 'forwards';
    }
    startCountdown();

    return el;
  }

  window.showToast    = showToast;
  window.toastSuccess = (m,t='Sucesso') => showToast(m,{type:'success', title:t});
  window.toastWarn    = (m,t='Atenção') => showToast(m,{type:'warning', title:t});
  window.toastError   = (m,t='Erro')    => showToast(m,{type:'error',   title:t});
  window.toastInfo    = (m,t='Info')    => showToast(m,{type:'info',    title:t});
  window.toastHideAll = () => [...root.querySelectorAll('.toast')].forEach(closeToast);
})();
