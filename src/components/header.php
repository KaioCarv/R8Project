<?php

$isLogged    = isset($_SESSION['user_id']);
$userName    = $isLogged ? ($_SESSION['user_nome'] ?? '') : '';
$firstLetter = $isLogged && $userName !== ''
  ? strtoupper(substr($userName, 0, 1))
  : '?';
?>
<header class="header">
  <div class="header-inner">
    <div class="logo">R8</div>

    <nav class="nav-main">
      <button
        type="button"
        class="nav-link nav-link--highlight"
        data-open-modal="modal-login">
        Entrar
      </button>

      <button
        type="button"
        class="nav-link nav-link--highlight"
        data-open-modal="modal-register">
        Cadastre-se
      </button>
      
      <button
        type="button"
        class="nav-link nav-link--highlight"
        >
        <a href="?page=rankings" >Filmes</a> 
      </button>

      <button
        type="button"
        class="nav-link nav-link--highlight"
        >
       <a href="?page=feed">Feed</a>
      </button>
      
      
    </nav>

    <div class="header-spacer"></div>

<div class="search-box" role="search">
  <span class="search-icon" data-search-submit tabindex="0" aria-label="Buscar">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
         viewBox="0 0 24 24" fill="none" stroke="black"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         class="lucide lucide-search-icon lucide-search">
      <path d="m21 21-4.34-4.34"></path>
      <circle cx="11" cy="11" r="8"></circle>
    </svg>
  </span>

  <input
    class="search-input"
    type="search"
    placeholder="Pesquisar"
    name="q"
    data-search-input
  />
</div>

    <div class="header-user-area" data-user-menu>
      <?php if ($isLogged): ?>
  
        <button
          type="button"
          class="user-avatar-button"
          data-user-menu-toggle
          aria-haspopup="true"
          aria-expanded="false"
        >
          <span class="user-avatar-circle">
            <?= htmlspecialchars($firstLetter) ?>
          </span>
        </button>

        <div class="user-menu" data-user-menu-dropdown>
          <a href="?page=perfil" class="user-menu-item">
            Meu perfil
          </a>

          <form action="logout.php" method="post">
            <button
              type="submit"
              class="user-menu-item user-menu-item--logout">
              Sair
            </button>
          </form>
        </div>
      <?php else: ?>
      
        <button
          type="button"
          class="user-avatar-button"
          data-open-modal="modal-login"
          title="Crie sua conta">
          <span class="user-avatar-circle user-avatar-circle--guest">?</span>
        </button>
      <?php endif; ?>
    </div>

    <div class="logo-principal">#</div>
  </div>
</header>
