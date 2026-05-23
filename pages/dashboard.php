<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/reservas.php';
exigirGestor();

$stats = obterEstatisticasGestor($_SESSION['user_id']);
$reservasRecentes = array_slice(obterReservasGestor($_SESSION['user_id']), 0, 5);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — StayManager</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Visão geral dos teus alojamentos e reservas</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?= (int)($stats['ativos'] ?? 0) ?></div>
            <div class="stat-lbl">Alojamentos ativos</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?= (int)($stats['pendentes'] ?? 0) ?></div>
            <div class="stat-lbl">Reservas pendentes</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?= (int)($stats['confirmadas'] ?? 0) ?></div>
            <div class="stat-lbl">Reservas confirmadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?= (int)($stats['proximas_estadias'] ?? 0) ?></div>
            <div class="stat-lbl">Próximas estadias</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">€<?= number_format((float)($stats['receita_total'] ?? 0), 0) ?></div>
            <div class="stat-lbl">Receita total</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?= (int)($stats['concluidas'] ?? 0) ?></div>
            <div class="stat-lbl">Estadias concluídas</div>
        </div>
    </div>

    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem">
        <a href="gerir-alojamentos.php" class="btn btn-primary">Gerir Alojamentos</a>
        <a href="gerir-reservas.php" class="btn btn-outline">Gerir Reservas</a>
        <?php if ((int)($stats['pendentes'] ?? 0) > 0): ?>
        <a href="gerir-reservas.php?estado=pendente" class="btn btn-outline">Ver pendentes (<?= (int)$stats['pendentes'] ?>)</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-bottom:1rem;font-family:'DM Serif Display',serif">Reservas recentes</h3>
        <?php if (empty($reservasRecentes)): ?>
            <p style="color:var(--muted)">Ainda não há reservas nos teus alojamentos.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Alojamento</th>
                        <th>Hóspede</th>
                        <th>Check-in</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservasRecentes as $r): ?>
                    <tr>
                        <td>#<?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['alojamento_nome']) ?></td>
                        <td><?= htmlspecialchars($r['hospede_nome']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['data_checkin'])) ?></td>
                        <td>€<?= number_format($r['preco_total'], 2) ?></td>
                        <td><span class="badge badge-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:1rem;text-align:right">
            <a href="gerir-reservas.php" style="color:var(--accent);font-size:.9rem">Ver todas →</a>
        </p>
        <?php endif; ?>
    </div>
</main>

<footer><p>© <?= date('Y') ?> StayManager — Henrique Marinho</p></footer>
</body>
</html>
