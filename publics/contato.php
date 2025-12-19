<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contato - NetoNerd Soluções Digitais</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  
  <style>
    .page-header {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      padding: 80px 0 60px;
      text-align: center;
    }
    
    .page-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    .page-header p {
      font-size: 1.2rem;
      opacity: 0.95;
    }
    
    .contato-section {
      padding: 80px 0;
    }
    
    .contact-card {
      background: white;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      height: 100%;
      transition: all 0.3s ease;
    }
    
    .contact-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .contact-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 1.5rem;
      color: white;
    }
    
    .contact-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 15px;
      text-align: center;
    }
    
    .contact-info {
      text-align: center;
      color: #666;
    }
    
    .contact-info a {
      color: #007bff;
      text-decoration: none;
      font-weight: 600;
      display: block;
      margin: 10px 0;
      transition: all 0.3s ease;
    }
    
    .contact-info a:hover {
      color: #0056b3;
      transform: translateX(5px);
    }
    
    .form-card {
      background: white;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .form-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 30px;
      text-align: center;
    }
    
    .form-group label {
      font-weight: 600;
      color: #2c3e50;
    }
    
    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 12px;
      transition: all 0.3s ease;
    }
    
    .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .btn-enviar {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border: none;
      padding: 15px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 8px;
      color: white;
      transition: all 0.3s ease;
    }
    
    .btn-enviar:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0,123,255,0.3);
      color: white;
    }
    
    .whatsapp-cta {
      background: #25d366;
      color: white;
      padding: 30px;
      border-radius: 15px;
      text-align: center;
      margin-bottom: 30px;
    }
    
    .whatsapp-cta h3 {
      margin-bottom: 15px;
      font-weight: 700;
    }
    
    .btn-whatsapp {
      background: white;
      color: #25d366;
      padding: 15px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
    }
    
    .btn-whatsapp:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      color: #25d366;
      text-decoration: none;
    }
    .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
        }
    .horario-atendimento {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      margin-top: 20px;
    }
    
    .horario-atendimento h6 {
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 15px;
    }
    
    .horario-item {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #dee2e6;
    }
    
    .horario-item:last-child {
      border-bottom: none;
    }
    
    .mapa-container {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      margin-top: 40px;
    }
    
    .mapa-container iframe {
      width: 100%;
      height: 400px;
      border: none;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-custom bg-white sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img class="logo" src="../src/imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span style="color: white;">☰</span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="atendimento.php">Atendimento</a></li>
          <li class="nav-item"><a class="nav-link" href="planos.php">Planos</a></li>
          <li class="nav-item"><a class="nav-link active" href="contato.php">Contato</a></li>
          <li class="nav-item"><a class="nav-link" href="quemsomo.php">Sobre</a></li>
          <li class="nav-item"><a class="nav-link btn btn-primary text-white ml-2" href="login.php">Entrar</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="container">
      <h1><i class="fas fa-comments"></i> Entre em Contato</h1>
      <p>Estamos prontos para te atender. Escolha a melhor forma de contato!</p>
    </div>
  </div>

  <!-- Contato Section -->
  <section class="contato-section">
    <div class="container">
      <!-- WhatsApp CTA -->
      <div class="whatsapp-cta">
        <h3><i class="fab fa-whatsapp"></i> Atendimento Rápido via WhatsApp</h3>
        <p>Fale diretamente com nossa equipe agora mesmo!</p>
        <a href="https://wa.me/5521977395867?text=Olá,%20gostaria%20de%20mais%20informações%20sobre%20os%20serviços%20da%20NetoNerd" 
           class="btn-whatsapp" target="_blank">
          <i class="fab fa-whatsapp"></i> Iniciar Conversa
        </a>
      </div>

      <div class="row mb-5">
        <!-- Telefone -->
        <div class="col-md-4 mb-4">
          <div class="contact-card">
            <div class="contact-icon">
              <i class="fas fa-phone-alt"></i>
            </div>
            <h5 class="contact-title">Telefone</h5>
            <div class="contact-info">
              <a href="tel:+5521977395867">(21) 97739-5867</a>
              <p class="text-muted mb-0">Ligação ou WhatsApp</p>
            </div>
          </div>
        </div>

        <!-- Email -->
        <div class="col-md-4 mb-4">
          <div class="contact-card">
            <div class="contact-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <h5 class="contact-title">Email</h5>
            <div class="contact-info">
              <a href="mailto:netonerdinterno@gmail.com">netonerdinterno@gmail.com</a>
              <p class="text-muted mb-0">Resposta em até 24h</p>
            </div>
          </div>
        </div>

        <!-- Localização -->
        <div class="col-md-4 mb-4">
          <div class="contact-card">
            <div class="contact-icon">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <h5 class="contact-title">Localização</h5>
            <div class="contact-info">
              <p>Teresópolis - RJ<br>Brasil</p>
              <p>Araruama - RJ<br>Brasil</p>
              <p>Saquarema - RJ<br>Brasil</p>
              <p class="text-muted mb-0">Atendemos toda região</p>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Formulário de Contato -->
        <div class="col-lg-8 mb-4">
          <div class="form-card">
            <h3 class="form-title">Envie sua Mensagem</h3>
            
            <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
              <i class="fas fa-check-circle"></i> <strong>Mensagem enviada com sucesso!</strong> 
              Entraremos em contato em breve.
              <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
              <i class="fas fa-exclamation-circle"></i> <strong>Erro ao enviar mensagem.</strong> 
              Tente novamente ou entre em contato via WhatsApp.
              <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <form action="processa_contato.php" method="POST" id="formContato">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" class="form-control" name="nome" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Email *</label>
                    <input type="email" class="form-control" name="email" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Telefone/WhatsApp *</label>
                    <input type="tel" class="form-control" name="telefone" 
                           placeholder="(21) 99999-9999" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Assunto *</label>
                    <select class="form-control" name="assunto" required>
                      <option value="">Selecione...</option>
                      <option value="Orçamento">Solicitar Orçamento</option>
                      <option value="Suporte">Suporte Técnico</option>
                      <option value="MyHealth">MyHealth - Demonstração</option>
                      <option value="Escritorius">Escritorius - Informações</option>
                      <option value="StyleManager">Style Manager - Informações</option>
                      <option value="PJ">NetoNerd PJ - Empresas</option>
                      <option value="Outro">Outro Assunto</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Localidade *</label>
                    <select class="form-control" name="localidade" required>
                      <option value="">Selecione...</option>
                      <option value="Araruama">Araruama</option>
                      <option value="Teresopolis">Teresópolis</option>
                      <option value="Saquarema">Saquarema</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Mensagem *</label>
                <textarea class="form-control" name="mensagem" rows="5" 
                          placeholder="Descreva sua necessidade ou dúvida..." required></textarea>
              </div>

              <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="aceito_contato" required>
                <label class="form-check-label" for="aceito_contato">
                  Concordo em receber contato da NetoNerd sobre minha solicitação
                </label>
              </div>

              <button type="submit" class="btn btn-enviar btn-block">
                <i class="fas fa-paper-plane"></i> Enviar Mensagem
              </button>
            </form>
          </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="col-lg-4">
          <div class="contact-card">
            <h5 class="contact-title">
              <i class="fas fa-clock"></i> Horário de Atendimento
            </h5>
            <div class="horario-atendimento">
              <div class="horario-item">
                <span>Segunda-feira</span>
                <strong>09:00 - 18:00</strong>
              </div>
              <div class="horario-item">
                <span>Terça-feira</span>
                <strong>09:00 - 18:00</strong>
              </div>
              <div class="horario-item">
                <span>Quarta-feira</span>
                <strong>09:00 - 18:00</strong>
              </div>
              <div class="horario-item">
                <span>Quinta-feira</span>
                <strong>09:00 - 18:00</strong>
              </div>
              <div class="horario-item">
                <span>Sexta-feira</span>
                <strong>09:00 - 18:00</strong>
              </div>
              <div class="horario-item">
                <span>Sábado</span>
                <strong>09:00 - 13:00</strong>
              </div>
              <div class="horario-item">
                <span>Domingo</span>
                <strong class="text-danger">Fechado</strong>
              </div>
            </div>
            <div class="alert alert-info mt-3 mb-0">
              <small><i class="fas fa-info-circle"></i> <strong>Emergências:</strong> 
              Atendimento 24h via WhatsApp para clientes com plano ativo</small>
            </div>
          </div>

          <!-- <div class="contact-card mt-4">
            <h5 class="contact-title">
              <i class="fas fa-share-alt"></i> Redes Sociais
            </h5>
            <div class="text-center">
              <a href="https://facebook.com/netonerd" target="_blank" 
                 class="btn btn-outline-primary btn-sm m-1">
                <i class="fab fa-facebook-f"></i> Facebook
              </a>
              <a href="https://instagram.com/netonerd" target="_blank" 
                 class="btn btn-outline-primary btn-sm m-1">
                <i class="fab fa-instagram"></i> Instagram
              </a>
              <a href="https://linkedin.com/company/netonerd" target="_blank" 
                 class="btn btn-outline-primary btn-sm m-1">
                <i class="fab fa-linkedin-in"></i> LinkedIn
              </a>
            </div>
          </div> -->
        </div>
      </div>

      <!-- Mapa (Opcional) -->
      <div class="mapa-container">
        <div class="p-4 text-center bg-light">
          <h4><i class="fas fa-map-marked-alt"></i> Nossa Região de Atuação</h4>
          <p class="text-muted mb-0">Atendemos Teresópolis, Araruama e Saquarema.</p>
        </div>
        <!-- Adicione aqui o iframe do Google Maps se necessário -->
      </div>
    </div>
  </section>

  <!-- Footer -->
  <?php include '../routes/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Máscara para telefone
    document.querySelector('[name="telefone"]').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length <= 11) {
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        e.target.value = value;
      }
    });

    // Validação do formulário
    document.getElementById('formContato').addEventListener('submit', function(e) {
      const nome = this.nome.value.trim();
      const email = this.email.value.trim();
      const telefone = this.telefone.value.trim();
      const mensagem = this.mensagem.value.trim();

      if (nome.length < 3) {
        e.preventDefault();
        alert('Por favor, informe seu nome completo.');
        return false;
      }

      if (mensagem.length < 10) {
        e.preventDefault();
        alert('Por favor, escreva uma mensagem mais detalhada.');
        return false;
      }
    });
  </script>
</body>
</html>