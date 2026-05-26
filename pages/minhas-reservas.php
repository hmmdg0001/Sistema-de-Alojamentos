<?php
# Antes de começar o programa necessita o config.php, auth.php e reservas.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/reservas.php';
exigirLogin();

# Processa pedido de cancelamento (antes de carregar a lista)
if (isset($_POST['cancelar'])) {
    cancelarReserva((int)$_POST['reserva_id'], $_SESSION['user_id']);
    header('Location: minhas-reservas.php'); // recarrega a página sem reenviar POST
    exit;
}

$reservas = obterReservasHospede($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas — AlojamentosOnline</title>
    <meta name="color-scheme" content="light">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= CSS_URL ?>">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Minhas Reservas</h1>
        <p>Histórico e gestão das tuas reservas</p>
    </div>

    <?php if (empty($reservas)): ?>
        <div class="card" style="text-align:center;padding:3rem;color:var(--muted)">
            <p style="font-size:2rem">🗓️</p>
            <p>Ainda não tens reservas.</p>
            <a href="../index.php" class="btn btn-primary" style="margin-top:1rem">Ver Alojamentos</a>
        </div>
    <?php else: ?>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Alojamento</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $r): ?>
                    <tr>
                        <td>#<?= $r['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($r['alojamento_nome']) ?></strong><br>
                            <span style="color:var(--muted);font-size:.82rem"> <?= htmlspecialchars($r['localizacao']) ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($r['data_checkin'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['data_checkout'])) ?></td>
                        <td>€<?= number_format($r['preco_total'], 2) ?></td>
                        <td><span class="badge badge-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span></td>
                        <td>
                            <?php if (in_array($r['estado'], ['pendente','confirmada'])): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Cancelar reserva?')">
                                <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="cancelar" class="btn btn-danger btn-sm">Cancelar</button>
                            </form>
                            <?php else: ?>
                                <span style="color:var(--muted);font-size:.82rem">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</main>
<footer><p>© <?= date('Y') ?> AlojamentosOnline — Henrique Marinho</p></footer>
</body>
</html>