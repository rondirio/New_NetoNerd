<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atendimento - NetoNerd Soluções Digitais</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  
  <style>
    .page-header {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      padding: 100px 0 80px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .page-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
      background-size: 50px 50px;
    }
    
    .page-header-content {
      position: relative;
      z-index: 1;
    }
    
    .page-header h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 20px;
    }
    
    .page-header p {
      font-size: 1.3rem;
      opacity: 0.95;
    }
    
    .intro-section {
      padding: 60px 0;
      background: white;
    }
    
    .intro-card {
      background: #f8f9fa;
      padding: 40px;
      border-radius: 15px;
      border-left: 5px solid #007bff;
      margin-bottom: 30px;
    }
    
    .intro-card h4 {
      color: #007bff;
      font-weight: 700;
      margin-bottom: 20px;
    }
    
    .modalidades-section {
      padding: 80px 0;
      background: #f8f9fa;
    }
    
    .modalidade-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      transition: all 0.4s ease;
      height: 100%;
      border-top: 5px solid;
      position: relative;
    }
    
    .modalidade-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .modalidade-verde {
      border-top-color: #28a745;
    }
    
    .modalidade-amarelo {
      border-top-color: #ffc107;
    }
    
    .modalidade-vermelho {
      border-top-color: #dc3545;
    }
    
    .modalidade-preto {
      border-top-color: #343a40;
    }
    
    .modalidade-header {
      padding: 30px;
      text-align: center;
    }
    
    .modalidade-icon {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 3rem;
      color: white;
    }
    
    .icon-verde {
      background: linear-gradient(135deg, #28a745, #1e7e34);
    }
    
    .icon-amarelo {
      background: linear-gradient(135deg, #ffc107, #e0a800);
    }
    
    .icon-vermelho {
      background: linear-gradient(135deg, #dc3545, #c82333);
    }
    
    .icon-preto {
      background: linear-gradient(135deg, #343a40, #23272b);
    }
    
    .modalidade-titulo {
      font-size: 1.8rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 10px;
    }
    
    .modalidade-tag {
      display: inline-block;
      padding: 5px 20px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      color: white;
      margin-bottom: 20px;
    }
    
    .tag-verde { background: #28a745; }
    .tag-amarelo { background: #ffc107; color: #333; }
    .tag-vermelho { background: #dc3545; }
    .tag-preto { background: #343a40; }
    
    .modalidade-body {
      padding: 0 30px 30px;
    }
    
    .modalidade-descricao {
      color: #555;
      line-height: 1.8;
      margin-bottom: 25px;
    }
    
    .caracteristicas-list {
      list-style: none;
      padding: 0;
      margin-bottom: 25px;
    }
    
    .caracteristicas-list li {
      padding: 12px 0;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      align-items: center;
    }
    
    .caracteristicas-list li:last-child {
      border-bottom: none;
    }
    
    .caracteristicas-list i {
      margin-right: 12px;
      font-size: 1.2rem;
    }
    
    .icon-check { color: #28a745; }
    .icon-time { color: #ffc107; }
    .icon-tool { color: #007bff; }
    
    .info-box {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      border-left: 4px solid #007bff;
      margin-bottom: 20px;
    }
    
    .info-box-icon {
      font-size: 2rem;
      color: #007bff;
      margin-bottom: 10px;
    }
    
    .processo-section {
      padding: 80px 0;
      background: white;
    }
    
    .processo-step {
      text-align: center;
      padding: 30px;
      position: relative;
    }
    
    .processo-numero {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
      box-shadow: 0 5px 20px rgba(0,123,255,0.3);
    }
    
    .processo-titulo {
      font-size: 1.2rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 10px;
    }
    
    .processo-desc {
      color: #666;
      line-height: 1.6;
    }
    
    .empresas-section {
      padding: 80px 0;
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
    }
    
    .empresa-card {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      padding: 40px;
      text-align: center;
      border: 2px solid rgba(255,255,255,0.2);
      height: 100%;
    }
    
    .empresa-icon {
      font-size: 3rem;
      margin-bottom: 20px;
    }
    
    .empresa-titulo {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    .cta-section {
      padding: 80px 0;
      background: white;
      text-align: center;
    }
    
    .cta-card {
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 60px 40px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0,123,255,0.3);
    }
    
    .cta-card h2 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 20px;
    }
    
    .cta-card p {
      font-size: 1.2rem;
      margin-bottom: 30px;
      opacity: 0.95;
    }
    .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
        }
    
    .btn-cta {
      background: white;
      color: #007bff;
      padding: 15px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      text-decoration: none;
      display: inline-block;
      margin: 10px;
      transition: all 0.3s ease;
    }
    
    .btn-cta:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      color: #007bff;
      text-decoration: none;
    }
    
    @media (max-width: 768px) {
      .page-header h1 {
        font-size: 2rem;
      }
      
      .modalidade-card {
        margin-bottom: 30px;
      }
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
          <li class="nav-item"><a class="nav-link active" href="atendimento.php">Atendimento</a></li>
          <li class="nav-item"><a class="nav-link" href="planos.php">Planos</a></li>
          <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
          <li class="nav-item"><a class="nav-link" href="quemsomo.php">Sobre</a></li>
          <li class="nav-item"><a class="nav-link btn btn-primary text-white ml-2" href="login.php">Entrar</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-content container">
      <h1><i class="fas fa-tools"></i> Modalidades de Atendimento</h1>
      <p>Oferecemos diferentes tipos de suporte para atender suas necessidades</p>
    </div>
  </div>

  <!-- Introdução -->
  <section class="intro-section">
    <div class="container">
      <div class="intro-card">
        <h4><i class="fas fa-info-circle"></i> Como Funcionam Nossos Atendimentos</h4>
        <p class="mb-0">
          Na NetoNerd, priorizamos sua <strong>comodidade e eficiência</strong>. Dependendo da complexidade do problema, 
          realizamos o atendimento na sua residência/empresa ou, quando necessário, levamos o equipamento 
          para nosso laboratório técnico. Cada atendimento é classificado por uma <strong>cor que indica 
          o nível de complexidade e o procedimento</strong> a ser seguido.
        </p>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="info-box">
            <div class="info-box-icon">
              <i class="fas fa-home"></i>
            </div>
            <h5><strong>Atendimento Presencial</strong></h5>
            <p class="mb-0">
              Nosso técnico vai até você com todas as ferramentas necessárias. 
              Ideal para problemas que podem ser resolvidos no local.
            </p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="info-box">
            <div class="info-box-icon">
              <i class="fas fa-laptop"></i>
            </div>
            <h5><strong>Laboratório Técnico</strong></h5>
            <p class="mb-0">
              Para problemas complexos, levamos o equipamento para análise detalhada 
              e orçamento prévio antes de qualquer intervenção.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Modalidades -->
  <section class="modalidades-section">
    <div class="container">
      <h2 class="section-title">Modalidades de Atendimento</h2>
      
      <div class="row">
        <!-- Verde -->
        <div class="col-lg-6 mb-4">
          <div class="modalidade-card modalidade-verde">
            <div class="modalidade-header">
              <div class="modalidade-icon icon-verde">
                <i class="fas fa-check-circle"></i>
              </div>
              <h3 class="modalidade-titulo">🟢 Atendimento Verde</h3>
              <span class="modalidade-tag tag-verde">Rápido e Simples</span>
            </div>
            <div class="modalidade-body">
              <p class="modalidade-descricao">
                Problemas simples que podem ser resolvidos <strong>rapidamente no local</strong>. 
                O técnico soluciona na hora, sem necessidade de levar o equipamento.
              </p>
              
              <ul class="caracteristicas-list">
                <li>
                  <i class="fas fa-clock icon-time"></i>
                  <span><strong>Tempo:</strong> 30min a 1h</span>
                </li>
                <li>
                  <i class="fas fa-map-marker-alt icon-check"></i>
                  <span><strong>Local:</strong> Residência/Empresa</span>
                </li>
                <li>
                  <i class="fas fa-wrench icon-tool"></i>
                  <span><strong>Ferramentas:</strong> Kit básico do técnico</span>
                </li>
              </ul>

              <div class="alert alert-success mb-0">
                <strong>Exemplos:</strong>
                <ul class="mb-0 mt-2">
                  <li>Instalação de programas</li>
                  <li>Configuração de impressora</li>
                  <li>Limpeza de vírus simples</li>
                  <li>Configuração de rede Wi-Fi</li>
                  <li>Backup de arquivos</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Amarelo -->
        <div class="col-lg-6 mb-4">
          <div class="modalidade-card modalidade-amarelo">
            <div class="modalidade-header">
              <div class="modalidade-icon icon-amarelo">
                <i class="fas fa-clock"></i>
              </div>
              <h3 class="modalidade-titulo">🟡 Atendimento Amarelo</h3>
              <span class="modalidade-tag tag-amarelo">Moderado</span>
            </div>
            <div class="modalidade-body">
              <p class="modalidade-descricao">
                Serviços que <strong>demandam mais tempo</strong> mas ainda podem ser realizados no local. 
                A visita pode se estender por algumas horas.
              </p>
              
              <ul class="caracteristicas-list">
                <li>
                  <i class="fas fa-clock icon-time"></i>
                  <span><strong>Tempo:</strong> 2h a 4h</span>
                </li>
                <li>
                  <i class="fas fa-map-marker-alt icon-check"></i>
                  <span><strong>Local:</strong> Residência/Empresa</span>
                </li>
                <li>
                  <i class="fas fa-wrench icon-tool"></i>
                  <span><strong>Ferramentas:</strong> Kit completo</span>
                </li>
              </ul>

              <div class="alert alert-warning mb-0">
                <strong>Exemplos:</strong>
                <ul class="mb-0 mt-2">
                  <li>Formatação completa do sistema</li>
                  <li>Instalação de múltiplos softwares</li>
                  <li>Configuração de rede empresarial</li>
                  <li>Manutenção preventiva completa</li>
                  <li>Migração de dados</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Vermelho -->
        <div class="col-lg-6 mb-4">
          <div class="modalidade-card modalidade-vermelho">
            <div class="modalidade-header">
              <div class="modalidade-icon icon-vermelho">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <h3 class="modalidade-titulo">🔴 Atendimento Vermelho</h3>
              <span class="modalidade-tag tag-vermelho">Laboratório Técnico</span>
            </div>
            <div class="modalidade-body">
              <p class="modalidade-descricao">
                Problemas que <strong>não podem ser resolvidos no local</strong>. O equipamento 
                precisa ser levado ao laboratório para diagnóstico e orçamento detalhado.
              </p>
              
              <ul class="caracteristicas-list">
                <li>
                  <i class="fas fa-clock icon-time"></i>
                  <span><strong>Prazo:</strong> 2 a 5 dias úteis</span>
                </li>
                <li>
                  <i class="fas fa-building icon-check"></i>
                  <span><strong>Local:</strong> Laboratório NetoNerd</span>
                </li>
                <li>
                  <i class="fas fa-search icon-tool"></i>
                  <span><strong>Processo:</strong> Diagnóstico + Orçamento</span>
                </li>
              </ul>

              <div class="alert alert-danger mb-0">
                <strong>Exemplos:</strong>
                <ul class="mb-0 mt-2">
                  <li>Reparo de hardware</li>
                  <li>Recuperação de dados</li>
                  <li>Problemas de placa-mãe</li>
                  <li>Troca de componentes internos</li>
                  <li>Análise de falha crítica</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Preto -->
        <div class="col-lg-6 mb-4">
          <div class="modalidade-card modalidade-preto">
            <div class="modalidade-header">
              <div class="modalidade-icon icon-preto">
                <i class="fas fa-sync-alt"></i>
              </div>
              <h3 class="modalidade-titulo">⚫ Atendimento Preto</h3>
              <span class="modalidade-tag tag-preto">Substituição</span>
            </div>
            <div class="modalidade-body">
              <p class="modalidade-descricao">
                Quando o equipamento ou componente apresenta <strong>defeito irreparável</strong> 
                ou o reparo não é viável. Necessário substituição completa.
              </p>
              
              <ul class="caracteristicas-list">
                <li>
                  <i class="fas fa-ban icon-time"></i>
                  <span><strong>Situação:</strong> Defeito irreparável</span>
                </li>
                <li>
                  <i class="fas fa-exchange-alt icon-check"></i>
                  <span><strong>Solução:</strong> Substituição necessária</span>
                </li>
                <li>
                  <i class="fas fa-calculator icon-tool"></i>
                  <span><strong>Orçamento:</strong> Peça nova + Instalação</span>
                </li>
              </ul>

              <div class="alert alert-dark mb-0">
                <strong>Exemplos:</strong>
                <ul class="mb-0 mt-2">
                  <li>HD/SSD queimado sem recuperação</li>
                  <li>Fonte de alimentação queimada</li>
                  <li>Placa-mãe com curto-circuito</li>
                  <li>Tela de notebook quebrada</li>
                  <li>Memória RAM defeituosa</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Processo de Atendimento -->
  <section class="processo-section">
    <div class="container">
      <h2 class="section-title">Como Funciona o Processo</h2>
      <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
          <div class="processo-step">
            <div class="processo-numero">1</div>
            <h5 class="processo-titulo">Solicitação</h5>
            <p class="processo-desc">
              Entre em contato via WhatsApp, telefone ou abra uma ordem de serviço online
            </p>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
          <div class="processo-step">
            <div class="processo-numero">2</div>
            <h5 class="processo-titulo">Diagnóstico</h5>
            <p class="processo-desc">
              Nosso técnico avalia o problema e define a modalidade de atendimento
            </p>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
          <div class="processo-step">
            <div class="processo-numero">3</div>
            <h5 class="processo-titulo">Orçamento</h5>
            <p class="processo-desc">
              Você recebe o orçamento detalhado antes de qualquer serviço ser realizado
            </p>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
          <div class="processo-step">
            <div class="processo-numero">4</div>
            <h5 class="processo-titulo">Execução</h5>
            <p class="processo-desc">
              Após aprovação, realizamos o serviço com garantia de qualidade
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Atendimento Empresarial -->
  <section class="empresas-section">
    <div class="container">
      <h2 class="section-title" style="color: white;">Atendimento Empresarial</h2>
      <p class="text-center mb-5" style="font-size: 1.2rem; opacity: 0.95;">
        Oferecemos serviços especializados para empresas com suporte diferenciado
      </p>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="empresa-card">
            <div class="empresa-icon">
              <i class="fas fa-building"></i>
            </div>
            <h4 class="empresa-titulo">Suporte On-Site</h4>
            <p>Técnicos dedicados para atendimento na sua empresa com prioridade máxima</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="empresa-card">
            <div class="empresa-icon">
              <i class="fas fa-server"></i>
            </div>
            <h4 class="empresa-titulo">Gestão de Infraestrutura</h4>
            <p>Gerenciamento completo da sua infraestrutura de TI com monitoramento 24/7</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="empresa-card">
            <div class="empresa-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h4 class="empresa-titulo">Segurança Corporativa</h4>
            <p>Soluções de segurança, backup e recuperação de desastres para proteger seu negócio</p>
          </div>
        </div>
      </div>
      <div class="text-center mt-4">
        <a href="contato.php?produto=pj" class="btn btn-light btn-lg" style="padding: 15px 40px;">
          <i class="fas fa-briefcase"></i> Solicitar Plano Empresarial
        </a>
      </div>
    </div>
  </section>

  <!-- CTA Final -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-card">
        <h2>Precisa de Atendimento Técnico?</h2>
        <p>Nossa equipe está pronta para resolver seu problema. Entre em contato agora!</p>
        <div>
          <a href="https://wa.me/5521977395867?text=Olá,%20preciso%20de%20atendimento%20técnico" 
             class="btn-cta" target="_blank">
            <i class="fab fa-whatsapp"></i> WhatsApp
          </a>
          <a href="contato.php" class="btn-cta">
            <i class="fas fa-envelope"></i> Formulário
          </a>
          <a href="tel:+5521977395867" class="btn-cta">
            <i class="fas fa-phone"></i> (21) 97739-5867
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer bg-dark text-white py-5">
    <div class="container text-center">
      <p>&copy; 2025 NetoNerd Soluções Digitais. Todos os direitos reservados.</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Animação das modalidades ao scroll
    const modalidadeCards = document.querySelectorAll('.modalidade-card');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }, index * 150);
        }
      });
    }, { threshold: 0.1 });
    
    modalidadeCards.forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(30px)';
      card.style.transition = 'all 0.6s ease';
      observer.observe(card);
    });
  </script>
</body>
</html>