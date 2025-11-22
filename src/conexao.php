<?php
declare(strict_types=1);

require_once __DIR__ . '/config/env.php';

$host    = env('DB_HOST', 'localhost');
$port    = (int) env('DB_PORT', 3306);
$db      = env('DB_NAME', 'R8');
$user    = env('DB_USER', 'root');
$pass    = env('DB_PASS', '');
$charset = env('DB_CHARSET', 'utf8mb4');

$mysqli = @new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    error_log('Erro de conexão com o banco: ' . $mysqli->connect_error);
    http_response_code(500);
    die('Ocorreu um erro inesperado no servidor. Por favor, tente novamente mais tarde.');
}

$mysqli->set_charset($charset);
$conn = $mysqli;
