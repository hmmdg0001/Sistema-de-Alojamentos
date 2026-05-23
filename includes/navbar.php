<?php
// includes/navbar.php
require_once __DIR__ . '/auth.php';
iniciarSessao();
$logado = utilizadorLogado();
$tipo   = $_SESSION['user_tipo'] ?? '';
$nome   = $_SESSION['user_nome'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <a href="<?= BASE_URL ?>index.php" class="nav-logo">Alojamentos Online</a>
    <div class="nav-links">
        <?php if ($logado): ?>
            <a href="<?= BASE_URL ?>index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Alojamentos</a>
            <a href="<?= BASE_URL ?>pages/minhas-reservas.php" class="<?= $currentPage === 'minhas-reservas.php' ? 'active' : '' ?>">Minhas Reservas</a>
            <?php if ($tipo === 'gestor'): ?>
                <a href="<?= BASE_URL ?>pages/dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= BASE_URL ?>pages/gerir-alojamentos.php" class="<?= $currentPage === 'gerir-alojamentos.php' ? 'active' : '' ?>">Alojamentos</a>
                <a href="<?= BASE_URL ?>pages/gerir-reservas.php" class="<?= $currentPage === 'gerir-reservas.php' ? 'active' : '' ?>">Reservas</a>
            <?php endif; ?>
            <span style="color:var(--muted);font-size:.85rem;padding:0 .5rem"><?= htmlspecialchars($nome) ?></span>
            <a href="<?= BASE_URL ?>pages/logout.php" class="btn btn-outline btn-sm">Sair</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>pages/login.php">Entrar</a>
            <a href="<?= BASE_URL ?>pages/registar.php" class="btn btn-primary btn-sm">Registar</a>
        <?php endif; ?>
    </div>
</nav>
