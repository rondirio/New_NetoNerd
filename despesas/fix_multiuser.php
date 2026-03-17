<?php
/**
 * Script de correção para adicionar suporte multi-usuário
 * Execute este arquivo UMA VEZ após atualizar o sistema
 */

echo "🔧 Iniciando correções multi-usuário...\n\n";

// Verificar se todas as despesas têm usuario_id
require_once 'classes/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar despesas sem usuario_id
    $sql = "SELECT COUNT(*) as total FROM despesas WHERE usuario_id = 0 OR usuario_id IS NULL";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch();
    
    if ($result['total'] > 0) {
        echo "⚠️  Encontradas {$result['total']} despesas sem usuário atribuído.\n";
        echo "Atribuindo ao usuário padrão (ID 1)...\n";
        
        $sqlFix = "UPDATE despesas SET usuario_id = 1 WHERE usuario_id = 0 OR usuario_id IS NULL";
        $conn->exec($sqlFix);
        
        echo "✅ Despesas corrigidas!\n\n";
    } else {
        echo "✅ Todas as despesas já têm usuário atribuído.\n\n";
    }
    
    echo "✅ Correções concluídas com sucesso!\n";
    echo "\n🎯 Próximos passos:\n";
    echo "1. DELETE este arquivo (fix_multiuser.php)\n";
    echo "2. Acesse o sistema normalmente\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
