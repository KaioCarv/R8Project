<?php

require_once __DIR__ . '/../../redis_client.php';

$redis = redisClient();


$rankingId      = 1;
$rankingKeyZSet = "ranking:{$rankingId}:itens";


$obraKeys = $redis->zRevRange($rankingKeyZSet, 0, -1);

$filmes = [];

foreach ($obraKeys as $obraKey) {

    $dados = $redis->hGetAll($obraKey);
    if (!$dados || empty($dados['url_poster'])) {
        continue;
    }

    $filmes[] = [
        'titulo'     => $dados['titulo']     ?? '',
        'sinopse'    => $dados['sinopse']    ?? '',
        'url_poster' => $dados['url_poster'] ?? '',
    ];
}


$filmesComPoster = array_values($filmes);


$top10 = $filmesComPoster;
shuffle($top10);
$top10 = array_slice($top10, 0, 10);
?>
<section class="rankings-page">

  <section class="rankings-section">
    <h1 class="rankings-title">Filmes populares dessa semana</h1>

    <div class="rankings-carousel">
      <button
        type="button"
        class="rankings-arrow rankings-arrow--left"
        data-carousel-arrow="week-left"
        aria-label="Voltar">
        ‹
      </button>

      <div
        class="rankings-row rankings-row--large"
        data-carousel="week">
        <?php if (!empty($filmesComPoster)): ?>
          <?php foreach ($filmesComPoster as $filme): ?>
            <?php
              $posterUrl = $filme['url_poster'];
            ?>
            <article
              class="poster-card poster-card--large"
              style="background-image: url('<?= htmlspecialchars($posterUrl) ?>');">
            </article>
          <?php endforeach; ?>
        <?php else: ?>
        
          <article class="poster-card poster-card--large poster-card--p1"></article>
          <article class="poster-card poster-card--large poster-card--p2"></article>
          <article class="poster-card poster-card--large poster-card--p3"></article>
          <article class="poster-card poster-card--large poster-card--p4"></article>
          <article class="poster-card poster-card--large poster-card--p5"></article>
          <article class="poster-card poster-card--large poster-card--p6"></article>
        <?php endif; ?>
      </div>

      <button
        type="button"
        class="rankings-arrow rankings-arrow--right"
        data-carousel-arrow="week-right"
        aria-label="Avançar">
        ›
      </button>
    </div>
  </section>

  <hr class="rankings-divider" />

 
  <section class="rankings-section">
    <h2 class="rankings-title rankings-title--small">Top 10</h2>

    <div class="rankings-row rankings-row--small">
      <?php if (!empty($top10)): ?>
        <?php foreach ($top10 as $filme): ?>
          <?php
            $posterUrl = $filme['url_poster'];
          ?>
          <article
            class="poster-card poster-card--small"
            style="background-image: url('<?= htmlspecialchars($posterUrl) ?>');">
          </article>
        <?php endforeach; ?>
      <?php else: ?>
    
        <article class="poster-card poster-card--small poster-card--t1"></article>
        <article class="poster-card poster-card--small poster-card--t2"></article>
        <article class="poster-card poster-card--small poster-card--t3"></article>
        <article class="poster-card poster-card--small poster-card--t4"></article>
        <article class="poster-card poster-card--small poster-card--t5"></article>
        <article class="poster-card poster-card--small poster-card--t6"></article>
        <article class="poster-card poster-card--small poster-card--t7"></article>
        <article class="poster-card poster-card--small poster-card--t8"></article>
        <article class="poster-card poster-card--small poster-card--t9"></article>
        <article class="poster-card poster-card--small poster-card--t10"></article>
      <?php endif; ?>
    </div>
  </section>

</section>
