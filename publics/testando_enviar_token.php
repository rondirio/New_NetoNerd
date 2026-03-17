<?php

print_r($_POST);
if($_POST['senha'] == 123){
    echo 'saenha validada';
    $_GLOBALDaDos = array(
        'user' => 'root',
        'database_name' => 'nome do banco',
        'pass' => 'senhaDoBanco',
    );
    print_r($_GLOBALDaDos);
}
else{
    echo 'senha invalida';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="../../../SoftwaresNetoNerd/stylemanager/config/trata_nomes_db.php" method="post">

    <label for="">Inclua o nome do banco</label>
    <input type="text" name="nome_banco" id="" required>

    <label for="">Inclua o usuario do usuário do banco</label>
    <input type="text" name="usuario_banco" id="" required>

    <label for="">Inclua a senha do banco</label>
    <input type="password" name="senha_banco" id="" required>

    <button type="submit">Enviar</button>
    </form>
</body>
</html>