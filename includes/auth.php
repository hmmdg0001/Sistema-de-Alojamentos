<?php
// includes/auth.php — Funções de autenticação

require_once __DIR__ . '/config.php';

function iniciarSessao(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function login(string $email, string $password): bool {
    $pdo = conectar();
    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        iniciarSessao();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_tipo'] = $user['tipo'];
        return true;
    }
    return false;
}

function logout(): void {
    iniciarSessao();
    session_destroy();
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit;
}

function utilizadorLogado(): bool {
    iniciarSessao();
    return isset($_SESSION['user_id']);
}

function exigirLogin(): void {
    if (!utilizadorLogado()) {
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit;
    }
}

function exigirGestor(): void {
    exigirLogin();
    iniciarSessao();
    if ($_SESSION['user_tipo'] !== 'gestor') {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

function utilizadorAtual(): array {
    iniciarSessao();
    if (!utilizadorLogado()) return [];
    $pdo = conectar();
    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: [];
}

function registar(string $nome, string $email, string $password, string $telefone, string $tipo = 'hospede'): bool {
    $pdo = conectar();
    // Verificar se email já existe
    $check = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) return false;

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, password_hash, telefone, tipo) VALUES (?,?,?,?,?)");
    return $stmt->execute([$nome, $email, $hash, $telefone, $tipo]);
}
