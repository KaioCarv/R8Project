<?php

function api_perfil_getPerfil(mysqli $conn, int $userId): ?array
{
    $sql = "
        SELECT
          u.id,
          u.nome,
          u.email,
          uc.nome_usuario,
          uc.biografia,
          uc.status,
          uc.num_filmes_assistidos,
          uc.num_series_assistidas,
          uc.genero_favorito,
          uc.media_notas,
          uc.total_curtidas_recebidas,
          uc.visibilidade_perfil
        FROM usuario u
        JOIN usuariocomum uc
          ON uc.id_usuario = u.id
        WHERE u.id = ?
        LIMIT 1
    ";

    if (!$stmt = $conn->prepare($sql)) {
        return null;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

function api_perfil_getAvaliacoes(mysqli $conn, int $userId, int $limit = 5): array
{
    $avaliacoes = [];

    $sql = "
        SELECT
          a.id,
          a.nota,
          a.comentario,
          a.data_criacao,
          a.num_curtidas
        FROM avaliacao a
        WHERE a.FK_UsuarioComum = ?
          AND a.status = 'Ativa'
        ORDER BY a.data_criacao DESC
        LIMIT ?
    ";

    if (!$stmt = $conn->prepare($sql)) {
        return $avaliacoes;
    }

    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $avaliacoes[] = $row;
    }

    $stmt->close();

    return $avaliacoes;
}
