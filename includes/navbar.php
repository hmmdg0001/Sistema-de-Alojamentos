<?php
# Barra de navegação do sistema
# Antes de começar o programa necessita o auth.php
require_once __DIR__ . '/auth.php';
iniciarSessao();

$autenticado = utilizadorautenticado();
$tipo   = $_SESSION['user_tipo'] ?? '';
$nome   = $_SESSION['user_nome'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <a href="<?= BASE_URL ?>index.php" class="nav-logo">Alojamentos Online</a>
    <div class="nav-links">
        <?php if ($autenticado): ?>
            <!-- Menu do utilizador autenticado -->
            <a href="<?= BASE_URL ?>index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Alojamentos</a>
            <a href="<?= BASE_URL ?>pages/minhas-reservas.php" class="<?= $currentPage === 'minhas-reservas.php' ? 'active' : '' ?>">Minhas Reservas</a>

            <?php if ($tipo === 'gestor'): ?>
                <!-- Menu extra só para gestores -->
                <a href="<?= BASE_URL ?>pages/dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= BASE_URL ?>pages/gerir-alojamentos.php" class="<?= $currentPage === 'gerir-alojamentos.php' ? 'active' : '' ?>">Alojamentos</a>
                <a href="<?= BASE_URL ?>pages/gerir-reservas.php" class="<?= $currentPage === 'gerir-reservas.php' ? 'active' : '' ?>">Reservas</a>
            <?php endif; ?>

            <span class="nav-user"><?= htmlspecialchars($nome) ?></span>
            <a href="<?= BASE_URL ?>pages/logout.php" class="btn btn-outline btn-sm">Sair</a>
        <?php else: ?>
            <!-- Menu do utilizador sem conta -->
            <a href="<?= BASE_URL ?>pages/login.php">Entrar</a>
            <a href="<?= BASE_URL ?>pages/registar.php" class="btn btn-primary btn-sm">Registar</a>
        <?php endif; ?>
    </div>
</nav>
