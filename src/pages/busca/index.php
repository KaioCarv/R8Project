<?php

declare(strict_types=1);

require_once __DIR__ . '/../../redis_client.php';

$q = trim($_GET['q'] ?? '');
$qLower = mb_strtolower($q, 'UTF-8');

$redis = redisClient();


function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function renderStars(float $nota10): string {

    $nota5 = max(0.0, min(5.0, $nota10 / 2.0));
    $full  = (int) floor($nota5);
    $frac  = $nota5 - $full;
    $half  = ($frac >= 0.25 && $frac < 0.75) ? 1 : 0;
    if ($frac >= 0.75) { $full++; $half = 0; }
    $empty = 5 - $full - $half;

    $svgFull = '<svg width="18" height="18" viewBox="0 0 24 24" fill="#facc15"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    $svgHalf = '<svg width="18" height="18" viewBox="0 0 24 24"><defs><linearGradient id="g"><stop offset="50%" stop-color="#facc15"/><stop offset="50%" stop-color="transparent"/></linearGradient></defs><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="url(#g)" stroke="#facc15" stroke-width="0.5"/></svg>';
    $svgEmpty= '<svg width="18" height="18" viewBox="0 0 24 24"><path d="M22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24z" fill="none" stroke="#facc15" stroke-width="1.2"/></svg>';

    return '<span class="stars">' .
        str_repeat($svgFull, $full) .
        str_repeat($svgHalf, $half) .
        str_repeat($svgEmpty, $empty) .
        '</span>';
}


$allKeys = [];
$cursor = '0';
do {
    $resp = $redis->scan($cursor, ['MATCH' => 'obra:*', 'COUNT' => 500]);
    $cursor = isset($resp[0]) ? (string)$resp[0] : '0';
    foreach ($resp[1] ?? [] as $k) $allKeys[] = $k;
} while ($cursor !== '0');


$results = [];
if ($allKeys) {
    $rows = $redis->pipeline(function ($pipe) use ($allKeys) {
        foreach ($allKeys as $k) {
            $pipe->hmget($k, ['tipo','titulo','ano_lancamento','url_poster','sinopse','diretores','atores','nota_media','votos']);
        }
    });

    
    $DEFAULT_RANKING_ID = 1;

    foreach ($rows as $i => $fields) {
        if (!is_array($fields)) continue;

        $obraKey = $allKeys[$i]; 
        [$tipo,$titulo,$ano,$poster,$sinopse,$dirs,$ats,$nota,$votos] = $fields + [null,null,null,null,null,'[]','[]','0','0'];

        if ($qLower !== '' && mb_stripos((string)$titulo, $q, 0, 'UTF-8') === false) continue;

        $diretores = @json_decode((string)$dirs, true) ?: [];
        $atores    = @json_decode((string)$ats,  true) ?: [];

        $notaF = is_numeric($nota) ? (float)$nota : 0.0;

        if ($notaF <= 0) {
            $z = $redis->zscore("ranking:{$DEFAULT_RANKING_ID}:itens", $obraKey);
            if ($z !== null) $notaF = (float)$z; 
        }

        $votosI = (int)$votos;

        $results[] = [
            'titulo'  => (string)$titulo,
            'ano'     => (string)$ano,
            'poster'  => (string)$poster,
            'sinopse' => (string)$sinopse,
            'diretores' => $diretores,
            'atores'    => $atores,
            'nota10'    => $notaF,
            'nota5'     => round($notaF/2, 1),
            'votos'     => $votosI,
        ];
    }
}


usort($results, fn($a,$b)=>strcasecmp($a['titulo'],$b['titulo']));
?>
<section class="search-page">
  <h1 class="search-title">Resultados encontrados (<?= count($results) ?>)</h1>
  <?php if ($q !== ''): ?>
    <p class="search-subtitle">Consulta: <strong><?= e($q) ?></strong></p>
  <?php endif; ?>

  <?php if (!$results): ?>
    <div class="search-empty">Nenhum resultado encontrado.</div>
  <?php else: ?>
    <div class="results-list">
      <?php foreach ($results as $r): ?>
        <article class="result-card">
          <div class="result-poster">
            <?php if ($r['poster']): ?>
              <img src="<?= e($r['poster']) ?>" alt="<?= e($r['titulo']) ?>">
            <?php else: ?>
              <div class="poster-placeholder"></div>
            <?php endif; ?>
          </div>

          <div class="result-body">
            <h2 class="result-title"><?= e($r['titulo']) ?></h2>
            <div class="result-meta">
              <?php if ($r['ano']): ?><span><?= e($r['ano']) ?></span><?php endif; ?>
            </div>

            <?php if ($r['sinopse']): ?>
              <p class="result-overview"><?= e($r['sinopse']) ?></p>
            <?php endif; ?>

            <div class="result-rating">
              <?= renderStars($r['nota10']) ?>
              <span class="rating-number"><?= number_format($r['nota5'], 1, ',', '') ?></span>
              <?php if ($r['votos']): ?>
                <span class="rating-votes">(<?= number_format($r['votos'], 0, ',', '.') ?> votos)</span>
              <?php endif; ?>
            </div>

          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>