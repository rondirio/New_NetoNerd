<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Verificação de Senha</h1>";

// O hash que sabemos que está no banco para o usuário 'teste'
$hash_do_banco = '$2y$10$1ODiHUSCRsfy2wL5gJ5d.eKSgE9s6gqHMp2B7wz0L2otLgA4aNC.q';

// A senha que estamos tentando verificar
$senha_digitada = 'senha';

echo "<p><strong>Hash a ser verificado:</strong> " . htmlspecialchars($hash_do_banco) . "</p>";
echo "<p><strong>Senha a ser testada:</strong> " . htmlspecialchars($senha_digitada) . "</p>";
echo "<hr>";

// A verificação
if (password_verify($senha_digitada, $hash_do_banco)) {
    echo '<h2 style="color: green;">SUCESSO: A SENHA E O HASH CORRESPONDEM!</h2>';
} else {
    echo '<h2 style="color: red;">ERRO: A senha e o hash NÃO correspondem.</h2>';
    echo "<p>Isso pode indicar um problema com a sua instalação do PHP ou a biblioteca de criptografia.</p>";
}
?>