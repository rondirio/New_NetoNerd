<?php
/**
 * Helper de histórico de status de chamados.
 *
 * Substitui o trigger `registrar_historico_status` (removido na Fase 5 —
 * ver config/bandoDeDados/migracao_fase5_banco_dados.sql), que sempre
 * gravava usuario_id = tecnico_id do chamado, mesmo quando quem mudou o
 * status era o cliente ou um admin. Cada ponto que muda `chamados.status`
 * deve chamar esta função explicitamente, passando o ID de quem está
 * de fato logado.
 */

if (!function_exists('registrarHistoricoStatus')) {
    function registrarHistoricoStatus($conn, $chamado_id, $usuario_id, $status_anterior, $status_novo, $comentario = null) {
        $stmt = $conn->prepare("
            INSERT INTO historico_chamados (chamado_id, usuario_id, status_anterior, status_novo, comentario)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss", $chamado_id, $usuario_id, $status_anterior, $status_novo, $comentario);
        $stmt->execute();
        $stmt->close();
    }
}
