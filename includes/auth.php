<?php
 # Antes de começar o programa necessita o config.php
require_once __DIR__ . '/config.php';

 # Inicia a sessão PHP se ainda não estiver ativa (evita warnings). */
function iniciarSessao(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); 
    }
}

/**
 * Valida email + password na BD e, se corretos, guarda dados na sessão.
 * A password na BD está em hash (password_hash); password_verify compara.
 */
function login(string $email, string $password): bool { # Função que vai validar o login do utilizador
    $pdo = conectar();
    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) { #Função que vai verificar a password
        iniciarSessao();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_tipo'] = $user['tipo'];
        return true;
    }
    return false;
}

function logout(): void { # Função de logout
    iniciarSessao();
    session_destroy();
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit; 
}

function utilizadorautenticado(): bool { # Se o utilizador estiver autenticado fica TRUE
    iniciarSessao();
    return isset($_SESSION['user_id']);
}

function exigirLogin(): void {  # Se o utulizador nao estiver autenticado manda o utilizador para a pagina login.php
    if (!utilizadorautenticado()) {
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit;
    }
}

function exigirGestor(): void { # Função que gestores so podem aceder, se o utilizador for algo diferente (hospede) manda-o devolta para a pagina inicial
    exigirLogin();
    iniciarSessao();
    if ($_SESSION['user_tipo'] !== 'gestor') {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

function utilizadorAtual(): array { # Verifica o utilizador atual e devolve em um array
    iniciarSessao();
    if (!utilizadorautenticado()) return [];
    $pdo = conectar();
    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: [];
}


 # Função que vai registar um utilizador novo na base de dados
function registar(string $nome, string $email, string $password, string $telefone, string $tipo = 'hospede'): bool {
    $pdo = conectar();

    # Impedir emails duplicados
    $check = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) return false;

    # Encripta a password para mais segurança
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, password_hash, telefone, tipo) VALUES (?,?,?,?,?)");
    return $stmt->execute([$nome, $email, $hash, $telefone, $tipo]);
}
