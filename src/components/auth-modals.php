<?php
?>
<div id="modal-login" class="auth-modal" aria-hidden="true">
  <div class="auth-backdrop" data-close-modal="modal-login"></div>

  <div class="auth-card" role="dialog" aria-modal="true" aria-labelledby="login-title">
    <header class="auth-card-header">
      <div class="auth-logo-left">R8</div>
      <div class="auth-logo-right">#</div>
    </header>

    <form class="auth-form" method="post" autocomplete="off" data-form="login">
      <div class="auth-field">
        <label for="login-usuario">Usuário</label>
        <div class="auth-input-wrap">
          <input
            id="login-usuario"
            name="usuario"
            type="text"
            required
          />
          <span class="auth-input-icon-static" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
        </div>
      </div>

      <div class="auth-field">
        <label for="login-senha">Senha</label>
        <div class="auth-input-wrap">
          <input
            id="login-senha"
            name="senha"
            type="password"
            required
          />
       <button
          type="button"
          class="auth-input-icon-btn"
          data-toggle-password="login-senha"
          aria-label="Mostrar ou ocultar senha">
        
        </button>
        </div>
      </div>

      <button type="submit" class="auth-primary-btn">
        ENTRAR
      </button>

    <p class="auth-switch-text">
      Não tem login?
      <button
        type="button"
        class="auth-switch-link"
        data-open-modal="modal-register">
        Cadastre-se
      </button>
    </p>
    </form>
  </div>
</div>


<div id="modal-register" class="auth-modal" aria-hidden="true">
  <div class="auth-backdrop" data-close-modal="modal-register"></div>

  <div class="auth-card" role="dialog" aria-modal="true" aria-labelledby="register-title">
    <header class="auth-card-header">
      <div class="auth-logo-left">R8</div>
      <div class="auth-logo-right">#</div>
    </header>
    <form class="auth-form" method="post" autocomplete="off" data-form="register">
      <div class="auth-field">
        <label for="reg-email">Email</label>
        <div class="auth-input-wrap">
          <input
            id="reg-email"
            name="email"
            type="email"
            required
          />
          <span class="auth-input-icon-static" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-icon lucide-mail"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
          </span>
        </div>
      </div>

      <div class="auth-field">
        <label for="reg-usuario">Usuário</label>
        <div class="auth-input-wrap">
          <input
            id="reg-usuario"
            name="usuario"
            type="text"
            required
          />
          <span class="auth-input-icon-static" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
        </div>
      </div>

      <div class="auth-field">
        <label for="reg-senha">Senha</label>
        <div class="auth-input-wrap">
          <input
            id="reg-senha"
            name="senha"
            type="password"
            required
          />
        <button
          type="button"
          class="auth-input-icon-btn"
          data-toggle-password="reg-senha"
          aria-label="Mostrar ou ocultar senha">
        </button>
        </div>
      </div>

      <div class="auth-field">
        <label for="reg-senha-confirm">Confirmar Senha</label>
        <div class="auth-input-wrap">
          <input
            id="reg-senha-confirm"
            name="senha_confirm"
            type="password"
            required
          />
       
          <button
            type="button"
            class="auth-input-icon-btn"
            data-toggle-password="reg-senha-confirm"
            aria-label="Mostrar ou ocultar senha">
          </button>
        </div>
      </div>

      <button type="submit" class="auth-primary-btn">
        CRIAR CONTA
      </button>
    </form>
  </div>
</div>
