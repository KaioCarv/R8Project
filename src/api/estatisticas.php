<?php


function api_stats_getResumo(mysqli $conn, int $userId): array
{
    $sql = "
        SELECT
          num_filmes_assistidos,
          num_series_assistidas,
          media_notas,
          total_curtidas_recebidas
        FROM usuariocomum
        WHERE id_usuario = ?
        LIMIT 1
    ";

    $dados = [
        'num_filmes_assistidos'    => 0,
        'num_series_assistidas'    => 0,
        'media_notas'              => 0,
        'total_curtidas_recebidas' => 0,
    ];

    if (!$stmt = $conn->prepare($sql)) {
        return $dados;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $dados = $row;
    }
    $stmt->close();

    return $dados;
}

function api_stats_getQtdAvaliacoes(mysqli $conn, int $userId): int
{
    $sql = "
        SELECT COUNT(*) AS total
        FROM avaliacao
        WHERE FK_UsuarioComum = ?
          AND status = 'Ativa'
    ";

    if (!$stmt = $conn->prepare($sql)) {
        return 0;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    return $row ? (int)$row['total'] : 0;
}
