<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>NetoNerd</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../src/css/main.css">
    <style>
      .card-login {
        padding: 30px 0 0 0;
        width: 350px;
        margin: 0 auto;
      }
      .atendimento-section {
        margin-top: 50px;
      }
      .atendimento-card img {
        width: 100%;
        height: auto;
        border-radius: 10px;
      }
      .carousel-item img {
        width: 100%;
        height: auto;
        border-radius: 10px;
      }
    </style>
  </head>
  <body>
 <?php include_once('../routes/header.php')?>

  <!-- Seção - História da NetoNerd -->
  <div class="container mt-5 mb-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card border-primary">
          <div class="card-header bg-primary text-white">
            <h4>Nossa História</h4>
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-4 text-center mb-3 mb-md-0">
          <figure class="figure">
            <img src="../src/imagens/logoNetoNerd.jpg" class="figure-img img-fluid rounded" alt="Logo NetoNerd" style="max-height:140px; object-fit:contain;">
            <figcaption class="figure-caption mt-2"><strong>Produto: NetoNerd</strong></figcaption>
          </figure>
              </div>
              <div class="col-md-4 text-center mb-3 mb-md-0">
          <figure class="figure">
            <img src="../src/imagens/foto_CEO.jpeg" class="figure-img img-fluid rounded-circle" alt="Rondineli Oliveira" style="width:140px; height:140px; object-fit:cover;">
            <figcaption class="figure-caption mt-2"><strong>Rondineli Oliveira</strong><br><small class="text-muted">CEO</small></figcaption>
          </figure>
              </div>
              <div class="col-md-4">
          <p>A Neto Nerd nasceu do coração do nosso CEO Rondineli Oliveira, apaixonado por tecnologia, formado em Ciência da Computação e Data Science. Rondineli desenvolveu a NetoNerd para ser uma empresa de suporte tecnológico às pessoas idosas. No entanto, ao começar a trabalhar em Teresópolis-RJ, o projeto cresceu e alcançou até mesmo o público jovem.</p>
          <p>Com isso, ele viu a necessidade de expandir a empresa e atender pessoas e empresas. Ao observar as necessidades de algumas empresas, observou a possibilidade de ir além, assim, criou nossa linha de produtos inovadores.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Seção - Produtos NetoNerd -->
  <div class="container mt-5 mb-5">
    <h4 class="text-center mb-5">Nossos Produtos</h4>
    <div class="row">
      <div class="col-md-6 col-lg-4 mb-4">
        <a href="apresenta_myhealth/apresenta_myhealth.php" style="color: black; text-decoration: none;">
        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid #007bff;">
          <div class="card-body text-center p-3">
            <div class="mb-3" style="height:140px; overflow:hidden; border-radius:10px; display:flex; align-items:center; justify-content:center;">
              <img src="../src/imagens/Logo_MyHealth.png" alt="MyHealth" style="width:70%; height:100%; object-fit:cover; object-position:center; display:block;">
            </div>
            <!-- <h5 class="card-title">MyHealth</h5> -->
            <p class="card-text">Plataforma para gerenciamento de prontuários eletrônicos.</p>
          </div>
        </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-4 mb-4">
        <a href="apresenta_myhealth/apresenta_myhealth.php" style="color: black; text-decoration: none;">
        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid #28a745;">
          <div class="card-body text-center">
            <div class="mb-3" style="height:140px; overflow:hidden; border-radius:10px; display:flex; align-items:center; justify-content:center;">
              <img src="../src/imagens/Logo_Escritorius.png" alt="MyHealth" style="width:70%; height:100%; object-fit:cover; object-position:center; display:block;">
            </div>
            <!-- <h5 class="card-title">Escritorius</h5> -->
            <p class="card-text">Sistema de gerenciamento de escritórios de advocacia.</p>
          </div>
        </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-4 mb-4">
        <a href="apresenta_myhealth/apresenta_myhealth.php" style="color: black; text-decoration: none;">
        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid #28a745;">
          <div class="card-body text-center">
            <div class="mb-3" style="height:140px; overflow:hidden; border-radius:10px; display:flex; align-items:center; justify-content:center;">
              <img src="../src/imagens/Logo_StyleManager.png" alt="MyHealth" style="width:70%; height:100%; object-fit:cover; object-position:center; display:block;">
            </div>
            <!-- <h5 class="card-title">Escritorius</h5> -->
            <p class="card-text">Sistema de gerenciamento de escritórios de advocacia.</p>
          </div>
        </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-4 mb-4">
        <a href="apresenta_myhealth/apresenta_myhealth.php" style="color: black; text-decoration: none;">
        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid #ffc107;">
          <div class="card-body text-center">
            <div class="mb-3" style="height:140px; overflow:hidden; border-radius:10px; display:flex; align-items:center; justify-content:center;">
              <img src="../src/imagens/logoNetoNerd.jpg" alt="MyHealth" style="width:40%; height:100%; object-fit:cover; object-position:center; display:block;">
            </div>
            <h5 class="card-title">NetoNerd PJ</h5>
            <p class="card-text">Suporte tecnológico especializado para empresas com planos personalizados.</p>
          </div>
        </div>
        </a>
      </div>
    </div>
  </div>

    <div class="container">
      <!-- Preços dos Produtos -->
      <div class="mt-5 mb-4">
      <h4 class="text-center mb-4">💰 Confira nossos preços</h4>
      <div class="row">
        <!-- MyHealth (pronto para atualização caso tenha valores específicos) -->
        <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm text-center" style="border-top: 4px solid #007bff;">
          <div class="card-body">
          <h5 class="card-title">🏥 MyHealth</h5>
          <p class="card-text">Prontuários eletrônicos e gerenciamento de clínicas e hospitais.</p>
          <p class="text-info font-weight-bold">📞 Fale conosco para conhecer valores especiais</p>
          <a href="contato.php" class="btn btn-sm btn-info">Solicitar Orçamento</a>
          </div>
        </div>
        </div>

        <!-- Escritorius (advogados) -->
        <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm text-center" style="border-top: 4px solid #28a745;">
          <div class="card-body">
          <h5 class="card-title">⚖️ Escritorius</h5>
          <p class="card-text">Sistema para escritórios de advocacia.</p>
          <ul class="list-unstyled mt-3">
        <li class="mb-2"><strong>R$ 300,00</strong> <span class="badge badge-light">/mês</span></li>
        <li class="mb-2"><span class="badge badge-success">-10%</span> <strong>R$ 3.240,00</strong> <span class="badge badge-light">/ano</span></li>
        <li><span class="badge badge-success">-5%</span> <strong>R$ 1.710,00</strong> <span class="badge badge-light">/semestre</span></li>
          </ul>
          </div>
        </div>
        </div>

        <!-- Style Manager -->
        <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm text-center" style="border-top: 4px solid #ffc107;">
          <div class="card-body">
          <h5 class="card-title">💇 Style Manager</h5>
          <p class="card-text">Gestão para salões, barbearias e similares.</p>
          <ul class="list-unstyled mt-3">
        <li class="mb-2"><strong>R$ 119,90</strong> <span class="badge badge-light">/mês</span></li>
        <li class="mb-2"><strong>R$ 1.300,00</strong> <span class="badge badge-light">/ano</span></li>
        <li><strong>R$ 683,00</strong> <span class="badge badge-light">/semestre</span></li>
          </ul>
          </div>
        </div>
        </div>

        <!-- NetoNerd PJ -->
        <div class="col-md-6 col-lg-3 mb-4">
        <div class="card h-100 border-0 shadow-sm text-center" style="border-top: 4px solid #dc3545;">
          <div class="card-body">
          <h5 class="card-title">🚀 NetoNerd PJ</h5>
          <p class="card-text">Suporte tecnológico especializado para empresas.</p>
          <ul class="list-unstyled mt-3">
        <li class="mb-2"><strong>R$ 699,00</strong> <span class="badge badge-light">/mês</span></li>
        <li class="mb-2"><span class="badge badge-success">-10%</span> <strong>R$ 7.549,20</strong> <span class="badge badge-light">/ano</span></li>
        <li><span class="badge badge-success">-5%</span> <strong>R$ 3.984,30</strong> <span class="badge badge-light">/semestre</span></li>
          </ul>
          <a href="contato.php" class="btn btn-sm btn-danger mt-2">Plano Personalizado</a>
          </div>
        </div>
        </div>
      </div>
      </div></div>
    <gmpx-api-loader key="AIzaSyAn8jvObnrRaBeaYBdSG3-yBtv-pKI_Czc" solution-channel="GMP_GE_placepicker_v2">
    </gmpx-api-loader>
    <div id="place-picker-box">
      <div id="place-picker-container">
      <gmpx-place-picker placeholder="Enter an address"></gmpx-place-picker>
      </div>
    </div>
    <!-- Scripts necessários para o funcionamento do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery Completo -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> <!-- Inclui Popper.js e Bootstrap JS -->
    <?php
    include_once('../routes/footer.php')
    ?>

<!-- Scripts FontAwesome (para os ícones de redes sociais) -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>


<!-- Scripts FontAwesome (para os ícones de redes sociais) -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

  </body>
</html>
