<?php
require_once "../config/bandoDeDados/conexao.php";
$conn = getConnection();

$result = $conn->query("SELECT id, senha_hash FROM tecnicos WHERE status_tecnico = 'Ativo'");

while ($tecnico = $result->fetch_assoc()) {
    if (password_get_info($tecnico['senha_hash'])['algo'] === null) {
        // Senha em texto plano - converter
        $novo_hash = password_hash($tecnico['senha_hash'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE tecnicos SET senha_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_hash, $tecnico['id']);
        $stmt->execute();
        echo "Técnico ID {$tecnico['id']} atualizado\n";
    }
}

echo "Conversão concluída!\n";
?>