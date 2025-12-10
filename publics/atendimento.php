<?php 
// require_once "validador_acesso.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Atendimentos - NetoNerd</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <style>
    .card-atendimento {
      padding: 30px 0 0 0;
      width: 100%;
      margin: 0 auto;
    }
    .status-verde { background-color: #28a745; color: white; }
    .status-amarelo { background-color: #ffc107; color: white; }
    .status-vermelho { background-color: #dc3545; color: white; }
    .status-preto { background-color: #000; color: white; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <?php
  include_once('../routes/header.php')
  ?>

  <!-- Conteúdo Principal -->
  <div class="container mt-5">
    <h4 class="d-block w-100 p-4 bg-primary text-center text-white border border-primary border-3">Atendimentos - Modalidades</h4>

    <!-- Descrição do Atendimento -->
    <div class="card-atendimento">
      <div class="card">
        <div class="card-header bg-light">
          Como Realizamos os Atendimentos
        </div>
        <div class="card-body">
          <p>Na Four_BA, prezamos pela comodidade e eficiência no atendimento aos nossos clientes. Dependendo da natureza do problema, podemos realizar o atendimento na residência do cliente ou, quando necessário, levar o equipamento para nosso laboratório. Abaixo estão as categorias de atendimento:</p>
          
          <!-- Modalidade Verde -->
          <div class="status-verde p-3 mb-3">
            <h5>🟢 Verde: Atendimento no Local</h5>
            <p>O problema pode ser resolvido diretamente na residência do cliente. Essa modalidade é ideal para casos simples e rápidos.</p>
          </div>
          
          <!-- Modalidade Amarelo -->
          <div class="status-amarelo p-3 mb-3">
            <h5>🟡 Amarelo: Atendimento Demorado no Local</h5>
            <p>O atendimento pode demorar um pouco mais, mas ainda assim podemos resolver na residência. A assistência será feita no local e a visita poderá ser prolongada.</p>
          </div>
          
          <!-- Modalidade Vermelho -->
          <div class="status-vermelho p-3 mb-3">
            <h5>🔴 Vermelho: Equipamento Levado para o Laboratório</h5>
            <p>Quando o problema não pode ser resolvido no local, levamos o equipamento para o laboratório para análise e orçamento. O valor da visita será cobrado.</p>
          </div>

          <!-- Modalidade Preto -->
          <div class="status-preto p-3 mb-3">
            <h5>⚫ Preto: Substituição de Equipamento</h5>
            <p>Se o equipamento ou componente estiver com defeito irreparável, será necessário substituí-lo. O valor da visita será cobrado.</p>
          </div>
          
          <p>Além dos atendimentos residenciais, atendemos também empresas, com um serviço especializado para esse tipo de cliente. Caso a sua empresa necessite de suporte, entre em contato para mais informações.</p>
        </div>
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
