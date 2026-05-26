<?php
# Antes de começar o programa necessita o config.php, auth.php e reservas.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/reservas.php';

// (int) força número inteiro; protege contra id inválido na URL
$id = (int)($_GET['id'] ?? 0);
$pdo = conectar();

$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS gestor_nome
    FROM alojamentos a
    JOIN utilizadores u ON a.gestor_id = u.id
    WHERE a.id = ? AND a.ativo = 1
");
$stmt->execute([$id]);
$alojamento = $stmt->fetch();

if (!$alojamento) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Comentários/avaliações deste alojamento (mais recentes primeiro)
$av = $pdo->prepare("
    SELECT av.*, u.nome AS hospede_nome
    FROM avaliacoes av
    JOIN utilizadores u ON av.hospede_id = u.id
    WHERE av.alojamento_id = ?
    ORDER BY av.criado_em DESC
");
$av->execute([$id]);
$avaliacoes = $av->fetchAll();

$mediaAv = count($avaliacoes) ? round(array_sum(array_column($avaliacoes, 'pontuacao')) / count($avaliacoes), 1) : null; # Média de avaliações do alojamento

// Para futuro calendário JS (datas já reservadas ou bloqueadas)
$datasOcupadas = datasOcupadas($id);

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar'])) { # Função de formulário de reserva
    exigirLogin(); # Redireciona para o login se não estiver autenticado

    $checkin    = $_POST['checkin'] ?? '';
    $checkout   = $_POST['checkout'] ?? '';
    $numHosp    = (int)($_POST['num_hospedes'] ?? 1);
    $notas      = trim($_POST['notas'] ?? '');

    if (!$checkin || !$checkout) {
        $erro = 'Preenche as datas de check-in e check-out.';
    } elseif ($checkin >= $checkout) {
        $erro = 'O check-out deve ser após o check-in.';
    } else {
        # criarReserva valida estadia mínima e disponibilidade internamente
        $reservaId = criarReserva($id, $_SESSION['user_id'], $checkin, $checkout, $numHosp, $notas);
        if ($reservaId) {
            $sucesso = "Reserva criada com sucesso! Nº $reservaId — aguarda confirmação.";
        } elseif (!verificarDisponibilidade($id, $checkin, $checkout)) {
            $erro = 'Datas indisponíveis. Escolhe outras datas.';
        } else {
            $erro = "Não cumpres a estadia mínima de {$alojamento['estadia_minima']} noite(s).";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avaliar'])) { # Formulário de avaliação
    exigirLogin();
    $reservaId  = (int)($_POST['reserva_id'] ?? 0);
    $pontuacao  = (int)($_POST['pontuacao'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($pontuacao >= 1 && $pontuacao <= 5 && $reservaId) {
        // INSERT IGNORE evita avaliar duas vezes a mesma reserva (unique em reserva_id)
        $ins = $pdo->prepare("INSERT IGNORE INTO avaliacoes (reserva_id, hospede_id, alojamento_id, pontuacao, comentario) VALUES (?,?,?,?,?)");
        $ins->execute([$reservaId, $_SESSION['user_id'], $id, $pontuacao, $comentario]);
        header("Location: alojamento.php?id=$id"); // POST-redirect-GET evita reenvio do formulário
        exit;
    }
}

// Hóspede com estadia concluída e ainda sem avaliação pode avaliar
$reservaParaAvaliar = null;
if (utilizadorautenticado()) {
    $rp = $pdo->prepare("
        SELECT r.id FROM reservas r
        LEFT JOIN avaliacoes av ON av.reserva_id = r.id
        WHERE r.alojamento_id = ? AND r.hospede_id = ? AND r.estado = 'concluida' AND av.id IS NULL
        LIMIT 1
    ");
    $rp->execute([$id, $_SESSION['user_id']]);
    $reservaParaAvaliar = $rp->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($alojamento['nome']) ?> — AlojamentosOnline</title>
    <meta name="color-scheme" content="light">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= CSS_URL ?>">
    <style>
        .aloj-hero { background: var(--surface2); height: 280px; border-radius: var(--radius); display:flex;align-items:center;justify-content:center;font-size:5rem;margin-bottom:2rem;border:1px solid var(--border); }
        .aloj-grid { display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start; }
        .info-row { display:flex;gap:2rem;margin:1rem 0;flex-wrap:wrap; }
        .info-item { color:var(--muted);font-size:.9rem; }
        .info-item strong { color:var(--text);display:block; }
        @media(max-width:760px){ .aloj-grid{grid-template-columns:1fr} }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<main class="container" style="padding-top:2rem">
    <div class="aloj-hero">🏠</div>

    <div class="aloj-grid">
        <!-- Coluna esquerda: informação e avaliações -->
        <div>
            <h1 style="font-size:1.75rem;font-weight:600;color:var(--text)"><?= htmlspecialchars($alojamento['nome']) ?></h1>
            <p style="color:var(--muted);margin-top:.3rem"> <?= htmlspecialchars($alojamento['localizacao']) ?></p>

            <?php if ($mediaAv): ?>
            <p style="margin:.5rem 0">
                <span class="stars"><?= str_repeat('★', round($mediaAv)) ?><?= str_repeat('☆', 5 - round($mediaAv)) ?></span>
                <span style="color:var(--muted);font-size:.85rem"> <?= $mediaAv ?> (<?= count($avaliacoes) ?> avaliações)</span>
            </p>
            <?php endif; ?>

            <div class="info-row">
                <div class="info-item"><strong>€<?= number_format($alojamento['preco_noite'],2) ?></strong>por noite</div>
                <div class="info-item"><strong><?= $alojamento['capacidade'] ?> pessoas</strong>capacidade máx.</div>
                <div class="info-item"><strong><?= $alojamento['estadia_minima'] ?> noite(s)</strong>estadia mínima</div>
            </div>

            <div class="card" style="margin-top:1.5rem">
                <h3 style="margin-bottom:.8rem">Descrição</h3>
                <p style="color:var(--muted);line-height:1.7"><?= nl2br(htmlspecialchars($alojamento['descricao'] ?? 'Sem descrição disponível.')) ?></p>
            </div>

            <div style="margin-top:2rem">
                <h3 style="margin-bottom:1rem;font-weight:600">Avaliações</h3>
                <?php if (empty($avaliacoes)): ?>
                    <p style="color:var(--muted)">Sem avaliações ainda.</p>
                <?php else: ?>
                    <?php foreach ($avaliacoes as $av): ?>
                    <div class="card" style="margin-bottom:1rem">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
                            <strong><?= htmlspecialchars($av['hospede_nome']) ?></strong>
                            <span class="stars" style="font-size:.9rem"><?= str_repeat('★', $av['pontuacao']) ?><?= str_repeat('☆', 5-$av['pontuacao']) ?></span>
                        </div>
                        <p style="color:var(--muted);font-size:.9rem"><?= htmlspecialchars($av['comentario']) ?></p>
                        <p style="color:var(--border);font-size:.75rem;margin-top:.4rem"><?= date('d/m/Y', strtotime($av['criado_em'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($reservaParaAvaliar): ?>
                <div class="card" style="margin-top:1.5rem;border-color:var(--accent)">
                    <h4 style="margin-bottom:1rem">Deixa a tua avaliação</h4>
                    <form method="POST">
                        <input type="hidden" name="reserva_id" value="<?= $reservaParaAvaliar ?>">
                        <div class="form-group">
                            <label>Pontuação (1-5)</label>
                            <select name="pontuacao" required>
                                <?php for ($i=5;$i>=1;$i--): ?>
                                <option value="<?=$i?>"><?=$i?> <?= str_repeat('★',$i) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Comentário</label>
                            <textarea name="comentario" rows="3" placeholder="Conta-nos a tua experiência..."></textarea>
                        </div>
                        <button type="submit" name="avaliar" class="btn btn-primary">Enviar Avaliação</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Coluna direita: formulário de reserva -->
        <div>
            <div class="card" style="position:sticky;top:80px">
                <h3 style="margin-bottom:1.2rem">Reservar</h3>

                <?php if ($erro): ?><div class="alert alert-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

                <?php if (utilizadorautenticado()): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Check-in</label>
                        <input type="date" name="checkin" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['checkin'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Check-out</label>
                        <input type="date" name="checkout" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= htmlspecialchars($_POST['checkout'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Nº de hóspedes</label>
                        <select name="num_hospedes">
                            <?php for ($i=1; $i<=$alojamento['capacidade']; $i++): ?>
                            <option value="<?=$i?>"><?=$i?> hóspede(s)</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notas (opcional)</label>
                        <textarea name="notas" rows="2" placeholder="Pedidos especiais..."></textarea>
                    </div>
                    <button type="submit" name="reservar" class="btn btn-primary" style="width:100%;justify-content:center">Confirmar Reserva</button>
                </form>
                <?php else: ?>
                    <p style="color:var(--muted);text-align:center;margin-bottom:1rem">Faz login para reservar este alojamento.</p>
                    <a href="login.php" class="btn btn-primary" style="width:100%;justify-content:center">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<footer><p>© <?= date('Y') ?> Alojamentos Online — Henrique Marinho</p></footer>
</body>
</html>