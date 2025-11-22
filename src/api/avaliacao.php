<?php

require_once __DIR__ . '/../conexao.php';


function api_listarFeed(mysqli $conn): array
{
    $avaliacoes = [];

    $sql = "
        SELECT
          a.id              AS avaliacao_id,
          a.nota,
          a.comentario      AS avaliacao_texto,
          a.data_criacao,
          a.num_curtidas    AS curtidas_avaliacao,
          a.FK_UsuarioComum AS usuario_id,
          u.nome            AS nome_real,
          uc.nome_usuario
        FROM avaliacao a
        JOIN usuariocomum uc ON uc.id_usuario = a.FK_UsuarioComum
        JOIN usuario      u  ON u.id = uc.id_usuario
        WHERE a.status = 'Ativa'
        ORDER BY a.data_criacao DESC
    ";

    $res = $conn->query($sql);
    if (!$res) {
        return [];
    }

    while ($row = $res->fetch_assoc()) {
        $avaliacaoId = (int)$row['avaliacao_id'];

        
        $comentarios = [];
        $sqlC = "
            SELECT
              c.id,
              c.texto,
              c.data_criacao,
              c.num_curtidas,
              c.FK_UsuarioComum,
              u2.nome           AS nome_real,
              uc2.nome_usuario  AS nome_usuario
            FROM comentario c
            JOIN usuariocomum uc2 ON uc2.id_usuario = c.FK_UsuarioComum
            JOIN usuario      u2  ON u2.id = uc2.id_usuario
            WHERE c.FK_Avaliacao = ?
            ORDER BY c.data_criacao ASC
        ";
        if ($stmtC = $conn->prepare($sqlC)) {
            $stmtC->bind_param('i', $avaliacaoId);
            $stmtC->execute();
            $resC = $stmtC->get_result();
            while ($c = $resC->fetch_assoc()) {
                $comentarios[] = $c;
            }
            $stmtC->close();
        }

        $row['comentarios'] = $comentarios;
        $avaliacoes[] = $row;
    }

    return $avaliacoes;
}

function api_criarComentario(mysqli $conn, int $avaliacaoId, int $userId, string $texto): array
{
    $texto = trim($texto);
    if ($texto === '') {
        return ['status' => 'empty'];
    }

    $sql = "
        INSERT INTO comentario (texto, data_criacao, num_curtidas, FK_UsuarioComum, FK_Avaliacao)
        VALUES (?, NOW(), 0, ?, ?)
    ";

    if (!$stmt = $conn->prepare($sql)) {
        return ['status' => 'error', 'message' => $conn->error];
    }

    $stmt->bind_param('sii', $texto, $userId, $avaliacaoId);
    $ok = $stmt->execute();
    $comentarioId = $stmt->insert_id;
    $stmt->close();

    if (!$ok) {
        return ['status' => 'error'];
    }

    // Retorna comentário completo
    $sqlSel = "
        SELECT
          c.id,
          c.texto,
          c.data_criacao,
          c.num_curtidas,
          c.FK_UsuarioComum,
          u.nome,
          uc.nome_usuario
        FROM comentario c
        JOIN usuariocomum uc ON uc.id_usuario = c.FK_UsuarioComum
        JOIN usuario      u  ON u.id = uc.id_usuario
        WHERE c.id = ?
        LIMIT 1
    ";

    if (!$stmt2 = $conn->prepare($sqlSel)) {
        return ['status' => 'ok'];
    }

    $stmt2->bind_param('i', $comentarioId);
    $stmt2->execute();
    $res = $stmt2->get_result();
    $row = $res->fetch_assoc();
    $stmt2->close();

    return [
        'status'     => 'ok',
        'comentario' => $row,
    ];
}

function api_curtirAvaliacao(mysqli $conn, int $avaliacaoId): array
{
    $sql = "UPDATE avaliacao SET num_curtidas = num_curtidas + 1 WHERE id = ?";
    if (!$stmt = $conn->prepare($sql)) {
        return ['status' => 'error'];
    }

    $stmt->bind_param('i', $avaliacaoId);
    $stmt->execute();
    $stmt->close();

    $res = $conn->query("SELECT num_curtidas FROM avaliacao WHERE id = " . (int)$avaliacaoId);
    $row = $res ? $res->fetch_assoc() : null;

    return [
        'status'   => 'ok',
        'curtidas' => $row ? (int)$row['num_curtidas'] : 0,
    ];
}

function api_curtirComentario(mysqli $conn, int $comentarioId): array
{
    $sql = "UPDATE comentario SET num_curtidas = num_curtidas + 1 WHERE id = ?";
    if (!$stmt = $conn->prepare($sql)) {
        return ['status' => 'error'];
    }

    $stmt->bind_param('i', $comentarioId);
    $stmt->execute();
    $stmt->close();

    $res = $conn->query("SELECT num_curtidas FROM comentario WHERE id = " . (int)$comentarioId);
    $row = $res ? $res->fetch_assoc() : null;

    return [
        'status'   => 'ok',
        'curtidas' => $row ? (int)$row['num_curtidas'] : 0,
    ];
}


function api_editarComentario(mysqli $conn, int $comentarioId, int $userId, string $texto): array
{
    $texto = trim($texto);
    if ($texto === '') {
        return ['status' => 'empty'];
    }

    $sql = "
        UPDATE comentario
        SET texto = ?, data_criacao = NOW()
        WHERE id = ? AND FK_UsuarioComum = ?
    ";

    if (!$stmt = $conn->prepare($sql)) {
        return ['status' => 'error', 'message' => $conn->error];
    }

    $stmt->bind_param('sii', $texto, $comentarioId, $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        return ['status' => 'forbidden'];
    }

    return ['status' => 'ok'];
}


function api_excluirComentario(mysqli $conn, int $comentarioId, int $userId): array
{
    $sql = "DELETE FROM comentario WHERE id = ? AND FK_UsuarioComum = ?";

    if (!$stmt = $conn->prepare($sql)) {
        return ['status' => 'error', 'message' => $conn->error];
    }

    $stmt->bind_param('ii', $comentarioId, $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        return ['status' => 'forbidden'];
    }

    return ['status' => 'ok'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'] ?? '';

    if ($action === 'criar_comentario') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'not_logged']);
            exit;
        }
        $avaliacaoId = (int)($_POST['avaliacao_id'] ?? 0);
        $texto       = $_POST['texto'] ?? '';
        $userId      = (int)$_SESSION['user_id'];

        echo json_encode(api_criarComentario($conn, $avaliacaoId, $userId, $texto));
        exit;
    }

    if ($action === 'curtir_avaliacao') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'not_logged']);
            exit;
        }

        $avaliacaoId = (int)($_POST['avaliacao_id'] ?? 0);
        $userId      = (int)$_SESSION['user_id'];

        if (!isset($_SESSION['liked_avaliacoes'])) {
            $_SESSION['liked_avaliacoes'] = [];
        }

        if (in_array($avaliacaoId, $_SESSION['liked_avaliacoes'], true)) {
            echo json_encode([
                'status' => 'already_liked'
            ]);
            exit;
        }

        $resp = api_curtirAvaliacao($conn, $avaliacaoId);
        $resp['liked'] = true;

        $_SESSION['liked_avaliacoes'][] = $avaliacaoId;

        echo json_encode($resp);
        exit;
    }

    if ($action === 'curtir_comentario') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'not_logged']);
            exit;
        }

        $comentarioId = (int)($_POST['comentario_id'] ?? 0);
        $userId       = (int)$_SESSION['user_id'];

        
        if (!isset($_SESSION['liked_comentarios'])) {
            $_SESSION['liked_comentarios'] = [];
        }

        if (in_array($comentarioId, $_SESSION['liked_comentarios'], true)) {
            echo json_encode([
                'status' => 'already_liked'
            ]);
            exit;
        }

        $resp = api_curtirComentario($conn, $comentarioId);
        $resp['liked'] = true;

        $_SESSION['liked_comentarios'][] = $comentarioId;

        echo json_encode($resp);
        exit;
    }

    if ($action === 'editar_comentario') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'not_logged']);
            exit;
        }
        $comentarioId = (int)($_POST['comentario_id'] ?? 0);
        $texto        = $_POST['texto'] ?? '';
        $userId       = (int)$_SESSION['user_id'];

        echo json_encode(api_editarComentario($conn, $comentarioId, $userId, $texto));
        exit;
    }

    if ($action === 'excluir_comentario') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'not_logged']);
            exit;
        }
        $comentarioId = (int)($_POST['comentario_id'] ?? 0);
        $userId       = (int)$_SESSION['user_id'];

        echo json_encode(api_excluirComentario($conn, $comentarioId, $userId));
        exit;
    }

    echo json_encode(['status' => 'unknown_action']);
    exit;
}
