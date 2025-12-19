<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetoNerd - Soluções Tecnológicas Profissionais</title>
    <meta name="description" content="NetoNerd - Suporte técnico especializado, desenvolvimento de software e soluções digitais em Teresópolis-RJ">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    
    <style>
        /* Hero Section Aprimorado */
        .hero-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 50px 50px;
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.95;
            animation: fadeInUp 1s ease 0.2s backwards;
        }
        
        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s backwards;
        }
        
        .btn-hero {
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            margin: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-hero-primary {
            background: white;
            color: #007bff;
            border: none;
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255,255,255,0.3);
            color: #007bff;
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-hero-outline:hover {
            background: white;
            color: #007bff;
        }
        
        /* Stats Section */
        .stats-section {
            background: #f8f9fa;
            padding: 60px 0;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
        }
        
        /* História Section Melhorada */
        .historia-section {
            padding: 80px 0;
            background: white;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 50px;
            text-align: center;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            margin: 20px auto 0;
            border-radius: 2px;
        }
        
        .historia-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 40px;
            height: 100%;
            border-left: 5px solid #007bff;
            transition: all 0.3s ease;
        }
        
        .historia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            object-fit: cover;
            margin-bottom: 20px;
        }
        .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
        }
        .logo-empresa {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 20px;
        }
        
        /* Produtos Section */
        .produtos-section {
            background: #f8f9fa;
            padding: 80px 0;
        }
        
        .produto-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border-top: 4px solid #007bff;
        }
        
        .produto-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .produto-header {
            padding: 30px;
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .produto-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
        }
        
        .produto-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .produto-nome {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .produto-tagline {
            color: #666;
            font-size: 0.95rem;
        }
        
        .produto-body {
            padding: 30px;
        }
        
        .produto-descricao {
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .produto-preco {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .preco-destaque {
            font-size: 2rem;
            font-weight: 700;
            color: #007bff;
        }
        
        .preco-periodo {
            color: #666;
            font-size: 0.9rem;
        }
        
        .desconto-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 10px;
        }
        
        /* Diferenciais Section */
        .diferenciais-section {
            background: white;
            padding: 80px 0;
        }
        
        .diferencial-card {
            text-align: center;
            padding: 40px 20px;
            transition: all 0.3s ease;
        }
        
        .diferencial-card:hover {
            transform: translateY(-5px);
        }
        
        .diferencial-icon {
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
            box-shadow: 0 5px 20px rgba(0,123,255,0.3);
        }
        
        .diferencial-titulo {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .diferencial-desc {
            color: #666;
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta-subtitle {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.95;
        }
        
        /* Footer Melhorado */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 50px 0 20px;
        }
        
        .footer-section {
            margin-bottom: 30px;
        }
        
        .footer-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 10px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: #007bff;
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            color: rgba(255,255,255,0.6);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .btn-hero {
                display: block;
                margin: 10px 0;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Melhorada -->
    <nav class="navbar navbar-expand-lg navbar-custom bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img class="logo" src="../src/imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon" style="color: white;">☰</span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#produtos">Produtos</a>
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
                        <a class="nav-link" href="quemsomo.php">Sobre</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-primary ml-2" href="login.php">
                            <i class="fas fa-user"></i> Entrar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">Tecnologia que Transforma seu Negócio</h1>
                    <p class="hero-subtitle">
                        Soluções completas em TI: suporte técnico especializado, desenvolvimento de software e consultoria tecnológica em Teresópolis-RJ
                    </p>
                    <div class="hero-buttons">
                        <a href="contato.php" class="btn btn-hero btn-hero-primary">
                            <i class="fas fa-rocket"></i> Solicitar Orçamento
                        </a>
                        <a href="#produtos" class="btn btn-hero btn-hero-outline">
                            <i class="fas fa-eye"></i> Conhecer Produtos
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center d-none d-lg-block">
                    <img src="../src/imagens/logoNetoNerd.jpg" alt="NetoNerd" style="max-width: 300px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">300+</div>
                        <div class="stat-label">Clientes Atendidos</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfação</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">5 Anos</div>
                        <div class="stat-label">de Experiência</div>
                    </div>
                </div>
                <!-- <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Suporte Disponível</div>
                    </div>
                </div> -->
            </div>
        </div>
    </section>

    <!-- História Section -->
    <section class="historia-section">
        <div class="container">
            <h2 class="section-title">Nossa História</h2>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="historia-card text-center">
                        <img src="../src/imagens/logoNetoNerd.jpg" alt="NetoNerd" class="logo-empresa">
                        <h4>NetoNerd</h4>
                        <p class="text-muted">Marca Principal</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="historia-card text-center">
                        <img src="../src/imagens/foto_CEO.jpeg" alt="Rondineli Oliveira" class="profile-img">
                        <h4>Rondineli Oliveira</h4>
                        <p class="text-muted">CEO & Fundador</p>
                        <p class="small">Cientista da Computação<br>Especialista em Data Science</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="historia-card">
                        <h4 class="mb-3">Nossa Missão</h4>
                        <p>A NetoNerd nasceu do coração de Rondineli Oliveira, apaixonado por tecnologia. O que começou como suporte para pessoas idosas em Teresópolis-RJ evoluiu para uma empresa completa de soluções tecnológicas.</p>
                        <p class="mb-0"><strong>Nosso propósito:</strong> Levar tecnologia, praticidade e confiança a pessoas e empresas, sempre com valores cristãos e compromisso com a excelência.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Produtos Section -->
    <section class="produtos-section" id="produtos">
        <div class="container">
            <h2 class="section-title">Nossos Produtos</h2>
            <div class="row">
                <!-- MyHealth -->
                <div class="col-lg-6 mb-4">
                    <div class="produto-card">
                        <div class="produto-header">
                            <div class="produto-icon">
                                <img src="../src/imagens/Logo_MyHealth.png" alt="MyHealth">
                            </div>
                            <div class="produto-tagline">Prontuário Eletrônico Nacional</div>
                        </div>
                        <div class="produto-body">
                            <p class="produto-descricao">
                                <strong>Sistema completo de gestão hospitalar</strong> com prontuário eletrônico unificado. Hospitais, clínicas e médicos acessam o histórico completo do paciente instantaneamente, garantindo atendimento mais seguro.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success"></i> Acesso nacional ao histórico</li>
                                <li><i class="fas fa-check text-success"></i> 100% compatível com LGPD</li>
                                <li><i class="fas fa-check text-success"></i> Integração com CFM</li>
                                <li><i class="fas fa-check text-success"></i> App para médicos e pacientes</li>
                            </ul>
                            <div class="produto-preco">
                                <div class="preco-destaque">Sob Consulta</div>
                                <small class="text-muted">Planos personalizados para sua instituição</small>
                            </div>
                            <a href="../apresenta_myhealth/apresenta_myhealth.php" class="btn btn-primary btn-block">
                                <i class="fas fa-info-circle"></i> Ver Detalhes
                            </a>
                            <a href="contato.php?produto=myhealth" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-phone"></i> Solicitar Demonstração
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Escritorius -->
                <div class="col-lg-6 mb-4">
                    <div class="produto-card">
                        <div class="produto-header">
                            <div class="produto-icon">
                                <img src="../src/imagens/Logo_Escritorius.png" alt="Escritorius">
                            </div>
                            <div class="produto-tagline">Gestão para Escritórios de Advocacia</div>
                        </div>
                        <div class="produto-body">
                            <p class="produto-descricao">
                                <strong>Plataforma completa para advogados</strong> que gerencia processos, prazos, clientes, documentos e financeiro. Tudo integrado em uma interface intuitiva.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success"></i> Gestão de processos e prazos</li>
                                <li><i class="fas fa-check text-success"></i> Geração automática de documentos</li>
                                <li><i class="fas fa-check text-success"></i> Controle financeiro integrado</li>
                                <li><i class="fas fa-check text-success"></i> App mobile incluso</li>
                            </ul>
                            <div class="produto-preco">
                                <div class="preco-destaque">
                                    R$ 300<span class="preco-periodo">/mês</span>
                                </div>
                                <small>
                                    <strong>Anual:</strong> R$ 3.240 <span class="desconto-badge">-10%</span><br>
                                    <strong>Semestral:</strong> R$ 1.710 <span class="desconto-badge">-5%</span>
                                </small>
                            </div>
                            <a href="produtos.php?id=escritorius" class="btn btn-primary btn-block">
                                <i class="fas fa-shopping-cart"></i> Contratar Agora
                            </a>
                            <a href="produtos.php?id=escritorius#demo" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-play"></i> Testar Grátis (7 dias)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Style Manager -->
                <div class="col-lg-6 mb-4">
                    <div class="produto-card">
                        <div class="produto-header">
                            <div class="produto-icon">
                                <img src="../src/imagens/Logo_StyleManager.png" alt="Style Manager">
                            </div>
                            <div class="produto-tagline">Gestão para Salões e Barbearias</div>
                        </div>
                        <div class="produto-body">
                            <p class="produto-descricao">
                                <strong>Sistema completo para salões de beleza</strong> que gerencia agendamentos, estoque, comissões e programa de fidelidade. Integração com WhatsApp.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success"></i> Agendamento online automático</li>
                                <li><i class="fas fa-check text-success"></i> Controle de estoque</li>
                                <li><i class="fas fa-check text-success"></i> Gestão de comissões</li>
                                <li><i class="fas fa-check text-success"></i> WhatsApp integrado</li>
                            </ul>
                            <div class="produto-preco">
                                <div class="preco-destaque">
                                    R$ 119,90<span class="preco-periodo">/mês</span>
                                </div>
                                <small>
                                    <strong>Anual:</strong> R$ 1.300<br>
                                    <strong>Semestral:</strong> R$ 683
                                </small>
                            </div>
                            <a href="produtos.php?id=stylemanager" class="btn btn-primary btn-block">
                                <i class="fas fa-shopping-cart"></i> Contratar Agora
                            </a>
                            <a href="produtos.php?id=stylemanager#demo" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-play"></i> Testar Grátis (14 dias)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- NetoNerd PJ -->
                <div class="col-lg-6 mb-4">
                    <div class="produto-card">
                        <div class="produto-header">
                            <div class="produto-icon">
                                <img src="../src/imagens/logoNetoNerd.jpg" alt="NetoNerd PJ" style="border-radius: 50%;">
                            </div>
                            <div class="produto-tagline">Suporte Tecnológico Empresarial</div>
                        </div>
                        <div class="produto-body">
                            <p class="produto-descricao">
                                <strong>Suporte técnico completo para empresas</strong> com infraestrutura, segurança, backup e atendimento prioritário. Seu time de TI terceirizado.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="fas fa-check text-success"></i> Suporte técnico 24/7</li>
                                <li><i class="fas fa-check text-success"></i> Gestão de infraestrutura</li>
                                <li><i class="fas fa-check text-success"></i> Backup em nuvem</li>
                                <li><i class="fas fa-check text-success"></i> Gerente de conta dedicado</li>
                            </ul>
                            <div class="produto-preco">
                                <div class="preco-destaque">
                                    R$ 699<span class="preco-periodo">/mês</span>
                                </div>
                                <small>
                                    <strong>Anual:</strong> R$ 7.549 <span class="desconto-badge">-10%</span><br>
                                    <strong>Semestral:</strong> R$ 3.984 <span class="desconto-badge">-5%</span>
                                </small>
                            </div>
                            <a href="contato.php?produto=pj" class="btn btn-primary btn-block">
                                <i class="fas fa-phone"></i> Falar com Consultor
                            </a>
                            <a href="produtos.php?id=pj" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-file-alt"></i> Plano Personalizado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Diferenciais Section -->
    <section class="diferenciais-section">
        <div class="container">
            <h2 class="section-title">Por Que Escolher a NetoNerd?</h2>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="diferencial-card">
                        <div class="diferencial-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="diferencial-titulo">Segurança Total</h5>
                        <p class="diferencial-desc">100% compatível com LGPD. Seus dados protegidos com criptografia de ponta.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="diferencial-card">
                        <div class="diferencial-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5 class="diferencial-titulo">Suporte Dedicado</h5>
                        <p class="diferencial-desc">Equipe especializada pronta para ajudar. Atendimento ágil e humanizado.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="diferencial-card">
                        <div class="diferencial-icon">
                            <i class="fas fa-sync"></i>
                        </div>
                        <h5 class="diferencial-titulo">Sempre Atualizado</h5>
                        <p class="diferencial-desc">Novos recursos e melhorias constantes sem custos adicionais.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="diferencial-card">
                        <div class="diferencial-icon">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <h5 class="diferencial-titulo">Cloud Computing</h5>
                        <p class="diferencial-desc">Acesse de qualquer lugar. Alta disponibilidade garantida.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Pronto para Transformar seu Negócio?</h2>
            <p class="cta-subtitle">Fale com nossa equipe e descubra a solução ideal para você</p>
            <a href="contato.php" class="btn btn-hero btn-hero-primary btn-lg">
                <i class="fas fa-comments"></i> Falar com Especialista
            </a>
            <a href="ordem_servico.php" class="btn btn-hero btn-hero-outline btn-lg">
                <i class="fas fa-tools"></i> Solicitar Atendimento
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">NetoNerd</h5>
                        <p>Soluções tecnológicas profissionais que transformam negócios. Suporte, desenvolvimento e consultoria em TI.</p>
                        <div class="social-links mt-3">
                            <a href="https://facebook.com/netonerd" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://instagram.com/netonerd" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://twitter.com/netonerd" target="_blank" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://linkedin.com/company/netonerd" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">Produtos</h5>
                        <ul class="footer-links">
                            <li><a href="produtos.php?id=myhealth">MyHealth</a></li>
                            <li><a href="produtos.php?id=escritorius">Escritorius</a></li>
                            <li><a href="produtos.php?id=stylemanager">Style Manager</a></li>
                            <li><a href="produtos.php?id=pj">NetoNerd PJ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">Empresa</h5>
                        <ul class="footer-links">
                            <li><a href="quemsomo.php">Sobre Nós</a></li>
                            <li><a href="atendimento.php">Atendimento</a></li>
                            <li><a href="planos.php">Planos</a></li>
                            <li><a href="contato.php">Contato</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">Contato</h5>
                        <ul class="footer-links">
                            <li><i class="fas fa-map-marker-alt"></i> Teresópolis - RJ</li>
                            <li><i class="fas fa-phone"></i> (21) 97739-5867</li>
                            <li><i class="fas fa-envelope"></i> netonerdinterno@gmail.com</li>
                            <li><i class="fas fa-clock"></i> Seg-Sex: 9h-18h | Sáb: 9h-13h</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Neto Nerd Soluções Digitais LTDA. Todos os direitos reservados.</p>
                <p class="mb-0">
                    <a href="termos.php" style="color: rgba(255,255,255,0.6); margin: 0 10px;">Termos de Uso</a> |
                    <a href="privacidade.php" style="color: rgba(255,255,255,0.6); margin: 0 10px;">Política de Privacidade</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scroll para links âncora
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animação de entrada dos cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Aplica animação aos cards de produto
        document.querySelectorAll('.produto-card, .diferencial-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Contador animado para estatísticas
        function animateCounter(element, target, suffix = '') {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target + suffix;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current) + suffix;
                }
            }, 30);
        }

        // Ativa contador quando seção aparece
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                    entry.target.classList.add('animated');
                    const numbers = entry.target.querySelectorAll('.stat-number');
                    numbers.forEach(num => {
                        const text = num.textContent;
                        const value = parseInt(text.replace(/\D/g, ''));
                        const suffix = text.replace(/[0-9]/g, '');
                        animateCounter(num, value, suffix);
                    });
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }
    </script>
</body>
</html>