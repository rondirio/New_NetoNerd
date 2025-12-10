<?php 
// require_once "validador_acesso.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Quem Somos - NetoNerd</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <style>
    .card-info {
      padding: 30px;
      margin-bottom: 30px;
    }
    .card-header {
      background-color: #007bff;
      color: white;
      text-align: center;
      font-size: 1.5rem;
      padding: 15px;
    }
    .card-body p {
      font-size: 1rem;
      line-height: 1.6;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <?php 
      include_once('../routes/header.php')
    ?>

  <!-- Conteúdo Principal -->
  <div class="container mt-5">
    <h4 class="d-block w-100 p-4 bg-primary text-center text-white">Quem Somos</h4>

    <!-- Descrição da Empresa -->
    <div class="card card-info">
      <div class="card-header">
        <h5>Conheça a História da NetoNerd</h5>
      </div>
      <div class="card-body">
        <p>A NetoNerd tem suas raízes na cidade de Teresópolis, mais especificamente na UNIFESO, onde nasceu a Four_BA, uma empresa criada por quatro sócios. Após um ano de desenvolvimento, o sócio majoritário assumiu a liderança da empresa.</p>
        
        <p>Durante esse período, Rondineli, com sua visão e dedicação, criou o projeto NetoNerd, que logo se tornou a marca principal da empresa. Ligada à Four_BA, a NetoNerd tem como missão proporcionar <strong>independência tecnológica</strong> para todas as pessoas, com um forte compromisso com os valores cristãos.</p>

        <p>Hoje, a Four_BA já não é mais nossa identidade, pois a <strong>NetoNerd</strong> se consolidou como o nosso verdadeiro nome, representando nossa visão e nossos valores em cada atendimento e projeto que realizamos.</p>

        <p>A missão da NetoNerd é clara: <strong>Levar mais tecnologia, praticidade e confiança a cada pessoa e empresa que atendemos</strong>, sempre com respeito e comprometimento.</p>
      </div>
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
