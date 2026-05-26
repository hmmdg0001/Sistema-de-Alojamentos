<?php
# Antes de começar o programa necessita do config.php e auth.php
require_once '../includes/config.php';
require_once '../includes/auth.php';

iniciarSessao(); # Verifica se o utilizar já está autenticado, se o mesmo tiver manda-o para a pagina principal " index.php"
if (utilizadorautenticado()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolhe e limpa os dados do formulário
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');

    // Validações antes de tocar na base de dados
    if ($password !== $confirm) {
        $erro = 'As passwords não coincidem.';
    } elseif (strlen($password) < 6) {
        $erro = 'A password deve ter pelo menos 6 caracteres.';
    } elseif (registar($nome, $email, $password, $telefone)) {
        $sucesso = 'Conta criada com sucesso! Podes agora <a href="login.php" style="color:var(--accent)">entrar</a>.';
    } else { # Se o email já está registado ele devolve a mensagem
        $erro = 'Este email já está registado.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlojamentosOnline — Registar</title>
    <meta name="color-scheme" content="light">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= CSS_URL ?>">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">
        <h2>Criar Conta</h2>
        <p>Regista-te para reservar alojamentos</p>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= $sucesso ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nome completo</label>
                <input type="text" name="nome" required placeholder="João Silva" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="email@exemplo.pt" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="telefone" placeholder="9XXXXXXXX" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Confirmar</label>
                    <input type="password" name="confirm" required placeholder="••••••••">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">Criar Conta</button>
        </form>

        <p style="text-align:center;margin-top:1.2rem;color:var(--muted);font-size:.9rem">
            Já tens conta? <a href="login.php" style="color:var(--accent)">Entrar</a>
        </p>
    </div>
</div>
</body>
</html>
