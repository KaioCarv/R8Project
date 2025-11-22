<?php
declare(strict_types=1);

/**
 * Pequeno loader de variáveis .env
 * - Se "vlucas/phpdotenv" estiver instalado, usa ele.
 * - Senão, faz um parse simples do arquivo .env.
 */

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $val = getenv($key);
        return $val !== false ? $val : $default;
    }
}

(function () {
    // Evita carregar duas vezes
    if (getenv('__ENV_LOADED__')) return;

    // Procura .env na raiz do projeto (../.env) ou em /src/.env
    $base = dirname(__DIR__, 1);              // .../src
    $p1   = $base . '/../.env';               // raiz
    $p2   = $base . '/.env';                   // dentro de src
    $envPath = file_exists($p1) ? $p1 : (file_exists($p2) ? $p2 : null);

    // Tenta usar Dotenv se existir
    if ($envPath && class_exists(\Dotenv\Dotenv::class)) {
        $dir = dirname($envPath);
        \Dotenv\Dotenv::createImmutable($dir)->safeLoad();
    } elseif ($envPath) {
        // Parser simples
        $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            $v = trim($v, "\"'");
            putenv("$k=$v");
            $_ENV[$k]    = $v;
            $_SERVER[$k] = $v;
        }
    }

    putenv('__ENV_LOADED__=1');
})();
