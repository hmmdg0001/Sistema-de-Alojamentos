<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

iniciarSessao();
if (utilizadorLogado()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$erro = '';
$sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');

    if ($password !== $confirm) {
        $erro = 'As passwords não coincidem.';
    } elseif (strlen($password) < 6) {
        $erro = 'A password deve ter pelo menos 6 caracteres.';
    } elseif (registar($nome, $email, $password, $telefone)) {
        $sucesso = 'Conta criada com sucesso! Podes agora <a href="login.php" style="color:var(--accent)">entrar</a>.';
    } else {
        $erro = 'Este email já está registado.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayManager — Registar</title>
    <link rel="stylesheet" href="../css/style.css">
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
