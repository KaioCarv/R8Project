<?php
declare(strict_types=1);

require __DIR__ . '/config/env.php';
require __DIR__ . '/redis_client.php';

$redis = redisClient();


$apiKey   = (string) env('TMDB_API_KEY', '');
if ($apiKey === '') {
    die("Defina TMDB_API_KEY no arquivo .env\n");
}

$baseUrl  = (string) env('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
$lang     = (string) env('TMDB_LANG', 'pt-BR');
$imgBase  = (string) env('TMDB_IMG_BASE', 'https://image.tmdb.org/t/p/w500');

$endpoint = "/tv/popular?api_key={$apiKey}&language={$lang}&page=1";
$url      = $baseUrl . $endpoint;

/** ===== Chamada à API ===== */
$json = @file_get_contents($url);
if ($json === false) {
    die("Erro ao chamar TMDB: {$url}\n");
}

$data = json_decode($json, true);
if (!isset($data['results']) || !is_array($data['results'])) {
    die("Resposta inesperada da TMDB\n");
}

$series = $data['results'];

$rankingId      = 1;
$rankingKeyHash = "ranking:{$rankingId}";
$rankingKeyZSet = "ranking:{$rankingId}:itens";

$redis->del([$rankingKeyHash, $rankingKeyZSet]);

$redis->hmset($rankingKeyHash, [
    'tipo'             => 'RankingSerie',
    'criterio'         => 'Mais bem avaliadas (TMDB)',
    'data_atualizacao' => date('c'),
]);


function montarHashObra(array $tv, string $imgBase): array
{
    $ano = '';
    if (!empty($tv['first_air_date'])) {
        $ano = substr($tv['first_air_date'], 0, 4);
    }

    $posterPath = $tv['poster_path'] ?? null;
    $posterUrl  = $posterPath ? $imgBase . $posterPath : '';

    $nota  = isset($tv['vote_average']) ? (float) $tv['vote_average'] : 0.0;
    $votos = isset($tv['vote_count'])   ? (int)   $tv['vote_count']   : 0;

    return [
        'tipo'           => 'Série',
        'titulo'         => $tv['name']        ?? '',
        'ano_lancamento' => $ano,
        'genero'         => '',
        'sinopse'        => $tv['overview']    ?? '',
        'url_poster'     => $posterUrl,
        'atores'         => json_encode([], JSON_UNESCAPED_UNICODE),
        'diretores'      => json_encode([], JSON_UNESCAPED_UNICODE),
        'num_temporadas' => 0,
        'num_episodios'  => 0,
        'nota_media'     => (string) $nota,
        'votos'          => (string) $votos,
    ];
}

$contador = 0;

foreach ($series as $tv) {
    if (!isset($tv['id'])) continue;

    $idTmdb  = (int) $tv['id'];
    $obraKey = "obra:{$idTmdb}";

    $obraHash = montarHashObra($tv, $imgBase);
    $redis->hmset($obraKey, $obraHash);

    
    $score = isset($tv['vote_average']) ? (float) $tv['vote_average'] : 0.0;
    $redis->zadd($rankingKeyZSet, [$obraKey => $score]);

    $contador++;
}

echo "Importadas {$contador} séries para o Redis.\n";
echo "Ranking salvo em '{$rankingKeyHash}' e '{$rankingKeyZSet}'.\n";
