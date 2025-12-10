<?php
require_once '../config/database.php';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("❌ Erro de conexão: " . $db->connect_error);
}

echo "✅ Conexão estabelecida com sucesso!<br>";
echo "📊 Banco de dados: " . DB_NAME . "<br>";

// Testar se tabelas existem
$tables = ['tenants', 'jwt_tokens', 'jwt_validation_logs', 'jwt_config'];
foreach ($tables as $table) {
    $result = $db->query("SHOW TABLES LIKE '$table'");
    echo $result->num_rows > 0 ? "✅ Tabela $table existe<br>" : "❌ Tabela $table não encontrada<br>";
}

$db->close();