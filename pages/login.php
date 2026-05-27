<?php
# Antes de começar o programa necessita o config.php e auth.php
require_once '../includes/config.php';
require_once '../includes/auth.php';

iniciarSessao();
// Utilizador autenticado não precisa de ver esta página outra vez
if (utilizadorautenticado()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login($email, $password)) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    } else {
        $erro = 'Email ou password incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlojamentosOnline — Entrar</title>
    <meta name="color-scheme" content="light">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= CSS_URL ?>">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-box">
        <h2>Bem-vindo de volta</h2>
        <p>Entra na tua conta AlojamentosOnline</p>
        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="email@exemplo.pt" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">Entrar</button>
        </form>
        <p style="text-align:center;margin-top:1.2rem;color:var(--muted);font-size:.9rem">
            Não tens conta? <a href="registar.php" style="color:var(--accent)">Regista-te</a>
        </p>
    </div>
</div>
</body>
</html>