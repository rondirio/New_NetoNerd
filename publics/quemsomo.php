<?php
// quemsomo.php - Página Sobre Nós Melhorada
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sobre Nós - NetoNerd Soluções Digitais</title>
  
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
    .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
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
    
    .historia-section {
      padding: 80px 0;
      background: white;
    }
    
    .timeline {
      position: relative;
      padding: 40px 0;
    }
    
    .timeline::before {
      content: '';
      position: absolute;
      left: 50%;
      top: 0;
      bottom: 0;
      width: 3px;
      background: linear-gradient(180deg, #007bff, #0056b3);
      transform: translateX(-50%);
    }
    
    .timeline-item {
      margin-bottom: 50px;
      position: relative;
    }
    
    .timeline-content {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      position: relative;
      width: 45%;
    }
    
    .timeline-item:nth-child(odd) .timeline-content {
      margin-left: auto;
      margin-right: 55%;
    }
    
    .timeline-item:nth-child(even) .timeline-content {
      margin-left: 55%;
    }
    
    .timeline-marker {
      position: absolute;
      left: 50%;
      top: 30px;
      width: 30px;
      height: 30px;
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 50%;
      transform: translateX(-50%);
      border: 5px solid white;
      box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
      z-index: 2;
    }
    
    .timeline-year {
      display: inline-block;
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    .equipe-section {
      padding: 80px 0;
      background: #f8f9fa;
    }
    
    .membro-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      text-align: center;
      padding: 40px 30px;
    }
    
    .membro-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .membro-foto {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      margin: 0 auto 20px;
      border: 5px solid #007bff;
      box-shadow: 0 5px 20px rgba(0,123,255,0.3);
      object-fit: cover;
    }
    
    .membro-nome {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 10px;
    }
    
    .membro-cargo {
      color: #007bff;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .membro-desc {
      color: #666;
      line-height: 1.7;
    }
    
    .valores-section {
      padding: 80px 0;
      background: white;
    }
    
    .valor-card {
      text-align: center;
      padding: 40px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      height: 100%;
      transition: all 0.3s ease;
    }
    
    .valor-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .valor-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 2rem;
      color: white;
    }
    
    .valor-titulo {
      font-size: 1.3rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 15px;
    }
    
    .missao-section {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      padding: 80px 0;
      text-align: center;
    }
    
    .missao-card {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      padding: 40px;
      border-radius: 15px;
      border: 2px solid rgba(255,255,255,0.2);
    }
    
    .missao-card h3 {
      font-size: 2rem;
      margin-bottom: 20px;
      font-weight: 700;
    }
    
    .missao-card p {
      font-size: 1.2rem;
      line-height: 1.8;
      opacity: 0.95;
    }
    
    @media (max-width: 768px) {
      .timeline::before {
        left: 20px;
      }
      
      .timeline-content,
      .timeline-item:nth-child(odd) .timeline-content,
      .timeline-item:nth-child(even) .timeline-content {
        width: calc(100% - 60px);
        margin-left: 60px !important;
      }
      
      .timeline-marker {
        left: 20px;
      }
      
      .page-header h1 {
        font-size: 2rem;
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
          <li class="nav-item"><a class="nav-link" href="atendimento.php">Atendimento</a></li>
          <li class="nav-item"><a class="nav-link" href="planos.php">Planos</a></li>
          <li class="nav-item"><a class="nav-link" href="contato.php">Contato</a></li>
          <li class="nav-item"><a class="nav-link active" href="quemsomo.php">Sobre</a></li>
          <li class="nav-item"><a class="nav-link btn btn-primary text-white ml-2" href="login.php">Entrar</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-content container">
      <h1>Nossa História</h1>
      <p>Conheça a trajetória da NetoNerd e nosso compromisso com a excelência tecnológica</p>
    </div>
  </div>

  <!-- História/Timeline -->
  <section class="historia-section">
    <div class="container">
      <div class="timeline">
        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <span class="timeline-year">2020</span>
            <h4>O Começo</h4>
            <p>Rondineli Oliveira, formado em Ciência da Computação, identifica a necessidade de suporte tecnológico para pessoas idosas em Teresópolis-RJ. Nasce a ideia da NetoNerd.</p>
          </div>
        </div>

        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <span class="timeline-year">2021</span>
            <h4>Expansão</h4>
            <p>O projeto cresce além do público idoso, atendendo jovens e empresas. A Four_BA é fundada com quatro sócios em parceria com a UNIFESO.</p>
          </div>
        </div>

        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <span class="timeline-year">2022</span>
            <h4>Consolidação</h4>
            <p>NetoNerd se torna a marca principal. Rondineli assume como sócio majoritário e CEO, direcionando a empresa para soluções corporativas.</p>
          </div>
        </div>

        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <span class="timeline-year">2023</span>
            <h4>Inovação</h4>
            <p>Lançamento dos primeiros produtos próprios: MyHealth (gestão hospitalar) e Escritorius (gestão jurídica). Início do desenvolvimento de soluções SaaS.</p>
          </div>
        </div>

        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <span class="timeline-year">2024</span>
            <h4>Crescimento</h4>
            <p>Expansão da carteira de produtos com Style Manager. Mais de 500 clientes atendidos. Estabelecimento como referência em soluções tecnológicas na região.</p>
          </div>
        </div>

        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <span class="timeline-year">2025</span>
            <h4>Consolidação</h4>
            <p>Com a consolidação em Teresópolis. A expansão foi rápida para Araruama e Saquarema</p>
          </div>
        </div>

        <div class="timeline-item">
          <div class="timeline-marker"></div>
          <div class="timeline-content">
            <span class="timeline-year">2026</span>
            <h4>Região dos Lagos</h4>
            <p>Surge o primeiro escritório fisico da NetoNerd, em Araruama.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Missão, Visão e Valores -->
  <section class="missao-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="missao-card">
            <h3><i class="fas fa-bullseye"></i><br>Missão</h3>
            <p>Levar tecnologia, praticidade e confiança a pessoas e empresas, democratizando o acesso a soluções digitais de qualidade.</p>
          </div>
        </div>
        <div class="col-lg-4 mb-4">
          <div class="missao-card">
            <h3><i class="fas fa-eye"></i><br>Visão</h3>
            <p>Ser referência nacional em soluções tecnológicas, reconhecida pela excelência técnica e compromisso com o sucesso dos clientes.</p>
          </div>
        </div>
        <div class="col-lg-4 mb-4">
          <div class="missao-card">
            <h3><i class="fas fa-heart"></i><br>Valores</h3>
            <p>Ética cristã, transparência, inovação constante, respeito ao cliente e compromisso com resultados.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Equipe -->
  <section class="equipe-section">
    <div class="container">
      <h2 class="section-title">Nossa Equipe</h2>
      <div class="row">
        <div class="col-lg-4 offset-lg-2 mb-4">
          <div class="membro-card">
            <img src="../src/imagens/foto_CEO.jpeg" alt="Rondineli Oliveira" class="membro-foto">
            <h4 class="membro-nome">Rondineli Oliveira</h4>
            <div class="membro-cargo">CEO & Fundador</div>
            <p class="membro-desc">
              Cientista da Computação com especialização em Data Science. Apaixonado por tecnologia e pela missão de democratizar o acesso digital. Lidera a visão estratégica da NetoNerd.
            </p>
            <div class="mt-3">
              <a href="https://linkedin.com/in/rondineli" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fab fa-linkedin"></i> LinkedIn
              </a>
            </div>
          </div>
        </div>
        
        <div class="col-lg-4 mb-4">
          <div class="membro-card">
            <div class="membro-foto" style="background: linear-gradient(135deg, #007bff, #0056b3); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; border: none;">
              <i class="fas fa-users"></i>
            </div>
            <h4 class="membro-nome">Equipe NetoNerd</h4>
            <div class="membro-cargo">Time de Especialistas</div>
            <p class="membro-desc">
              Nossa equipe é formada por técnicos qualificados, desenvolvedores experientes e profissionais dedicados ao suporte. Juntos, garantimos excelência em cada atendimento.
            </p>
            <div class="mt-3">
              <a href="contato.php" class="btn btn-sm btn-primary">
                <i class="fas fa-envelope"></i> Trabalhe Conosco
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Valores/Princípios -->
  <section class="valores-section">
    <div class="container">
      <h2 class="section-title">Nossos Princípios</h2>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="valor-card">
            <div class="valor-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h5 class="valor-titulo">Confiança</h5>
            <p>Construímos relacionamentos duradouros baseados em transparência e honestidade com nossos clientes.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="valor-card">
            <div class="valor-icon">
              <i class="fas fa-lightbulb"></i>
            </div>
            <h5 class="valor-titulo">Inovação</h5>
            <p>Buscamos constantemente novas tecnologias e soluções para manter nossos clientes na vanguarda digital.</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="valor-card">
            <div class="valor-icon">
              <i class="fas fa-hands-helping"></i>
            </div>
            <h5 class="valor-titulo">Compromisso</h5>
            <p>Dedicação total ao sucesso de cada cliente. Seu problema é nosso problema, sua vitória é nossa vitória.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="missao-section">
    <div class="container text-center">
      <h2 class="mb-4">Faça Parte Dessa História</h2>
      <p class="mb-5" style="font-size: 1.2rem;">Venha conhecer nossas soluções e descubra como podemos ajudar você ou sua empresa.</p>
      <a href="contato.php" class="btn btn-light btn-lg" style="padding: 15px 40px; font-weight: 600;">
        <i class="fas fa-comments"></i> Fale Conosco
      </a>
    </div>
  </section>

  <!-- Footer -->
  <?php include '../routes/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Animação da timeline ao scroll
    const timelineItems = document.querySelectorAll('.timeline-item');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1 });
    
    timelineItems.forEach(item => {
      item.style.opacity = '0';
      item.style.transform = 'translateY(30px)';
      item.style.transition = 'all 0.6s ease';
      observer.observe(item);
    });
  </script>
</body>
</html>