<?php
/**
 * Direitos do titular (LGPD) — M2/M3 do plano de correção.
 * MVP técnico: anonimização (em vez de exclusão) e exportação de dados.
 * `chamados`/`ordens_servico` mantêm o registro como histórico
 * estatístico/fiscal — só os campos identificáveis do cliente são
 * sobrescritos, o vínculo cliente_id é preservado para integridade
 * referencial mas passa a apontar para um titular anonimizado.
 */

require_once __DIR__ . '/auth_middleware.php';

/**
 * Retorna todos os dados pessoais do cliente em array associativo,
 * para exportação/portabilidade (art. 18 LGPD).
 */
function exportarDadosCliente(mysqli $conn, int $clienteId): array
{
    $dados = [];

    $stmt = $conn->prepare("SELECT id, nome, email, telefone, endereco, complemento, cep, genero, data_criacao FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $clienteId);
    $stmt->execute();
    $dados['cliente'] = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT id, protocolo, titulo, descricao, status, data_abertura, data_atualizacao FROM chamados WHERE cliente_id = ?");
    $stmt->bind_param("i", $clienteId);
    $stmt->execute();
    $dados['chamados'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conn->prepare("SELECT id, numero_os, equipamento_tipo, equipamento_marca, equipamento_modelo, problema_relatado, valor_total, data_criacao FROM ordens_servico WHERE cliente_id = ?");
    $stmt->bind_param("i", $clienteId);
    $stmt->execute();
    $dados['ordens_servico'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $dados;
}

/**
 * Anonimiza os dados identificáveis do cliente em clientes, chamados e
 * ordens_servico. Não apaga os registros — preserva histórico/integridade
 * referencial, só sobrescreve nome/email/telefone/endereco/CPF.
 */
function anonimizarCliente(mysqli $conn, int $clienteId, int $executadoPorAdminId): bool
{
    $marcador = 'Titular anonimizado #' . $clienteId;
    $emailAnonimo = 'anonimizado+' . $clienteId . '@netonerd.invalid';

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            UPDATE clientes SET
                nome = ?, email = ?, telefone = NULL, endereco = NULL,
                complemento = NULL, cep = NULL, senha_hash = NULL
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $marcador, $emailAnonimo, $clienteId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE chamados SET
                cliente_nome = ?, cliente_email = ?, cliente_telefone = NULL
            WHERE cliente_id = ?
        ");
        $stmt->bind_param("ssi", $marcador, $emailAnonimo, $clienteId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE ordens_servico SET
                cliente_nome = ?, cliente_email = ?, cliente_telefone = 'ANONIMIZADO',
                cliente_endereco = NULL, cliente_cpf = NULL
            WHERE cliente_id = ?
        ");
        $stmt->bind_param("ssi", $marcador, $emailAnonimo, $clienteId);
        $stmt->execute();
        $stmt->close();

        registrarLogSistema($conn, $executadoPorAdminId, "Anonimizou dados do cliente #$clienteId (LGPD)", 'cliente', $clienteId);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("anonimizarCliente falhou para cliente #$clienteId: " . $e->getMessage());
        return false;
    }
}
