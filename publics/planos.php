<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos de Suporte - NetoNerd</title>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    
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
        
        .planos-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .plano-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
        }
        
        .plano-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .plano-card.destaque {
            border: 3px solid #007bff;
            box-shadow: 0 10px 40px rgba(0,123,255,0.2);
        }
        
        .badge-destaque {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 5px 15px rgba(255,193,7,0.4);
            z-index: 10;
        }
        
        .plano-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .plano-titulo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .plano-subtitulo {
            opacity: 0.95;
            font-size: 1rem;
        }
        
        .plano-preco-container {
            padding: 40px 30px;
            text-align: center;
            background: #f8f9fa;
        }
        
        .plano-preco {
            font-size: 3.5rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .plano-periodo {
            color: #666;
            font-size: 1.1rem;
        }
        
        .plano-economia {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .plano-body {
            padding: 30px;
        }
        
        .beneficios-list {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }
        
        .beneficios-list li {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: flex-start;
        }
        
        .beneficios-list li:last-child {
            border-bottom: none;
        }
        
        .beneficios-list i {
            color: #28a745;
            margin-right: 12px;
            margin-top: 3px;
            font-size: 1.2rem;
        }
        
        .btn-plano {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-plano-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            color: white;
        }
        
        .btn-plano-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,123,255,0.3);
            color: white;
        }
        
        .btn-plano-outline {
            background: white;
            border: 2px solid #007bff;
            color: #007bff;
        }
        
        .btn-plano-outline:hover {
            background: #007bff;
            color: white;
        }
        
        .comparacao-section {
            padding: 80px 0;
            background: white;
        }
        
        .table-comparacao {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .table-comparacao thead {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .table-comparacao thead th {
            padding: 20px;
            font-weight: 700;
            border: none;
        }
        
        .table-comparacao tbody td {
            padding: 20px;
            vertical-align: middle;
        }
        
        .table-comparacao tbody tr:hover {
            background: #f8f9fa;
        }
        
        .icon-sim {
            color: #28a745;
            font-size: 1.5rem;
        }
        
        .icon-nao {
            color: #dc3545;
            font-size: 1.5rem;
        }
        
        .faq-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .faq-item {
            background: white;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .faq-question {
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #2c3e50;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background: #f8f9fa;
            color: #007bff;
        }
        
        .faq-question i {
            transition: transform 0.3s ease;
        }
        
        .faq-question.active i {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 25px;
        }
        
        .faq-answer.show {
            max-height: 500px;
            padding: 0 25px 20px;
        }
        
        .faq-answer p {
            color: #666;
            line-height: 1.8;
            margin: 0;
        }
        .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
        }
        .cta-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .plano-preco {
                font-size: 2.5rem;
            }
            
            .plano-card {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img class="logo" src="../src/imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span style="color: white;">☰</span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="atendimento.php">Atendimento</a></li>
                    <li class="nav-item"><a class="nav-link active" href="planos.php">Planos</a></li>
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
            <h1><i class="fas fa-box-open"></i> Planos de Suporte Técnico</h1>
            <p>Escolha o plano ideal para garantir performance e segurança dos seus equipamentos</p>
        </div>
    </div>

    <!-- Planos -->
    <section class="planos-section">
        <div class="container">
            <div class="row">
                <!-- Plano Inicial -->
                <div class="col-lg-4 mb-4">
                    <div class="plano-card">
                        <div class="plano-header">
                            <h3 class="plano-titulo">Inicial</h3>
                            <p class="plano-subtitulo">Para uso pessoal básico</p>
                        </div>
                        <div class="plano-preco-container">
                            <div class="plano-preco">R$ 400</div>
                            <div class="plano-periodo">/mês</div>
                        </div>
                        <div class="plano-body">
                            <ul class="beneficios-list">
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>1 visita presencial</strong> por mês</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Suporte a <strong>Windows e Linux</strong></span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Diagnóstico</strong> completo</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Otimização</strong> de sistema</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Suporte via <strong>WhatsApp</strong></span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Atendimento em <strong>horário comercial</strong></span>
                                </li>
                            </ul>
                            <a href="contato.php?plano=inicial" class="btn btn-plano btn-plano-outline">
                                <i class="fas fa-shopping-cart"></i> Contratar Agora
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Plano Intermediário - DESTAQUE -->
                <div class="col-lg-4 mb-4">
                    <div class="plano-card destaque">
                        <span class="badge-destaque">⭐ Mais Popular</span>
                        <div class="plano-header">
                            <h3 class="plano-titulo">Intermediário</h3>
                            <p class="plano-subtitulo">Ideal para uso regular</p>
                        </div>
                        <div class="plano-preco-container">
                            <div class="plano-preco">R$ 500</div>
                            <div class="plano-periodo">/mês</div>
                            <span class="plano-economia">Melhor Custo-Benefício</span>
                        </div>
                        <div class="plano-body">
                            <ul class="beneficios-list">
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>2 visitas presenciais</strong> por mês</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Backup semestral</strong> gratuito</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Diagnóstico preventivo</strong></span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Treinamento</strong> para sua equipe</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Limpeza interna</strong> inclusa</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>15% desconto</strong> em peças</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Suporte <strong>prioritário</strong></span>
                                </li>
                            </ul>
                            <a href="contato.php?plano=intermediario" class="btn btn-plano btn-plano-primary">
                                <i class="fas fa-rocket"></i> Contratar Agora
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Plano Avançado -->
                <div class="col-lg-4 mb-4">
                    <div class="plano-card">
                        <div class="plano-header">
                            <h3 class="plano-titulo">Avançado</h3>
                            <p class="plano-subtitulo">Suporte completo premium</p>
                        </div>
                        <div class="plano-preco-container">
                            <div class="plano-preco">R$ 600</div>
                            <div class="plano-periodo">/mês</div>
                        </div>
                        <div class="plano-body">
                            <ul class="beneficios-list">
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>4 visitas presenciais</strong> por mês</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Backup trimestral</strong> gratuito</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Suporte <strong>24/7</strong> via WhatsApp</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Diagnóstico completo</strong> mensal</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Manutenção completa</strong> e limpeza</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>25% desconto</strong> em peças</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Consultoria</strong> personalizada</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span><strong>Atendimento emergencial</strong></span>
                                </li>
                            </ul>
                            <a href="contato.php?plano=avancado" class="btn btn-plano btn-plano-outline">
                                <i class="fas fa-crown"></i> Contratar Agora
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i> Todos os planos incluem garantia de 30 dias e sem fidelidade
                </p>
            </div>
        </div>
    </section>

    <!-- Comparação -->
    <section class="comparacao-section">
        <div class="container">
            <h2 class="section-title">Compare os Planos</h2>
            <div class="table-responsive">
                <table class="table table-comparacao">
                    <thead>
                        <tr>
                            <th>Recursos</th>
                            <th class="text-center">Inicial</th>
                            <th class="text-center">Intermediário</th>
                            <th class="text-center">Avançado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Visitas Presenciais/Mês</strong></td>
                            <td class="text-center">1</td>
                            <td class="text-center">2</td>
                            <td class="text-center">4</td>
                        </tr>
                        <tr>
                            <td><strong>Backup Gratuito</strong></td>
                            <td class="text-center"><i class="fas fa-times icon-nao"></i></td>
                            <td class="text-center">Semestral</td>
                            <td class="text-center">Trimestral</td>
                        </tr>
                        <tr>
                            <td><strong>Limpeza Interna</strong></td>
                            <td class="text-center"><i class="fas fa-times icon-nao"></i></td>
                            <td class="text-center"><i class="fas fa-check icon-sim"></i></td>
                            <td class="text-center"><i class="fas fa-check icon-sim"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Suporte 24/7</strong></td>
                            <td class="text-center"><i class="fas fa-times icon-nao"></i></td>
                            <td class="text-center"><i class="fas fa-times icon-nao"></i></td>
                            <td class="text-center"><i class="fas fa-check icon-sim"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Desconto em Peças</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-center">15%</td>
                            <td class="text-center">25%</td>
                        </tr>
                        <tr>
                            <td><strong>Treinamento</strong></td>
                            <td class="text-center"><i class="fas fa-times icon-nao"></i></td>
                            <td class="text-center"><i class="fas fa-check icon-sim"></i></td>
                            <td class="text-center"><i class="fas fa-check icon-sim"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Atendimento Emergencial</strong></td>
                            <td class="text-center"><i class="fas fa-times icon-nao"></i></td>
                            <td class="text-center"><i class="fas fa-times icon-nao"></i></td>
                            <td class="text-center"><i class="fas fa-check icon-sim"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Perguntas Frequentes</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Os planos têm fidelidade?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Não! Todos os nossos planos são sem fidelidade. Você pode cancelar a qualquer momento sem multas ou taxas adicionais.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>O que acontece se eu precisar de mais visitas no mês?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Você pode solicitar visitas adicionais pagando apenas o valor avulso. Assinantes têm 20% de desconto em serviços extras.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Vocês atendem empresas?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sim! Temos o NetoNerd PJ, um serviço especializado para empresas com planos personalizados. Entre em contato para um orçamento.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Qual a área de cobertura?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Atendemos Teresópolis e toda região serrana do Rio de Janeiro. Para outras localidades, consulte-nos.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Posso trocar de plano depois?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sim! Você pode fazer upgrade ou downgrade do seu plano a qualquer momento, sem burocracias.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2>Ainda tem dúvidas?</h2>
            <p>Fale com nossa equipe e encontre o plano perfeito para você</p>
            <a href="contato.php" class="btn btn-light btn-lg" style="padding: 15px 40px; font-weight: 600;">
                <i class="fas fa-comments"></i> Falar com Especialista
            </a>
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
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle FAQ
        function toggleFAQ(element) {
            const answer = element.nextElementSibling;
            const isOpen = answer.classList.contains('show');
            
            // Fecha todas
            document.querySelectorAll('.faq-answer').forEach(a => {
                a.classList.remove('show');
            });
            document.querySelectorAll('.faq-question').forEach(q => {
                q.classList.remove('active');
            });
            
            // Abre a clicada se estava fechada
            if (!isOpen) {
                answer.classList.add('show');
                element.classList.add('active');
            }
        }

        // Animação dos cards ao scroll
        const planoCards = document.querySelectorAll('.plano-card');
        
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
        
        planoCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>