<?php
# Antes de começar o programa necessita o config.php
require_once __DIR__ . '/config.php';

function obterAlojamentosGestor(int $gestorId): array { # Função que vai listar os alojamentos do gestor com as reservas totais e a média de avaliações
    $pdo = conectar();
    $stmt = $pdo->prepare("
        SELECT a.*,
               COUNT(DISTINCT r.id) AS total_reservas,
               ROUND(AVG(av.pontuacao), 1) AS media_avaliacao
        FROM alojamentos a
        LEFT JOIN reservas r ON r.alojamento_id = a.id AND r.estado NOT IN ('cancelada')
        LEFT JOIN avaliacoes av ON av.alojamento_id = a.id
        WHERE a.gestor_id = ?
        GROUP BY a.id
        ORDER BY a.id DESC
    ");
    $stmt->execute([$gestorId]);
    return $stmt->fetchAll();
}

function obterAlojamentoGestor(int $id, int $gestorId): array|false { # Função que garante que um alojamento especifico só se pertencer ao gestor
    $pdo = conectar();
    $stmt = $pdo->prepare("SELECT * FROM alojamentos WHERE id = ? AND gestor_id = ?");
    $stmt->execute([$id, $gestorId]);
    return $stmt->fetch() ?: false;
}

function criarAlojamento(int $gestorId, array $dados): int|false { # Função para criar um novo alojamento e cria um id unico para o alojamento
    $pdo = conectar();
    $stmt = $pdo->prepare("
        INSERT INTO alojamentos (gestor_id, nome, localizacao, descricao, preco_noite, capacidade, estadia_minima, ativo)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $ok = $stmt->execute([
        $gestorId,
        $dados['nome'],
        $dados['localizacao'],
        $dados['descricao'] ?? '',
        $dados['preco_noite'],
        $dados['capacidade'],
        $dados['estadia_minima'],
    ]);
    return $ok ? (int) $pdo->lastInsertId() : false;
}

function atualizarAlojamento(int $id, int $gestorId, array $dados): bool { # Função para atualizar um alojamento
    $pdo = conectar();
    $stmt = $pdo->prepare("
        UPDATE alojamentos
        SET nome = ?, localizacao = ?, descricao = ?, preco_noite = ?,
            capacidade = ?, estadia_minima = ?
        WHERE id = ? AND gestor_id = ?
    ");
    $stmt->execute([
        $dados['nome'],
        $dados['localizacao'],
        $dados['descricao'] ?? '',
        $dados['preco_noite'],
        $dados['capacidade'],
        $dados['estadia_minima'],
        $id,
        $gestorId,
    ]);
    return $stmt->rowCount() > 0;
}

function toggleAlojamentoAtivo(int $id, int $gestorId): bool { # Função que alterna se o alojamento está ativo ou inativo
    $pdo = conectar();
    $stmt = $pdo->prepare("
        UPDATE alojamentos SET ativo = NOT ativo
        WHERE id = ? AND gestor_id = ?
    ");
    $stmt->execute([$id, $gestorId]);
    return $stmt->rowCount() > 0;
}
