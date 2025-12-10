<?php 
// require_once "validador_acesso.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Contato - NetoNerd</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <style>
    .contact-info {
      text-align: center;
      margin-top: 50px;
    }
    .btn-whatsapp {
      background-color: #25d366;
      color: white;
      padding: 15px;
      font-size: 1.25rem;
      width: 100%;
      border-radius: 5px;
      text-align: center;
    }
    /* Adicionando mais espaço abaixo da área de conteúdo */
    .content {
      margin-bottom: 100px;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <?php 
      include_once('../routes/header.php')
    ?>

  <!-- Conteúdo Principal -->
  <div class="container mt-5 content">
    <h4 class="d-block w-100 p-4 bg-primary text-center text-white">Contato com a NetoNerd</h4>

    <div class="contact-info">
      <h5>💬 Entre em contato conosco!</h5>
      <p>Estamos prontos para te ajudar com o que for necessário. Para agilizar seu atendimento, basta clicar no número abaixo e iniciar uma conversa no WhatsApp:</p>
      
      <a href="https://wa.me/5521977395867?text=Olá,%20quero%20mais%20informações%20sobre%20os%20serviços%20da%20NetoNerd." class="btn btn-whatsapp">
        <i class="fab fa-whatsapp"></i> Iniciar conversa no WhatsApp
      </a>
    </div>
  </div>

  <!-- Footer -->
  <?php 
      include_once('../routes/footer.php')
    ?>

  <!-- FontAwesome -->
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>

</body>
</html>
