<?php
/**
 * Script para Limpar Técnicos com ID = 0
 * NetoNerd ITSM v2.0
 * ATENÇÃO: Execute este script apenas UMA VEZ
 */

require_once '../config/bandoDeDados/conexao.php';

$conn = getConnection();

// Primeiro, vamos verificar quantos registros com id = 0 existem
$sql_check = "SELECT COUNT(*) as total FROM tecnicos WHERE id = 0";
$result_check = $conn->query($sql_check);
$total = $result_check->fetch_assoc()['total'];

echo "<h2>Script de Limpeza - Técnicos com ID = 0</h2>";
echo "<p>Total de registros encontrados com ID = 0: <strong>{$total}</strong></p>";

if ($total > 0) {
    // Mostra os registros que serão deletados
    echo "<h3>Registros que serão removidos:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Matrícula</th><th>Status</th></tr>";
    
    $sql_list = "SELECT * FROM tecnicos WHERE id = 0";
    $result_list = $conn->query($sql_list);
    
    while ($row = $result_list->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['matricula']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status_tecnico']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verifica se há chamados vinculados a esses técnicos
    $sql_chamados = "SELECT COUNT(*) as total FROM chamados WHERE tecnico_id = 0";
    $result_chamados = $conn->query($sql_chamados);
    $total_chamados = $result_chamados->fetch_assoc()['total'];
    
    if ($total_chamados > 0) {
        echo "<p style='color: red;'><strong>ATENÇÃO:</strong> Existem {$total_chamados} chamado(s) vinculados a técnicos com ID = 0.</p>";
        echo "<p>Esses chamados ficarão sem técnico atribuído (tecnico_id = NULL).</p>";
        
        // Desvincula os chamados primeiro
        $sql_update_chamados = "UPDATE chamados SET tecnico_id = NULL WHERE tecnico_id = 0";
        if ($conn->query($sql_update_chamados)) {
            echo "<p style='color: green;'>✓ Chamados desvinculados com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>✗ Erro ao desvincular chamados: " . $conn->error . "</p>";
        }
    }
    
    // Agora deleta os técnicos com id = 0
    $sql_delete = "DELETE FROM tecnicos WHERE id = 0";
    
    if ($conn->query($sql_delete)) {
        $linhas_afetadas = $conn->affected_rows;
        echo "<h3 style='color: green;'>✓ Limpeza concluída com sucesso!</h3>";
        echo "<p>{$linhas_afetadas} técnico(s) removido(s) da tabela.</p>";
    } else {
        echo "<h3 style='color: red;'>✗ Erro ao executar a limpeza:</h3>";
        echo "<p>" . $conn->error . "</p>";
    }
    
} else {
    echo "<p style='color: green;'>✓ Nenhum registro com ID = 0 encontrado. Banco de dados OK!</p>";
}

$conn->close();

echo "<hr>";
echo "<p><a href='../admin/dashboard.php'>← Voltar ao Dashboard</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2 {
    color: #333;
    border-bottom: 3px solid #007bff;
    padding-bottom: 10px;
}
table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    margin: 20px 0;
}
th {
    background: #007bff;
    color: white;
    padding: 10px;
}
td {
    padding: 10px;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
</style>
```

**Como usar:**

1. **Salve esse código** como `limpar_tecnicos_id_zero.php` na pasta `/admin/`

2. **Acesse pelo navegador**: 
```
   http://seusite.com/admin/limpar_tecnicos_id_zero.php