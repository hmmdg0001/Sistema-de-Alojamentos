<?php
# Antes de começar o programa necessita o config.php, auth.php e reservas.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/alojamentos.php';
exigirGestor();

$gestorId = $_SESSION['user_id'];
$erro = '';
$sucesso = '';
$editar = null; 

# Ativar / desativar alojamento 
if (isset($_POST['toggle_ativo'])) {
    if (toggleAlojamentoAtivo((int)$_POST['alojamento_id'], $gestorId)) {
        $sucesso = 'Estado do alojamento atualizado.';
    } else {
        $erro = 'Não foi possível alterar o alojamento.';
    }
}

# Criar ou editar alojamento
if (isset($_POST['guardar'])) {
    $dados = [
        'nome'           => trim($_POST['nome'] ?? ''),
        'localizacao'    => trim($_POST['localizacao'] ?? ''),
        'descricao'      => trim($_POST['descricao'] ?? ''),
        'preco_noite'    => (float)($_POST['preco_noite'] ?? 0),
        'capacidade'     => (int)($_POST['capacidade'] ?? 1),
        'estadia_minima' => (int)($_POST['estadia_minima'] ?? 1),
    ];

    if ($dados['nome'] === '' || $dados['localizacao'] === '') {
        $erro = 'Nome e localização são obrigatórios.';
    } elseif ($dados['preco_noite'] <= 0) {
        $erro = 'O preço por noite deve ser superior a zero.';
    } elseif ($dados['capacidade'] < 1) {
        $erro = 'A capacidade deve ser pelo menos 1.';
    } elseif ($dados['estadia_minima'] < 1) {
        $erro = 'A estadia mínima deve ser pelo menos 1 noite.';
    } else {
        $id = (int)($_POST['alojamento_id'] ?? 0);
        if ($id > 0) {
            // Modo edição
            if (atualizarAlojamento($id, $gestorId, $dados)) {
                $sucesso = 'Alojamento atualizado com sucesso.';
            } else {
                $erro = 'Não foi possível atualizar o alojamento.';
            }
        } else {
            // Modo criação 
            if (criarAlojamento($gestorId, $dados)) {
                $sucesso = 'Alojamento criado com sucesso.';
            } else {
                $erro = 'Não foi possível criar o alojamento.';
            }
        }
    }
}

# Modo edição
if (isset($_GET['editar'])) {
    $editar = obterAlojamentoGestor((int)$_GET['editar'], $gestorId);
    if (!$editar) {
        header('Location: gerir-alojamentos.php');
        exit;
    }
}

$alojamentos = obterAlojamentosGestor($gestorId);

$form = $editar ?: [
    'id' => 0, 'nome' => '', 'localizacao' => '', 'descricao' => '',
    'preco_noite' => '', 'capacidade' => 2, 'estadia_minima' => 1,
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Alojamentos — AlojamentosOnline</title>
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
        <h1>Gerir Alojamentos</h1>
        <p>Cria e edita os teus alojamentos</p>
    </div>

    <?php if ($erro): ?><div class="alert alert-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?><div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

    <!-- Formulário único -->
    <div class="card" style="margin-bottom:2rem">
        <h3 style="margin-bottom:1.2rem;font-weight:600">
            <?= $editar ? 'Editar alojamento' : 'Novo alojamento' ?>
        </h3>
        <form method="POST">
            <input type="hidden" name="alojamento_id" value="<?= (int)$form['id'] ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($form['nome']) ?>" placeholder="Ex: Apartamento Centro">
                </div>
                <div class="form-group">
                    <label>Localização</label>
                    <input type="text" name="localizacao" required value="<?= htmlspecialchars($form['localizacao']) ?>" placeholder="Ex: Lisboa, Portugal">
                </div>
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3" placeholder="Descreve o alojamento..."><?= htmlspecialchars($form['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Preço por noite (€)</label>
                    <input type="number" name="preco_noite" required min="0.01" step="0.01" value="<?= htmlspecialchars((string)$form['preco_noite']) ?>">
                </div>
                <div class="form-group">
                    <label>Capacidade (hóspedes)</label>
                    <input type="number" name="capacidade" required min="1" value="<?= (int)$form['capacidade'] ?>">
                </div>
                <div class="form-group">
                    <label>Estadia mínima (noites)</label>
                    <input type="number" name="estadia_minima" required min="1" value="<?= (int)$form['estadia_minima'] ?>">
                </div>
            </div>
            <div style="display:flex;gap:.8rem;flex-wrap:wrap">
                <button type="submit" name="guardar" class="btn btn-primary">
                    <?= $editar ? 'Guardar alterações' : 'Criar alojamento' ?>
                </button>
                <?php if ($editar): ?>
                <a href="gerir-alojamentos.php" class="btn btn-outline">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela com todos os alojamentos deste gestor -->
    <div class="card">
        <h3 style="margin-bottom:1rem;font-weight:600">Os teus alojamentos (<?= count($alojamentos) ?>)</h3>
        <?php if (empty($alojamentos)): ?>
            <p style="color:var(--muted)">Ainda não criaste nenhum alojamento.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Localização</th>
                        <th>Preço/noite</th>
                        <th>Reservas</th>
                        <th>Avaliação</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alojamentos as $a): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($a['nome']) ?></strong></td>
                        <td><?= htmlspecialchars($a['localizacao']) ?></td>
                        <td>€<?= number_format($a['preco_noite'], 2) ?></td>
                        <td><?= (int)$a['total_reservas'] ?></td>
                        <td>
                            <?php if ($a['media_avaliacao']): ?>
                                <span class="stars" style="font-size:.85rem">★</span> <?= $a['media_avaliacao'] ?>
                            <?php else: ?>
                                <span style="color:var(--muted)">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($a['ativo']): ?>
                                <span class="badge badge-confirmada">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-cancelada">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap">
                            <a href="gerir-alojamentos.php?editar=<?= $a['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                            <a href="alojamento.php?id=<?= $a['id'] ?>" class="btn btn-outline btn-sm">Ver</a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Alterar visibilidade deste alojamento?')">
                                <input type="hidden" name="alojamento_id" value="<?= $a['id'] ?>">
                                <button type="submit" name="toggle_ativo" class="btn btn-sm <?= $a['ativo'] ? 'btn-danger' : 'btn-success' ?>">
                                    <?= $a['ativo'] ? 'Desativar' : 'Ativar' ?>
                                </button>
                            </form>
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