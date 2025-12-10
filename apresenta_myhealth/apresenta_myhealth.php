<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyHealth - Revolucione o Atendimento Hospitalar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2c3e50;
            overflow-x: hidden;
            background: #f8f9fa;
        }

        .hero {
            background: linear-gradient(135deg, #0066cc 0%, #004d99 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 900px;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.95;
            animation: fadeInUp 1s ease 0.2s backwards;
        }

        .cta-button {
            display: inline-block;
            padding: 18px 50px;
            background: white;
            color: #0066cc;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: fadeInUp 1s ease 0.4s backwards;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

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

        .section {
            padding: 80px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #0066cc;
            margin-bottom: 50px;
        }

        .problem-section {
            background: #fff3cd;
            border-left: 5px solid #ff6b6b;
        }

        .problem-section .section-title {
            color: #d63031;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #555;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,102,204,0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            box-shadow: 0 15px 40px rgba(0,102,204,0.2);
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #0066cc, #004d99);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 20px;
            color: white;
        }

        .feature-title {
            font-size: 1.5rem;
            color: #0066cc;
            margin-bottom: 15px;
        }

        .feature-description {
            color: #666;
            line-height: 1.6;
        }

        .demo-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .demo-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }

        .demo-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .demo-tab {
            padding: 12px 30px;
            background: #e9ecef;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .demo-tab.active {
            background: #0066cc;
            color: white;
        }

        .demo-content {
            display: none;
        }

        .demo-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .demo-image {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .benefits-section {
            background: white;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 15px;
            border-left: 5px solid #0066cc;
        }

        .benefit-icon {
            font-size: 2rem;
            color: #0066cc;
            min-width: 50px;
        }

        .benefit-content h3 {
            color: #0066cc;
            margin-bottom: 10px;
        }

        .testimonial-section {
            background: linear-gradient(135deg, #0066cc 0%, #004d99 100%);
            color: white;
        }

        .testimonial-section .section-title {
            color: white;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .testimonial-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .testimonial-author {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .testimonial-role {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .pricing-section {
            background: white;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .pricing-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .pricing-card:hover {
            border-color: #0066cc;
            box-shadow: 0 15px 40px rgba(0,102,204,0.15);
            transform: translateY(-5px);
        }

        .pricing-card.featured {
            border-color: #0066cc;
            box-shadow: 0 15px 40px rgba(0,102,204,0.2);
            transform: scale(1.05);
        }

        .pricing-title {
            font-size: 1.5rem;
            color: #0066cc;
            margin-bottom: 15px;
        }

        .pricing-price {
            font-size: 3rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .pricing-period {
            color: #999;
            margin-bottom: 30px;
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 30px;
            text-align: left;
        }

        .pricing-features li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .pricing-features li:before {
            content: "✓";
            color: #0066cc;
            font-weight: bold;
            margin-right: 10px;
        }

        .pricing-button {
            display: block;
            width: 100%;
            padding: 15px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .pricing-button:hover {
            background: #004d99;
        }

        .cta-section {
            background: linear-gradient(135deg, #0066cc 0%, #004d99 100%);
            color: white;
            text-align: center;
            padding: 100px 20px;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .contact-button {
            display: inline-block;
            padding: 20px 60px;
            background: white;
            color: #0066cc;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .contact-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .pricing-card.featured {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>MyHealth</h1>
            <p>A solução definitiva para digitalização e acessibilidade do prontuário hospitalar que vai revolucionar o atendimento inicial em sua instituição</p>
            <a href="#demo" class="cta-button">Ver Demonstração</a>
        </div>
    </section>

    <!-- Problem Section -->
    <section class="section problem-section">
        <h2 class="section-title">O Problema Que Está Custando Vidas</h2>
        <p style="text-align: center; font-size: 1.2rem; margin-bottom: 40px;">Dados reais de profissionais de saúde revelam uma crise informacional:</p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">56,3%</div>
                <div class="stat-label">dos profissionais atendem pacientes que <strong>sempre ou quase sempre</strong> não sabem informar dados cruciais de saúde</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">87,5%</div>
                <div class="stat-label">consideram que a falta de informação afeta <strong>significativa ou criticamente</strong> a segurança do paciente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">43,8%</div>
                <div class="stat-label">apontam aumento do risco de <strong>erros de medicação</strong> como principal consequência</div>
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section class="section">
        <h2 class="section-title">A Solução: MyHealth</h2>
        <p style="text-align: center; font-size: 1.2rem; max-width: 800px; margin: 0 auto 50px;">Um prontuário eletrônico unificado, nacionalmente acessível, que coloca informações vitais nas mãos de médicos e pacientes em tempo real.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🏥</div>
                <h3 class="feature-title">Acesso Instantâneo</h3>
                <p class="feature-description">Médicos consultam dados completos do paciente em segundos usando apenas CRM e CPF. Sem burocracia, sem demora.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Segurança Total</h3>
                <p class="feature-description">Conformidade com LGPD, criptografia de dados, autenticação em dois fatores e sistema de autorização controlado pelo paciente.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3 class="feature-title">Multiplataforma</h3>
                <p class="feature-description">Funciona perfeitamente em desktops, tablets e smartphones. Acesse de qualquer lugar, a qualquer momento.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">Integração com CFM</h3>
                <p class="feature-description">Validação automática de CRM através da API do Conselho Federal de Medicina. Segurança profissional garantida.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔔</div>
                <h3 class="feature-title">Alertas Inteligentes</h3>
                <p class="feature-description">Notificações automáticas de medicação, lembretes personalizados e avisos de alterações no prontuário.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Histórico Completo</h3>
                <p class="feature-description">Alergias, medicamentos, cirurgias, doenças crônicas, tipo sanguíneo - tudo em um só lugar, sempre atualizado.</p>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section class="section demo-section" id="demo">
        <h2 class="section-title">Veja o MyHealth em Ação</h2>
        <div class="demo-container">
            <div class="demo-tabs">
                <button class="demo-tab active" onclick="showDemo('login')">Login Simplificado</button>
                <button class="demo-tab" onclick="showDemo('dashboard-medico')">Dashboard Médico</button>
                <button class="demo-tab" onclick="showDemo('consulta')">Nova Consulta</button>
                <button class="demo-tab" onclick="showDemo('dashboard-paciente')">Dashboard Paciente</button>
                <button class="demo-tab" onclick="showDemo('prontuario')">Prontuário Completo</button>
            </div>
            
            <div id="demo-login" class="demo-content active">
                <img src="https://via.placeholder.com/1200x700/0066cc/ffffff?text=Tela+de+Login+Intuitiva" alt="Tela de Login" class="demo-image">
                <h3 style="color: #0066cc; margin-bottom: 10px;">Interface Limpa e Profissional</h3>
                <p>Design pensado para facilitar o acesso tanto para médicos quanto para pacientes, com opções claras de cadastro e recuperação de senha.</p>
            </div>
            
            <div id="demo-dashboard-medico" class="demo-content">
                <img src="https://via.placeholder.com/1200x700/0066cc/ffffff?text=Dashboard+M%C3%A9dico" alt="Dashboard Médico" class="demo-image">
                <h3 style="color: #0066cc; margin-bottom: 10px;">Painel de Controle Completo</h3>
                <p>O médico acessa rapidamente informações do paciente inserindo apenas o CPF. Sistema de busca inteligente e rápido.</p>
            </div>
            
            <div id="demo-consulta" class="demo-content">
                <img src="https://via.placeholder.com/1200x700/0066cc/ffffff?text=Registro+de+Consulta" alt="Nova Consulta" class="demo-image">
                <h3 style="color: #0066cc; margin-bottom: 10px;">Registro Eficiente de Consultas</h3>
                <p>Formulário otimizado para registro rápido de consultas, com campos inteligentes e salvamento automático.</p>
            </div>
            
            <div id="demo-dashboard-paciente" class="demo-content">
                <img src="https://via.placeholder.com/1200x700/0066cc/ffffff?text=Dashboard+Paciente" alt="Dashboard Paciente" class="demo-image">
                <h3 style="color: #0066cc; margin-bottom: 10px;">Controle Total Para o Paciente</h3>
                <p>O paciente visualiza todo seu histórico médico, autoriza acessos e gerencia suas informações de saúde com autonomia.</p>
            </div>
            
            <div id="demo-prontuario" class="demo-content">
                <img src="https://via.placeholder.com/1200x700/0066cc/ffffff?text=Prontu%C3%A1rio+Eletr%C3%B4nico" alt="Prontuário" class="demo-image">
                <h3 style="color: #0066cc; margin-bottom: 10px;">Prontuário Completo e Organizado</h3>
                <p>Todas as informações médicas em uma interface clara, com histórico de alterações e logs de acesso para máxima transparência.</p>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="section benefits-section">
        <h2 class="section-title">Benefícios Comprovados</h2>
        
        <div class="benefit-item">
            <div class="benefit-icon">⏱️</div>
            <div class="benefit-content">
                <h3>Redução no Tempo de Atendimento</h3>
                <p>Elimine a perda de tempo com questionários manuais e busca de informações. O médico acessa tudo instantaneamente.</p>
            </div>
        </div>

        <div class="benefit-item">
            <div class="benefit-icon">🎯</div>
            <div class="benefit-content">
                <h3>Decisões Clínicas Mais Precisas</h3>
                <p>Com acesso completo ao histórico do paciente, incluindo alergias e medicamentos em uso, o médico toma decisões mais seguras e assertivas.</p>
            </div>
        </div>

        <div class="benefit-item">
            <div class="benefit-icon">💊</div>
            <div class="benefit-content">
                <h3>Eliminação de Erros de Medicação</h3>
                <p>Alertas automáticos de alergias e interações medicamentosas previnem erros que podem custar vidas.</p>
            </div>
        </div>

        <div class="benefit-item">
            <div class="benefit-icon">📈</div>
            <div class="benefit-content">
                <h3>Aumento da Satisfação dos Pacientes</h3>
                <p>Pacientes sentem-se mais seguros e acolhidos quando percebem que toda sua informação de saúde está acessível e protegida.</p>
            </div>
        </div>

        <div class="benefit-item">
            <div class="benefit-icon">💰</div>
            <div class="benefit-content">
                <h3>Redução de Custos Operacionais</h3>
                <p>Menos exames desnecessários, menos erros, menos retrabalho. O resultado é economia real para a instituição.</p>
            </div>
        </div>

        <div class="benefit-item">
            <div class="benefit-icon">🏆</div>
            <div class="benefit-content">
                <h3>Conformidade Legal Total</h3>
                <p>Totalmente adequado à LGPD e Lei 13.787/2018. Sem riscos jurídicos, com segurança máxima dos dados.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section testimonial-section">
        <h2 class="section-title">O Que Profissionais Dizem</h2>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p class="testimonial-text">"Finalmente uma solução que realmente entende as necessidades do atendimento de emergência. O acesso instantâneo às informações já salvou vidas na nossa unidade."</p>
                <p class="testimonial-author">Dr. Carlos Eduardo</p>
                <p class="testimonial-role">Médico Emergencista</p>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"A interface é tão intuitiva que até pacientes idosos conseguem usar sem dificuldade. Isso democratizou o acesso à informação de saúde."</p>
                <p class="testimonial-author">Enfª Maria Silva</p>
                <p class="testimonial-role">Coordenadora de Enfermagem</p>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"Como paciente, me sinto muito mais seguro sabendo que qualquer médico que me atender terá acesso ao meu histórico completo. É empoderador."</p>
                <p class="testimonial-author">José Santos</p>
                <p class="testimonial-role">Paciente com doença crônica</p>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="section pricing-section">
        <h2 class="section-title">Planos Que Cabem no Seu Orçamento</h2>
        <div class="pricing-grid">
            <div class="pricing-card">
                <h3 class="pricing-title">Básico</h3>
                <div class="pricing-price">R$ 2.999</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>Até 500 pacientes cadastrados</li>
                    <li>5 médicos simultâneos</li>
                    <li>Suporte por e-mail</li>
                    <li>Atualizações incluídas</li>
                    <li>Backup diário</li>
                </ul>
                <a href="#contato" class="pricing-button">Solicitar Proposta</a>
            </div>

            <div class="pricing-card featured">
                <h3 class="pricing-title">Profissional</h3>
                <div class="pricing-price">R$ 5.999</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>Até 2.000 pacientes cadastrados</li>
                    <li>20 médicos simultâneos</li>
                    <li>Suporte prioritário 24/7</li>
                    <li>Personalização de interface</li>
                    <li>Treinamento da equipe incluso</li>
                    <li>Integração com sistemas existentes</li>
                </ul>
                <a href="#contato" class="pricing-button">Plano Recomendado</a>
            </div>

            <div class="pricing-card">
                <h3 class="pricing-title">Enterprise</h3>
                <div class="pricing-price">Sob consulta</div>
                <p class="pricing-period">personalizado</p>
                <ul class="pricing-features">
                    <li>Pacientes ilimitados</li>
                    <li>Médicos ilimitados</li>
                    <li>Gerente de conta dedicado</li>
                    <li>SLA garantido</li>
                    <li>Customizações completas</li>
                    <li>Integração total com sua infraestrutura</li>
                </ul>
                <a href="#contato" class="pricing-button">Falar com Consultor</a>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="cta-section" id="contato">
        <h2>Pronto Para Transformar o Atendimento Hospitalar?</h2>
        <p>Entre em contato agora e receba uma demonstração personalizada do MyHealth na sua instituição</p>
        <a href="mailto:rondi.rio@hotmail.com" class="contact-button">Agendar Demonstração</a>
        <p style="margin-top: 30px; opacity: 0.9;">Ou ligue: (21) 97739-5867</p>
    </section>

    <script>
        function showDemo(demoId) {
            // Hide all demo contents
            const contents = document.querySelectorAll('.demo-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.demo-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected demo
            document.getElementById('demo-' + demoId).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Smooth scroll
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

        // Animate stats on scroll
        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-card, .feature-card, .benefit-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>