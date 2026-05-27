<?php

# Antes de começar o programa necessita do config.php e auth.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$pdo = conectar(); # Conecta á base de dados

# Lista os alojamentos ativos
$stmt = $pdo->query("
    SELECT a.*, u.nome AS gestor_nome,
           ROUND(AVG(av.pontuacao),1) AS media_avaliacao,
           COUNT(av.id) AS total_avaliacoes
    FROM alojamentos a
    JOIN utilizadores u ON a.gestor_id = u.id
    LEFT JOIN avaliacoes av ON a.id = av.alojamento_id
    WHERE a.ativo = 1
    GROUP BY a.id
    ORDER BY a.id DESC
");
$alojamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alojamentos Online</title>
    <meta name="color-scheme" content="light">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= CSS_URL ?>">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <main class="container">
        <div class="page-header">
            <h1>Alojamentos Disponíveis</h1>
            <p>Encontra o alojamento perfeito para a tua próxima estadia</p>
        </div>

        <?php if (empty($alojamentos)): ?>
            <div class="card" style="text-align:center;padding:3rem;color:var(--muted)">
                <p style="font-size:2rem">🏠</p>
                <p>Nenhum alojamento disponível de momento.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($alojamentos as $a): ?>
                    <a href="pages/alojamento.php?id=<?= $a['id'] ?>" class="alojamento-card">
                        <div class="alojamento-img">🏠</div>
                        <div class="alojamento-body">
                            <h3><?= htmlspecialchars($a['nome']) ?></h3>
                            <p class="loc"> <?= htmlspecialchars($a['localizacao']) ?></p>
                            <?php if ($a['media_avaliacao']): ?>
                                <p style="margin-top:.4rem;font-size:.85rem">
                                    <span class="stars"><?= str_repeat('★', round($a['media_avaliacao'])) ?></span>
                                    <span style="color:var(--muted)"><?= $a['media_avaliacao'] ?>
                                        (<?= $a['total_avaliacoes'] ?>avaliações)</span>
                                </p>
                            <?php endif; ?>
                            <p class="preco">€<?= number_format($a['preco_noite'], 2) ?> <span>/ noite</span></p>
                            <p style="color:var(--muted);font-size:.8rem;margin-top:.3rem">Estadia mínima:
                                <?= $a['estadia_minima'] ?> noite(s)
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <footer>
        <p>© <?= date('Y') ?> AlojamentosOnline — Henrique Marinho</p>
    </footer>
</body>
</html>