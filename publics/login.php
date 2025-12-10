<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>NetoNerd</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/main.css">
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
  <?php 
      include_once('../routes/header.php')
    ?>
      <div class="row">
      <div class="card-login">
        <div class="card">
        <div class="card-header bg-primary">Login</div>
        <div class="card-body bg-white">
          <form action="valida_login.php" method="post">
          <div class="form-group">
            <input name="email" type="email" class="form-control bg-light" placeholder="E-mail" required>
          </div>
          <div class="form-group">
            <input name="senha" type="password" class="form-control bg-light" placeholder="Senha" required>
          </div>
          <?php if(isset($_GET['login']) && $_GET['login'] == 'erro'){?>
          <div class="text-danger">Usuário ou senha inválido(s)</div>
          <?php } ?>
          <?php if(isset($_GET['login']) && $_GET['login'] == 'erro2'){?>
          <div class="text-danger">Por favor, faça login antes de acessar as páginas protegidas</div>
          <?php } ?>
          <button class="btn btn-lg btn-info btn-block" type="submit">Entrar</button>
          <a class="btn btn-lg btn-outline-info btn-block" href="cadastro.php">Cadastre-se</a>
          </form>
        </div>
        </div>
      </div>
      </div>
<!-- Scripts necessários para o funcionamento do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery Completo -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script> <!-- Inclui Popper.js e Bootstrap JS -->
    <!-- Footer -->
    <?php 
      include_once('../routes/footer.php')
    ?>

<!-- Scripts FontAwesome (para os ícones de redes sociais) -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>


<!-- Scripts FontAwesome (para os ícones de redes sociais) -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

  </body>
</html>
