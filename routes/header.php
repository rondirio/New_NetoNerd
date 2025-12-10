 
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../src/css/main.css">
 <nav class="navbar navbar-expand-lg navbar-custom bg-primary">
      <a class="navbar-brand" href="#">
        <img class="logo" src="../src/imagens/logoNetoNerd.jpg" alt="Logo NetoNerd" >
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse LinksNav" id="navbarNav">
      <ul class="navbar-nav ml-auto align-items-lg-center">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="produtosDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Produtos
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="produtosDropdown">
            <a class="dropdown-item" href="apresenta_myhealth/apresenta_myhealth.php">MyHealth</a>
            <a class="dropdown-item" href="escritorius.php">Escritorius</a>
            <a class="dropdown-item" href="stylemanager.php">Style Manager</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="pj.php">NetoNerd PJ</a>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="atendimento.php">Atendimento</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="planos.php">Planos</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="contato.php">Contato</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="quemsomo.php">Quem somos</a>
        </li>

        <!-- Call-to-action (shown on larger screens) -->
        <li class="nav-item d-none d-lg-block ml-3">
          <a class="btn btn-outline-light rounded" href="contato.php" title="Solicitar orçamento">Solicitar Orçamento</a>
        </li>

        <!-- Auth buttons with clear visual hierarchy -->
        <li class="nav-item ml-2">
          <a class="btn btn-light text-dark rounded" href="login.php" role="button" aria-label="Entrar">Login</a>
        </li>

        <li class="nav-item ml-2">
          <a class="btn btn-outline-dark text-white rounded" href="../tecnico/loginTecnico.php" role="button" aria-label="Acesso técnico">Técnico</a>
        </li>

        <!-- Quick contact for small screens -->
        <li class="nav-item d-lg-none mt-2">
          <a class="nav-link pl-0" href="tel:+5521XXXXXXXX" title="Ligar para suporte"><i class="fas fa-phone mr-2"></i>(21) XXXXX-XXXX</a>
        </li>
      </ul>
    </div>
  </nav>