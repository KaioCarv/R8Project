<?php
//
session_start();

$page = $_GET['page'] ?? 'feed'; 

$titles = [
  'feed'     => 'Feed',
  'perfil'   => 'Perfil',
  'stats'    => 'Estatísticas',
  'rankings' => 'Rankings Globais',
  'busca'    => 'Resultados da busca',
];

$title = $titles[$page] ?? 'Feed';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>R8 - <?= htmlspecialchars($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS global -->
  <link rel="stylesheet" href="main.css">
  <!-- CSS modais -->
  <link rel="stylesheet" href="auth.css">
  <link rel="stylesheet" href="components/modal/modal.css">


  <!-- CSS compartilhado perfil + estatísticas -->
  <?php if ($page === 'perfil' || $page === 'stats'): ?>
    <link rel="stylesheet" href="pages/perfil/perfil.css">
  <?php endif; ?>

  <!-- CSS específico de cada página -->
  <?php if ($page === 'feed'): ?>
    <link rel="stylesheet" href="pages/feedPrincipal/feed.css">
  <?php elseif ($page === 'stats'): ?>
    <link rel="stylesheet" href="pages/estatisticas/estatisticas.css">
  <?php elseif ($page === 'rankings'): ?>
    <link rel="stylesheet" href="pages/rankings/rankings.css">
  <?php elseif ($page === 'busca'): ?>
    <link rel="stylesheet" href="pages/busca/busca.css">
  <?php endif; ?>
</head>
<body>
  <div class="app">
    <?php require __DIR__ . '/components/header.php'; ?>

    <main class="main">
      <?php
        if ($page === 'perfil') {
          require __DIR__ . '/pages/perfil/index.php';
        } elseif ($page === 'stats') {
          require __DIR__ . '/pages/estatisticas/index.php';
        } elseif ($page === 'rankings') {
          require __DIR__ . '/pages/rankings/index.php';
        } elseif ($page === 'busca') {
          require __DIR__ . '/pages/busca/index.php';
        } else {
          require __DIR__ . '/pages/feedPrincipal/index.php';
        }
      ?>
    </main>

    <?php require __DIR__ . '/components/auth-modals.php'; ?>
    <?php require __DIR__ . '/components/modal/index.php'; ?>

  </div>

  <script src="components/modal/confirm.js"></script>
  <!-- JS por página -->
  <?php if ($page === 'feed'): ?>
    <script src="js/feed.js"></script>
  <?php elseif ($page === 'perfil'): ?> 
   <!-- Sem JS específico para o perfil por enquanto -->  
  <?php elseif ($page === 'stats'): ?>
     <!-- Sem JS específico para estatistica por enquanto -->
  <?php elseif ($page === 'rankings'): ?>
    <script src="js/rankings.js"></script>
  <?php endif; ?>

  <!-- JS global do header (dropdown usuário) -->
  <script src="js/header.js"></script>

  <!-- JS global dos modais -->
  <script src="js/auth.js"></script>

  <!-- JS global da busca (precisa estar sempre disponível para a barra no header) -->
  <script src="js/search.js"></script>


  <link rel="stylesheet" href="ui/toast.css">
  <script src="ui/toast.js"></script>

  <!-- raiz dos toasts -->
  <div id="toast-root" aria-live="polite" aria-atomic="false"></div>

</body>
</html>
