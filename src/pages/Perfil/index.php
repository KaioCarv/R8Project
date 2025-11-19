<?php


require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../api/perfil.php';


if (!isset($_SESSION['user_id'])) {
    ?>
    <section class="profile-page">
      <p>Você precisa estar logado para ver o perfil.</p>
    </section>
    <?php
    return;
}

$userId = (int)$_SESSION['user_id'];


$perfil = api_perfil_getPerfil($conn, $userId);

if (!$perfil) {
    ?>
    <section class="profile-page">
      <p>Não foi possível carregar os dados do perfil.</p>
    </section>
    <?php
    return;
}


$displayName = !empty($perfil['nome_usuario'])
    ? $perfil['nome_usuario']
    : $perfil['nome'];

$bio = $perfil['biografia'] ?: 'Adicione uma biografia para contar mais sobre você.';
$avatarLetter = strtoupper(substr($displayName, 0, 1)); 

$avaliacoes = api_perfil_getAvaliacoes($conn, $userId, 5);


function profile_render_stars(float $nota): string
{
    $html   = '';
    $cheias = floor($nota);
    $resto  = $nota - $cheias;

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $cheias) {
            $html .= '<span>★</span>';
        } elseif ($resto >= 0.5 && $i === $cheias + 1) {
            $html .= '<span>☆</span>';
        } else {
            $html .= '<span>★</span>';  
        }
    }

    return $html;
}
?>

<section class="profile-page">
 
  <div class="profile-tabs">
    <a href="?page=perfil"
       class="profile-tab <?= $page === 'perfil' ? 'profile-tab--active' : '' ?>"
       aria-label="Perfil">
      <svg xmlns="http://www.w3.org/2000/svg"
           viewBox="0 0 24 24"
           class="profile-tab-icon"
           aria-hidden="true">
        <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round" />
      </svg>
    </a>

    <a href="?page=stats"
       class="profile-tab <?= $page === 'stats' ? 'profile-tab--active' : '' ?>"
       aria-label="Estatísticas pessoais">
      <svg xmlns="http://www.w3.org/2000/svg"
           viewBox="0 0 24 24"
           class="profile-tab-icon"
           aria-hidden="true">
        <path d="M3 3v16a2 2 0 0 0 2 2h16"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round" />
        <path d="m19 9-5 5-4-4-3 3"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round" />
      </svg>
    </a>
  </div>

  <section class="profile-card">
    <div class="profile-header">
      <div class="profile-avatar">
        <?= htmlspecialchars($avatarLetter) ?>
      </div>

      <div class="profile-info">
        <h1 class="profile-name">
          <?= htmlspecialchars($displayName) ?>
        </h1>
        <p class="profile-bio">
          <?= nl2br(htmlspecialchars($bio)) ?>
        </p>
      </div>

      <button class="profile-edit-btn">Editar</button>
    </div>
  </section>

  <section class="profile-section">
    <header class="profile-section-header">
      <h2>Minhas Listas</h2>
    </header>

    <div class="profile-lists-row">
      <article class="profile-list-card">
        <div class="profile-list-posters profile-list-posters--assistidos"></div>
        <div class="profile-list-title">Assistidos</div>
      </article>

      <article class="profile-list-card">
        <div class="profile-list-posters profile-list-posters--quero"></div>
        <div class="profile-list-title">Quero Assistir</div>
      </article>

      <article class="profile-list-card">
        <div class="profile-list-posters profile-list-posters--ficcao"></div>
        <div class="profile-list-title">Ficções Científicas</div>
      </article>
    </div>
  </section>


  <section class="profile-section">
    <header class="profile-section-header">
      <h2>Minhas Avaliações</h2>
    </header>

    <?php if (empty($avaliacoes)): ?>
      <p class='NoAvaliacoes'>Você ainda não publicou nenhuma avaliação.</p>
    <?php else: ?>
      <?php foreach ($avaliacoes as $av): ?>
        <?php
          $dataCriacao = date('d/m/Y', strtotime($av['data_criacao']));
          $notaFmt     = number_format((float)$av['nota'], 1, ',', '.');
        ?>
        <article class="profile-review-card">
          <div class="profile-review-poster"></div>

          <div class="profile-review-content">
            <div class="profile-review-top">
              <div class="profile-review-title-wrap">
                <h3 class="profile-review-title">
                  Avaliação #<?= (int)$av['id'] ?>
                </h3>
                <div class="profile-review-stars">
                  <?= profile_render_stars((float)$av['nota']); ?>
                </div>
              </div>

              <div class="profile-review-meta">
                <span><?= $dataCriacao ?></span>
                <span style="margin-left:8px;"><?= $notaFmt ?> / 5</span>
              </div>
            </div>

            <p class="profile-review-text">
              <?= nl2br(htmlspecialchars($av['comentario'])) ?>
            </p>
          </div>

          <button class="profile-review-menu" aria-label="Mais opções">⋮</button>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</section>
