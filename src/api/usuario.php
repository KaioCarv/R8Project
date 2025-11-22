<?php

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../conexao.php'; 

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode(['status' => 'error', 'message' => 'Sem conexão com o banco']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    login($conn);
} elseif ($action === 'register') {
    register($conn);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ação inválida']);
}



function login(mysqli $conn): void
{
    $login = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($login === '' || $senha === '') {
        echo json_encode(['status' => 'error', 'message' => 'Preencha usuário e senha']);
        return;
    }

    
    $sql = "SELECT id, nome, email, hash_senha
            FROM usuario
            WHERE email = ? OR nome = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status'  => 'not_found',
            'message' => 'Usuário não encontrado. Faça seu cadastro.'
        ]);
        return;
    }

    $user = $result->fetch_assoc();

    if (!password_verify($senha, $user['hash_senha'])) {
        echo json_encode([
            'status'  => 'invalid_password',
            'message' => 'Senha incorreta.'
        ]);
        return;
    }

    session_start();
    $_SESSION['user_id']    = (int)$user['id'];  
    $_SESSION['user_nome']  = $user['nome'];
    $_SESSION['user_email'] = $user['email'];

    echo json_encode(['status' => 'ok', 'message' => 'Login realizado com sucesso']);
}



function register(mysqli $conn): void
{
    $email         = trim($_POST['email'] ?? '');
    $nome          = trim($_POST['usuario'] ?? '');
    $senha         = $_POST['senha'] ?? '';
    $senha_confirm = $_POST['senha_confirm'] ?? '';

    if ($email === '' || $nome === '' || $senha === '' || $senha_confirm === '') {
        echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'E-mail inválido']);
        return;
    }

    if ($senha !== $senha_confirm) {
        echo json_encode(['status' => 'password_mismatch', 'message' => 'As senhas não conferem']);
        return;
    }


    $sqlCheck = "SELECT id FROM usuario WHERE email = ? OR nome = ? LIMIT 1";
    $stmt = $conn->prepare($sqlCheck);
    $stmt->bind_param('ss', $email, $nome);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'status'  => 'exists',
            'message' => 'Usuário ou e-mail já cadastrado'
        ]);
        return;
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $sqlInsert = "INSERT INTO usuario (nome, email, hash_senha)
                  VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param('sss', $nome, $email, $hash);

    if (!$stmt->execute()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Erro ao criar usuário'
        ]);
        return;
    }

    $novoIdUsuario = (int)$stmt->insert_id;
    $stmt->close();

    $sqlComum = "
      INSERT INTO usuariocomum (
        id_usuario,
        nome_usuario,
        biografia,
        status,
        num_filmes_assistidos,
        num_series_assistidas,
        genero_favorito,
        media_notas,
        total_curtidas_recebidas,
        visibilidade_perfil,
        FK_Administrador
      )
      VALUES (?, ?, '', 'Ativo', 0, 0, '', 0, 0, 'Público', 1)
    ";

    $stmt2 = $conn->prepare($sqlComum);
    $stmt2->bind_param('is', $novoIdUsuario, $nome);

    if (!$stmt2->execute()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Usuário criado, mas houve erro ao vincular como usuário comum.'
        ]);
        return;
    }
    $stmt2->close();

    echo json_encode([
        'status'  => 'ok',
        'message' => 'Cadastro realizado com sucesso. Já pode fazer login!'
    ]);
}
