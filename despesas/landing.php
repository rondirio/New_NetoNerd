<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gerenciamento de Despesas | NetoNerd</title>
    <meta name="description" content="Plataforma profissional e gratuita para controle financeiro pessoal e empresarial. Desenvolvido pela NetoNerd.">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #0f172a;
            --accent: #06b6d4;
            --light: #f8fafc;
            --white: #ffffff;
            --gray: #64748b;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--secondary);
            overflow-x: hidden;
        }
        
        /* Header */
        .header {
            background: var(--white);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .nav-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: var(--white);
            padding: 8rem 2rem 6rem;
            margin-top: 70px;
            text-align: center;
        }
        
        .hero-content {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            line-height: 1.8;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-large {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
        }
        
        .btn-white {
            background: var(--white);
            color: var(--primary);
        }
        
        .btn-white:hover {
            background: var(--light);
        }
        
        /* Features */
        .features {
            padding: 5rem 2rem;
            background: var(--white);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        
        .section-title p {
            font-size: 1.125rem;
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            padding: 2rem;
            background: var(--light);
            border-radius: 1rem;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: var(--white);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--secondary);
        }
        
        .feature-card p {
            color: var(--gray);
            line-height: 1.7;
        }
        
        /* Stats */
        .stats {
            background: var(--secondary);
            color: var(--white);
            padding: 4rem 2rem;
        }
        
        .stats-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }
        
        .stat-item h3 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }
        
        .stat-item p {
            font-size: 1.125rem;
            opacity: 0.9;
        }
        
        /* Technology */
        .technology {
            padding: 5rem 2rem;
            background: var(--light);
        }
        
        .tech-grid {
            max-width: 1000px;
            margin: 3rem auto 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        
        .tech-item {
            padding: 1.5rem;
            background: var(--white);
            border-radius: 0.75rem;
            font-weight: 600;
            color: var(--secondary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* CTA */
        .cta {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: var(--white);
            padding: 5rem 2rem;
            text-align: center;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .cta p {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
        }
        
        /* Footer */
        .footer {
            background: var(--secondary);
            color: var(--white);
            padding: 3rem 2rem 2rem;
            text-align: center;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        .footer p {
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }
        
        .footer-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 2rem 0 1.5rem;
        }
        
        .footer-bottom {
            font-size: 0.9rem;
            opacity: 0.7;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="logo">NetoNerd</div>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-outline">Acessar Sistema</a>
                <a href="registro.php" class="btn btn-primary">Criar Conta</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Sistema de Gerenciamento de Despesas</h1>
            <p>Plataforma profissional e gratuita para controle total de suas finanças pessoais e empresariais. Desenvolvido com tecnologia de ponta pela NetoNerd.</p>
            <div class="hero-buttons">
                <a href="registro.php" class="btn btn-white btn-large">Começar Gratuitamente</a>
                <a href="index.php" class="btn btn-outline btn-large">Fazer Login</a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features">
        <div class="section-title">
            <h2>Recursos Avançados</h2>
            <p>Ferramentas profissionais para gestão completa de suas despesas e planejamento financeiro</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">01</div>
                <h3>Controle Multi-usuário</h3>
                <p>Sistema seguro com autenticação individual. Cada usuário possui acesso exclusivo aos seus dados financeiros com isolamento completo de informações.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">02</div>
                <h3>Despesas Recorrentes</h3>
                <p>Automatize o registro de contas fixas mensais. Configure uma vez e o sistema gerará automaticamente as despesas nos meses seguintes.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">03</div>
                <h3>Parcelamento Inteligente</h3>
                <p>Gerencie compras parceladas com facilidade. O sistema cria e organiza todas as parcelas automaticamente, facilitando o acompanhamento.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">04</div>
                <h3>Relatórios Detalhados</h3>
                <p>Visualize análises completas de seus gastos por período. Gráficos informativos e estatísticas precisas para melhor tomada de decisão.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">05</div>
                <h3>Envio por Email</h3>
                <p>Receba relatórios financeiros diretamente em sua caixa de entrada. Acompanhamento profissional com resumos automáticos mensais.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">06</div>
                <h3>API REST Integrada</h3>
                <p>Integre com outros sistemas através de nossa API REST. Consulte boletos pendentes e sincronize dados de forma programática.</p>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>100%</h3>
                <p>Gratuito</p>
            </div>
            <div class="stat-item">
                <h3>Open</h3>
                <p>Source</p>
            </div>
            <div class="stat-item">
                <h3>PHP</h3>
                <p>Moderno</p>
            </div>
            <div class="stat-item">
                <h3>24/7</h3>
                <p>Disponível</p>
            </div>
        </div>
    </section>

    <!-- Technology -->
    <section class="technology">
        <div class="section-title">
            <h2>Tecnologia de Ponta</h2>
            <p>Desenvolvido com as melhores tecnologias do mercado para garantir performance, segurança e estabilidade</p>
        </div>
        <div class="tech-grid">
            <div class="tech-item">PHP 7.4+</div>
            <div class="tech-item">MySQL</div>
            <div class="tech-item">PDO</div>
            <div class="tech-item">JavaScript</div>
            <div class="tech-item">REST API</div>
            <div class="tech-item">Responsive</div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <h2>Pronto para assumir o controle?</h2>
        <p>Junte-se a milhares de usuários que já organizam suas finanças de forma profissional</p>
        <a href="registro.php" class="btn btn-white btn-large">Criar Conta Gratuita</a>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-brand">NetoNerd</div>
            <p>Sistema de Gerenciamento de Despesas</p>
            <p>Projeto de código aberto e gratuito</p>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                <p>&copy; 2026 NetoNerd. Todos os direitos reservados.</p>
                <p>Desenvolvido com excelência para a comunidade</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll para links internos
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

        // Animação no scroll
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

        document.querySelectorAll('.feature-card, .stat-item, .tech-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
