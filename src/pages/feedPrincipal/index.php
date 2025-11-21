<?php

require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../api/avaliacao.php';

$avaliacoes   = api_listarFeed($conn);
$loggedUserId = $_SESSION['user_id'] ?? null;

function r8_renderStars(float $nota): string
{
    $html   = '';
    $cheias = floor($nota);
    $resto  = $nota - $cheias;

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $cheias) {
            $html .= '<span class="star">★</span>';
        } elseif ($resto >= 0.5 && $i === $cheias + 1) {
            $html .= '<span class="star">☆</span>';
        } else {
            $html .= '<span class="star star--empty">★</span>';
        }
    }

    return $html;
}
?>

<section class="feed-page">


  <?php foreach ($avaliacoes as $av): ?>
    <?php
      $avaliacaoId    = (int)$av['avaliacao_id'];
      $nota           = (float)$av['nota'];
      $notaFmt        = number_format($nota, 1, ',', '.');
      $dataCriacaoFmt = date('d/m/Y H:i', strtotime($av['data_criacao']));
      $autorNome      = $av['nome_usuario'] ?: $av['nome_real'];
      $autorInicial   = strtoupper(substr($autorNome, 0, 1));
    ?>

    <article class="feed-card" data-avaliacao-id="<?= $avaliacaoId ?>">
      <header class="feed-card-header">
        <div class="feed-user">
          <div class="feed-user-avatar">
            <?= htmlspecialchars($autorInicial) ?>
          </div>
          <div>
            <div class="feed-user-name"><?= htmlspecialchars($autorNome) ?></div>
            <div class="feed-user-sub">Usuário #<?= (int)$av['usuario_id'] ?></div>
          </div>
        </div>

        <div class="feed-meta">
          <span class="feed-date"><?= $dataCriacaoFmt ?></span>
          <span class="feed-score"><?= $notaFmt ?> / 5</span>
        </div>
      </header>

      <div class="feed-card-body">
        <div class="feed-left">
          <div class="feed-review-header">
            <div class="feed-review-badge">
              <span>AVALIAÇÃO</span>
              <span>FILME</span>
            </div>
            <div class="feed-review-title">Avaliação #<?= $avaliacaoId ?></div>
            <div class="feed-review-stars">
              <?= r8_renderStars($nota); ?>
            </div>
          </div>

          <textarea class="feed-review-text" readonly><?= htmlspecialchars($av['avaliacao_texto']) ?></textarea>

          <div class="feed-comments">
            <?php foreach ($av['comentarios'] as $comentario): ?>
              <?php
                $isOwner = $loggedUserId &&
                           (int)$comentario['FK_UsuarioComum'] === (int)$loggedUserId;
                $nomeComent = $comentario['nome_usuario'] ?: $comentario['nome_real'];
              ?>
              <div class="comment-row" data-comentario-id="<?= (int)$comentario['id'] ?>">
                <div class="comment-main">
                  <span class="comment-author"><?= htmlspecialchars($nomeComent) ?></span>
                  <span class="comment-text"><?= htmlspecialchars($comentario['texto']) ?></span>
                </div>
                <div class="comment-actions">
                  <button type="button"
                          class="comment-like-btn"
                          data-like-comentario="<?= (int)$comentario['id'] ?>">
                    <span class="comment-like-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart">
                        <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
                      </svg>
                    </span>
                    <span class="comment-like-count"><?= (int)$comentario['num_curtidas'] ?></span>
                  </button>

                  <?php if ($isOwner): ?>
                    <button type="button"
                            class="comment-icon-btn"
                            data-action="editar-comentario"
                            data-comentario-id="<?= (int)$comentario['id'] ?>"
                            title="Editar">
                      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil">
                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                        <path d="m15 5 4 4"/>
                      </svg>
                    </button>
                    <button type="button"
                            class="comment-icon-btn"
                            data-action="excluir-comentario"
                            data-comentario-id="<?= (int)$comentario['id'] ?>"
                            title="Excluir">
                      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-icon lucide-trash">
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                        <path d="M3 6h18"/>
                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                      </svg>
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>

            <form class="feed-comment-form" data-avaliacao-id="<?= $avaliacaoId ?>">
              <input
                type="text"
                class="feed-comment-input"
                name="texto"
                placeholder="Escreva um comentário..." />
              <button type="submit" class="feed-comment-submit">Publicar</button>
            </form>
          </div>
        </div>

        <aside class="feed-right">
          <button type="button"
                  class="feed-like-button"
                  data-like-avaliacao="<?= $avaliacaoId ?>">
            <span class="feed-like-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart">
                <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
              </svg>
            </span>
            <span>Gostei</span>
            <span class="feed-like-count"><?= (int)$av['curtidas_avaliacao'] ?></span>
          </button>

          <button type="button" class="feed-comment-toggle">
            <span class="feed-comment-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-icon lucide-message-circle">
                <path d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719"/>
              </svg>
            </span>
            <span>Comentar</span>
          </button>
        </aside>
      </div>
    </article>
  <?php endforeach; ?>
</section>
