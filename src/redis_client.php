<?php
declare(strict_types=1);

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Predis\Client;

/** Retorna um cliente Predis conectado ao Redis. */
function redisClient(): Client
{
    static $client = null;
    if ($client instanceof Client) {
        return $client;
    }

    $params = [
        'scheme' => 'tcp',
        'host'   => env('REDIS_HOST', '127.0.0.1'),
        'port'   => (int) env('REDIS_PORT', 6379),
    ];

    $pwd = env('REDIS_PASSWORD', '');
    if ($pwd !== '') $params['password'] = $pwd;

    $db = env('REDIS_DB', '');
    if ($db !== '' && is_numeric($db)) $params['database'] = (int) $db;

    $client = new Client($params);
    return $client;
}
