// src/js/auth.js
(function () {
  
  const ICON_HIDE = `
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="lucide lucide-eye-closed">
      <path d="m15 18-.722-3.25"></path>
      <path d="M2 8a10.645 10.645 0 0 0 20 0"></path>
      <path d="m20 15-1.726-2.05"></path>
      <path d="m4 15 1.726-2.05"></path>
      <path d="m9 18 .722-3.25"></path>
    </svg>
  `;

  const ICON_SHOW = `
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
         viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="lucide lucide-eye">
      <path d="M2.062 12.348a1 1 0 0 1 0-.696
               10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696
               10.75 10.75 0 0 1-19.876 0"></path>
      <circle cx="12" cy="12" r="3"></circle>
    </svg>
  `;


  function closeAllModals() {
    document
      .querySelectorAll('.auth-modal.is-open')
      .forEach(function (el) {
        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');
      });
  }

  function openModal(id) {
    var el = document.getElementById(id);
    if (!el) return;

 
    closeAllModals();

    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
  }

  function closeModal(id) {
   
    if (!id) {
      closeAllModals();
      return;
    }

    var el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('is-open');
    el.setAttribute('aria-hidden', 'true');
  }


  document.addEventListener('DOMContentLoaded', function () {
    document
      .querySelectorAll('[data-toggle-password]')
      .forEach(function (btn) {
        btn.innerHTML = ICON_HIDE;
      });
  });

  document.addEventListener('click', function (ev) {
  
    var openTarget = ev.target.closest('[data-open-modal]');
    if (openTarget) {
      var id = openTarget.getAttribute('data-open-modal');
      openModal(id);
      return;
    }


    var closeTarget = ev.target.closest('[data-close-modal]');
    if (closeTarget) {
      var idClose = closeTarget.getAttribute('data-close-modal');
      
      closeModal(idClose);
      return;
    }

    var toggleBtn = ev.target.closest('[data-toggle-password]');
    if (toggleBtn) {
      var inputId = toggleBtn.getAttribute('data-toggle-password');
      var input = document.getElementById(inputId);
      if (!input) return;

      var isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      toggleBtn.innerHTML = isPassword ? ICON_SHOW : ICON_HIDE;
    }
  });


  document.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Escape') return;
    closeAllModals();
  });

  document.addEventListener('submit', function (ev) {
    var form = ev.target.closest('.auth-form');
    if (!form) return;

    ev.preventDefault();

    var tipo = form.getAttribute('data-form'); 
    var fd = new FormData(form);
    fd.append('action', tipo);

    fetch('api/usuario.php', {
      method: 'POST',
      body: fd
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.status) {
          alert('Erro inesperado.');
          return;
        }

        if (tipo === 'login') {
          if (data.status === 'ok') {
            toastSuccess('Login realizado com sucesso!');
            try { window.__modalClose && window.__modalClose('modal-login'); } catch {}

            
            setTimeout(function () {
              window.location.reload();
            }, 1200); 
            return;
          } else if (data.status === 'not_found') {
           toastWarn('Usuário não existe, faça seu cadastro.');
          } else if (data.status === 'invalid_password') {
            toastError('Senha incorreta.');
          } else {
            toastError(data.message || 'Erro ao fazer login.');
          }
        }


        if (tipo === 'register') {
          if (data.status === 'ok') {
            toastSuccess('Cadastro realizado! Agora é só fazer login.');
            closeModal('modal-register');
         
          } else if (data.status === 'exists') {
            toastWarn('Usuário ou e-mail já cadastrado.');
          } else if (data.status === 'password_mismatch') {
             toastWarn('As senhas não conferem.');
          } else {
            toastError(data.message || 'Erro ao cadastrar.');
          }
        }
      })
      .catch(function () {
        toastError('Erro na requisição.');
      });
  });
})();
