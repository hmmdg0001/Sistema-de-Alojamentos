<?php
# Antes de começar o programa necessita o config.php
require_once __DIR__ . '/config.php';

function verificarDisponibilidade(int $alojamentoId, string $checkin, string $checkout): bool { # Função que vai verificar a disponibilidade dos alojamentos
    $pdo = conectar();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservas
        WHERE alojamento_id = ?
          AND estado NOT IN ('cancelada')
          AND data_checkin < ?
          AND data_checkout > ?
    ");
    $stmt->execute([$alojamentoId, $checkout, $checkin]);
    if ($stmt->fetchColumn() > 0) return false;

    $stmt2 = $pdo->prepare("
        SELECT COUNT(*) FROM datas_bloqueadas
        WHERE alojamento_id = ?
          AND data_inicio < ?
          AND data_fim > ?
    ");
    $stmt2->execute([$alojamentoId, $checkout, $checkin]);
    return $stmt2->fetchColumn() == 0;
}


function criarReserva(int $alojamentoId, int $hospedeId, string $checkin, string $checkout, int $numHospedes, string $notas = ''): int|false { # Função que cria Reservas
    $pdo = conectar();

    $aloj = $pdo->prepare("SELECT preco_noite, estadia_minima FROM alojamentos WHERE id = ?");
    $aloj->execute([$alojamentoId]);
    $alojamento = $aloj->fetch();
    if (!$alojamento) return false;

    $noites = (new DateTime($checkout))->diff(new DateTime($checkin))->days; # Calcula as noites e impede que existam 2 reservas no mesmo dia
    if ($noites < $alojamento['estadia_minima']) return false; # Verifica a estadia minima
    if (!verificarDisponibilidade($alojamentoId, $checkin, $checkout)) return false; # Verifica se o alojamento está disponivel

    $precoTotal = $noites * $alojamento['preco_noite']; # Calcula o preço total

    $stmt = $pdo->prepare("
        INSERT INTO reservas (alojamento_id, hospede_id, data_checkin, data_checkout, num_hospedes, preco_total, notas)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    "); # Insere na base de dados a reserva
    $stmt->execute([$alojamentoId, $hospedeId, $checkin, $checkout, $numHospedes, $precoTotal, $notas]);
    return (int) $pdo->lastInsertId();
}

function cancelarReserva(int $reservaId, int $utilizadorId): bool { #Função que cancela reservas
    $pdo = conectar();
    $stmt = $pdo->prepare("
        UPDATE reservas SET estado = 'cancelada'
        WHERE id = ? AND (hospede_id = ? OR ? IN (SELECT id FROM utilizadores WHERE tipo = 'gestor'))
          AND estado IN ('pendente','confirmada')
    ");
    $stmt->execute([$reservaId, $utilizadorId, $utilizadorId]);
    return $stmt->rowCount() > 0;
}

function confirmarReserva(int $reservaId): bool { # Função unica para o gestor que permite aceitar ou recusar  uma reserva pendente
    $pdo = conectar();
    $stmt = $pdo->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ? AND estado = 'pendente'");
    $stmt->execute([$reservaId]);
    return $stmt->rowCount() > 0;
}

function obterReservasHospede(int $hospedeId): array { # Função que obtem as reservas todas feitas por um cliente
    $pdo = conectar();
    $stmt = $pdo->prepare("
        SELECT r.*, a.nome AS alojamento_nome, a.localizacao
        FROM reservas r
        JOIN alojamentos a ON r.alojamento_id = a.id
        WHERE r.hospede_id = ?
        ORDER BY r.criado_em DESC
    ");
    $stmt->execute([$hospedeId]);
    return $stmt->fetchAll();
}

function obterTodasReservas(): array { # Função que lista todas as reservas feitas - Exclusivo para os gestores
    $pdo = conectar();
    return $pdo->query("
        SELECT r.*, a.nome AS alojamento_nome, u.nome AS hospede_nome
        FROM reservas r
        JOIN alojamentos a ON r.alojamento_id = a.id
        JOIN utilizadores u ON r.hospede_id = u.id
        ORDER BY r.data_checkin DESC
    ")->fetchAll();
}

function datasOcupadas(int $alojamentoId): array { # Função que lista as datas ocupadas
    $pdo = conectar();
    $stmt = $pdo->prepare("
        SELECT data_checkin, data_checkout FROM reservas
        WHERE alojamento_id = ? AND estado NOT IN ('cancelada')
        UNION
        SELECT data_inicio, data_fim FROM datas_bloqueadas
        WHERE alojamento_id = ?
    ");
    $stmt->execute([$alojamentoId, $alojamentoId]);
    return $stmt->fetchAll();
}

function obterReservasGestor(int $gestorId, ?string $estado = null): array { # Função exclusiva do gestor que mosta as reservas de um alojamento
    $pdo = conectar();
    $sql = "
        SELECT r.*, a.nome AS alojamento_nome, a.localizacao, u.nome AS hospede_nome, u.email AS hospede_email
        FROM reservas r
        JOIN alojamentos a ON r.alojamento_id = a.id
        JOIN utilizadores u ON r.hospede_id = u.id
        WHERE a.gestor_id = ?
    ";
    $params = [$gestorId];
    if ($estado) {
        $sql .= " AND r.estado = ?";
        $params[] = $estado;
    }
    $sql .= " ORDER BY r.data_checkin DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function reservaPertenceGestor(int $reservaId, int $gestorId): bool { # Função que confirma que a reserva é de um alojamento deste gestor
    $pdo = conectar();
    $stmt = $pdo->prepare("
        SELECT r.id FROM reservas r
        JOIN alojamentos a ON r.alojamento_id = a.id
        WHERE r.id = ? AND a.gestor_id = ?
    ");
    $stmt->execute([$reservaId, $gestorId]);
    return (bool) $stmt->fetch();
}

function atualizarEstadoReservaGestor(int $reservaId, int $gestorId, string $estado): bool { # Função que vai alterar o estado da reserva, o mesmo só acontece se pertencer ao gestor e estado for válido
    if (!reservaPertenceGestor($reservaId, $gestorId)) {
        return false;
    }
    $permitidos = ['pendente', 'confirmada', 'cancelada', 'concluida'];
    if (!in_array($estado, $permitidos, true)) {
        return false;
    }
    $pdo = conectar();
    $stmt = $pdo->prepare("UPDATE reservas SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $reservaId]);
    return $stmt->rowCount() > 0;
}

function obterEstatisticasGestor(int $gestorId): array { # Função que recolhe os dados para a dashboard
    $pdo = conectar();

    $aloj = $pdo->prepare("
        SELECT COUNT(*) AS total,
               SUM(ativo = 1) AS ativos
        FROM alojamentos WHERE gestor_id = ?
    ");
    $aloj->execute([$gestorId]);
    $alojStats = $aloj->fetch();

    $res = $pdo->prepare("
        SELECT
            SUM(r.estado = 'pendente') AS pendentes,
            SUM(r.estado = 'confirmada') AS confirmadas,
            SUM(r.estado = 'concluida') AS concluidas,
            SUM(r.estado = 'cancelada') AS canceladas,
            COALESCE(SUM(CASE WHEN r.estado IN ('confirmada','concluida') THEN r.preco_total ELSE 0 END), 0) AS receita_total
        FROM reservas r
        JOIN alojamentos a ON r.alojamento_id = a.id
        WHERE a.gestor_id = ?
    ");
    $res->execute([$gestorId]);
    $resStats = $res->fetch();

    $proximas = $pdo->prepare("
        SELECT COUNT(*) FROM reservas r
        JOIN alojamentos a ON r.alojamento_id = a.id
        WHERE a.gestor_id = ?
          AND r.estado = 'confirmada'
          AND r.data_checkin >= CURDATE()
    ");
    $proximas->execute([$gestorId]);

    return array_merge($alojStats ?: [], $resStats ?: [], [
        'proximas_estadias' => (int) $proximas->fetchColumn(),
    ]);
}