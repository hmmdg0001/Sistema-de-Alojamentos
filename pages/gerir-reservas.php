<?php
# Antes de começar o programa necessita o config.php, auth.php e reservas.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/reservas.php';
exigirGestor();

$gestorId = $_SESSION['user_id'];
$erro = '';
$sucesso = '';

# Processar alteração de estado de uma reserva
if (isset($_POST['acao'], $_POST['reserva_id'])) {
    $reservaId = (int)$_POST['reserva_id'];
    $acao = $_POST['acao']; 

    # Traduz a ação do formulário para o valor da coluna estado
    $estados = [
        'confirmar'  => 'confirmada',
        'cancelar'   => 'cancelada',
        'concluir'   => 'concluida',
        'repor'      => 'pendente',
    ];

    if (!isset($estados[$acao])) {
        $erro = 'Ação inválida.';
    } elseif (atualizarEstadoReservaGestor($reservaId, $gestorId, $estados[$acao])) {
        $sucesso = 'Reserva atualizada com sucesso.';
    } else {
        $erro = 'Não foi possível atualizar a reserva.';
    }
}

$filtro = $_GET['estado'] ?? '';
$reservas = obterReservasGestor($gestorId, $filtro !== '' ? $filtro : null);
$estadosFiltro = ['', 'pendente', 'confirmada', 'concluida', 'cancelada'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Reservas — AlojamentosOnline</title>
    <?php include '../includes/head-css.php'; ?>
    <style>
        .filtros { display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem; }
        .filtros a {
            padding:.4rem .9rem;border-radius:20px;font-size:.85rem;text-decoration:none;
            border:1px solid var(--border);color:var(--muted);transition:all .2s;
        }
        .filtros a:hover, .filtros a.ativo { border-color:var(--accent);color:var(--accent);background:var(--accent-light); }
        .acoes-cell { display:flex;gap:.4rem;flex-wrap:wrap; }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Gerir Reservas</h1>
        <p>Confirma, cancela e acompanha as reservas dos teus alojamentos</p>
    </div>

    <?php if ($erro): ?><div class="alert alert-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?><div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

    <div class="filtros">
        <?php foreach ($estadosFiltro as $e): ?>
        <a href="?<?= $e === '' ? '' : 'estado=' . urlencode($e) ?>" class="<?= $filtro === $e ? 'ativo' : '' ?>">
            <?= $e === '' ? 'Todas' : ucfirst($e) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <?php if (empty($reservas)): ?>
            <p style="color:var(--muted);text-align:center;padding:2rem">
                <?= $filtro ? 'Nenhuma reserva com este estado.' : 'Ainda não há reservas nos teus alojamentos.' ?>
            </p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Alojamento</th>
                        <th>Hóspede</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Hóspedes</th>
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
                            <span style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($r['localizacao']) ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($r['hospede_nome']) ?><br>
                            <span style="color:var(--muted);font-size:.78rem"><?= htmlspecialchars($r['hospede_email']) ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($r['data_checkin'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['data_checkout'])) ?></td>
                        <td><?= (int)$r['num_hospedes'] ?></td>
                        <td>€<?= number_format($r['preco_total'], 2) ?></td>
                        <td><span class="badge badge-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span></td>
                        <td>
                            <div class="acoes-cell">
                                <?php if ($r['estado'] === 'pendente'): ?>
                                <!-- Gestor aceita ou recusa pedido inicial -->
                                <form method="POST">
                                    <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="acao" value="confirmar" class="btn btn-success btn-sm">Confirmar</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Cancelar esta reserva?')">
                                    <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="acao" value="cancelar" class="btn btn-danger btn-sm">Recusar</button>
                                </form>
                                <?php elseif ($r['estado'] === 'confirmada'): ?>
                                <!-- Após a estadia, marcar como concluída (hóspede pode avaliar) -->
                                <form method="POST" onsubmit="return confirm('Marcar como concluída?')">
                                    <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="acao" value="concluir" class="btn btn-primary btn-sm">Concluir</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Cancelar esta reserva?')">
                                    <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="acao" value="cancelar" class="btn btn-danger btn-sm">Cancelar</button>
                                </form>
                                <?php elseif (in_array($r['estado'], ['cancelada', 'concluida'])): ?>
                                    <span style="color:var(--muted);font-size:.82rem">—</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($r['notas'])): ?>
                            <p style="color:var(--muted);font-size:.75rem;margin-top:.4rem" title="<?= htmlspecialchars($r['notas']) ?>">
                                📝 <?= htmlspecialchars(strlen($r['notas']) > 40 ? substr($r['notas'], 0, 40) . '…' : $r['notas']) ?>
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</main>
<footer><p>© <?= date('Y') ?> AlojamentosOnline — Henrique Marinho</p></footer>
</body>
</html>