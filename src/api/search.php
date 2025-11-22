<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../redis_client.php';

$redis = redisClient();

// -----------------------------------------------------------------------------
// 1) Ler parâmetros
// -----------------------------------------------------------------------------
$q = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));
if ($q === '') {
    echo json_encode([
        'status'  => 'empty',
        'query'   => '',
        'count'   => 0,
        'results' => [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$qLower = mb_strtolower($q, 'UTF-8');
$limit  = isset($_GET['n']) ? max(1, (int)$_GET['n']) : 10;

// cache da resposta final pra ESSE termo+limite
$queryCacheKey = 'search_cache:q:' . md5($qLower . '|' . $limit);
// cache com TODAS as obras já montadas
$allCacheKey   = 'search_cache:all_obras';
// TTLs
$queryTtl = 300; // 5 min
$allTtl   = 300; // 5 min

// -----------------------------------------------------------------------------
// 2) Tentar pegar resposta pronta desse termo (q + limit)
// -----------------------------------------------------------------------------
try {
    $cachedJson = $redis->get($queryCacheKey);
} catch (\Throwable $e) {
    $cachedJson = null;
}

if (is_string($cachedJson) && $cachedJson !== '') {
    echo $cachedJson;
    exit;
}

// -----------------------------------------------------------------------------
// 3) Tentar pegar lista completa de obras do cache (all_obras)
//    Se não tiver, monta UMA vez com SCAN + pipeline
// -----------------------------------------------------------------------------
try {
    $allJson = $redis->get($allCacheKey);
} catch (\Throwable $e) {
    $allJson = null;
}

$allObras = [];

if (is_string($allJson) && $allJson !== '') {
    $decoded = json_decode($allJson, true);
    if (is_array($decoded)) {
        $allObras = $decoded;
    }
}

if (!$allObras) {
    // Não tinha cache de todas as obras -> caminho pesado, mas raro
    $keys   = [];
    $cursor = '0';

    do {
        $resp   = $redis->scan($cursor, ['MATCH' => 'obra:*', 'COUNT' => 500]);
        $cursor = isset($resp[0]) ? (string)$resp[0] : '0';
        $found  = $resp[1] ?? [];

        if ($found) {
            foreach ($found as $k) {
                $keys[] = $k;
            }
        }
    } while ($cursor !== '0');

    $rows = [];
    if ($keys) {
        $rows = $redis->pipeline(function ($pipe) use ($keys) {
            foreach ($keys as $k) {
                $pipe->hmget($k, [
                    'titulo',
                    'ano_lancamento',
                    'url_poster',
                    'sinopse',
                    'nota_media',
                    'votos'
                ]);
            }
        });
    }

    $allObras = [];

    foreach ($rows as $i => $fields) {
        if (!is_array($fields)) {
            continue;
        }

        [$titulo, $ano, $poster, $sinopse, $nota, $votos] = $fields + [null, null, null, null, '0', '0'];

        if (!$titulo) {
            continue;
        }

        $key = $keys[$i] ?? '';
        $pos = strpos($key, ':');
        $id  = $pos !== false ? (int)substr($key, $pos + 1) : 0;

        $nota10 = (float)$nota;

        $allObras[] = [
            'id'        => $id,
            'title'     => (string)$titulo,
            'year'      => (string)$ano,
            'poster'    => (string)$poster,
            'overview'  => (string)$sinopse,
            'rating10'  => $nota10,
            'rating5'   => round($nota10 / 2, 1),
            'votes'     => (int)$votos,
        ];
    }

    // guarda lista completa em cache pra evitar novos SCAN
    try {
        $redis->setex(
            $allCacheKey,
            $allTtl,
            json_encode($allObras, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    } catch (\Throwable $e) {
        // se falhar o cache, segue a vida
    }
}

// -----------------------------------------------------------------------------
// 4) Filtrar em memória pelo termo e aplicar limite
// -----------------------------------------------------------------------------
$results = [];

foreach ($allObras as $obra) {
    $title = (string)($obra['title'] ?? '');
    if ($title === '') {
        continue;
    }

    if (mb_stripos($title, $q, 0, 'UTF-8') === false) {
        continue;
    }

    $results[] = $obra;
}

usort($results, fn($a, $b) => strcasecmp($a['title'], $b['title']));
$results = array_slice($results, 0, $limit);

// -----------------------------------------------------------------------------
// 5) Montar payload final + gravar no cache por termo + responder
// -----------------------------------------------------------------------------
$payload = [
    'status'  => 'ok',
    'query'   => $q,
    'count'   => count($results),
    'results' => $results,
];

$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

try {
    $redis->setex($queryCacheKey, $queryTtl, $json);
} catch (\Throwable $e) {
    // ignora erro de cache
}

echo $json;
