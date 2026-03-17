<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyHealth — Prontuário Eletrônico Unificado para Instituições de Saúde</title>
    <meta name="description" content="MyHealth digitaliza o prontuário hospitalar e garante acesso imediato ao histórico do paciente em qualquer ponto de atendimento. Conforme LGPD e Lei 13.787/2018.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --blue:       #0066cc;
            --blue-dark:  #004d99;
            --blue-light: #e8f0fe;
            --red:        #dc2626;
            --green:      #16a34a;
            --amber:      #d97706;
            --gray-100:   #f3f4f6;
            --gray-200:   #e5e7eb;
            --gray-600:   #4b5563;
            --gray-800:   #1f2937;
            --white:      #ffffff;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: var(--gray-800);
            overflow-x: hidden;
            background: var(--white);
        }

        /* ── NAVBAR ─────────────────────────────────── */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            height: 64px;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--gray-200);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--blue);
            text-decoration: none;
        }

        .navbar-brand .dot { color: var(--blue); }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .navbar-links a {
            text-decoration: none;
            color: var(--gray-600);
            font-size: .9rem;
            font-weight: 500;
            transition: color .2s;
        }

        .navbar-links a:hover { color: var(--blue); }

        .nav-cta {
            padding: 8px 22px;
            background: var(--blue);
            color: var(--white) !important;
            border-radius: 8px;
        }

        .nav-cta:hover { background: var(--blue-dark); color: var(--white) !important; }

        /* ── HERO ────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%);
            color: var(--white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 100px 20px 60px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: -50%;
            background: radial-gradient(circle, rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: drift 25s linear infinite;
        }

        @keyframes drift {
            to { transform: translate(48px, 48px); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 860px;
        }

        .hero-badge {
            display: inline-block;
            padding: 6px 18px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 50px;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 24px;
            letter-spacing: .5px;
        }

        .hero h1 {
            font-size: clamp(2.4rem, 6vw, 4rem);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 20px;
            animation: fadeUp .9s ease both;
        }

        .hero h1 span { color: rgba(255,255,255,.75); }

        .hero p {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
            margin-bottom: 40px;
            opacity: .9;
            max-width: 680px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeUp .9s ease .15s both;
        }

        .hero-buttons {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeUp .9s ease .3s both;
        }

        .btn-primary-hero {
            padding: 16px 44px;
            background: var(--white);
            color: var(--blue);
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all .3s;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
        }

        .btn-primary-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0,0,0,.28);
        }

        .btn-outline-hero {
            padding: 15px 36px;
            border: 2px solid rgba(255,255,255,.7);
            color: var(--white);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all .3s;
        }

        .btn-outline-hero:hover {
            background: rgba(255,255,255,.12);
            border-color: var(--white);
        }

        .hero-tags {
            margin-top: 50px;
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            opacity: .8;
            font-size: .82rem;
        }

        .hero-tag {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── SECTIONS ────────────────────────────────── */
        .section { padding: 90px 20px; }
        .section-inner { max-width: 1200px; margin: 0 auto; }

        .section-label {
            display: inline-block;
            padding: 4px 14px;
            background: var(--blue-light);
            color: var(--blue);
            border-radius: 50px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .section-title {
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 800;
            color: var(--gray-800);
            margin-bottom: 14px;
        }

        .section-title .accent { color: var(--blue); }

        .section-sub {
            font-size: 1.1rem;
            color: var(--gray-600);
            max-width: 640px;
            line-height: 1.65;
        }

        /* ── PROBLEM ─────────────────────────────────── */
        .problem-section { background: #fffbf0; }

        .problem-section .section-title .accent { color: var(--red); }
        .problem-section .section-label { background: #fee2e2; color: var(--red); }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-top: 48px;
        }

        .stat-card {
            background: var(--white);
            padding: 32px 28px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,.07);
            text-align: center;
            border-top: 4px solid var(--red);
            transition: transform .3s;
        }

        .stat-card:hover { transform: translateY(-4px); }

        .stat-number {
            font-size: 3.2rem;
            font-weight: 800;
            color: var(--red);
            line-height: 1;
            margin-bottom: 12px;
        }

        .stat-label { font-size: 1rem; color: var(--gray-600); line-height: 1.5; }
        .stat-source { font-size: .75rem; color: #9ca3af; margin-top: 10px; }

        /* ── SPECIALTIES STRIP ───────────────────────── */
        .specialties-section { background: var(--gray-100); }

        .specialties-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 36px;
            justify-content: center;
        }

        .specialty-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 50px;
            font-size: .9rem;
            font-weight: 500;
            color: var(--gray-800);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            transition: all .2s;
        }

        .specialty-chip:hover {
            border-color: var(--blue);
            color: var(--blue);
            box-shadow: 0 4px 12px rgba(0,102,204,.12);
        }

        .specialty-chip .icon { font-size: 1.1rem; }

        /* ── FEATURES ────────────────────────────────── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 28px;
            margin-top: 52px;
        }

        .feature-card {
            background: var(--white);
            padding: 36px 32px;
            border-radius: 18px;
            box-shadow: 0 4px 20px rgba(0,102,204,.08);
            border: 1px solid var(--gray-200);
            transition: all .3s;
        }

        .feature-card:hover {
            box-shadow: 0 12px 36px rgba(0,102,204,.14);
            transform: translateY(-4px);
            border-color: rgba(0,102,204,.2);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--blue-light);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 10px;
        }

        .feature-desc { color: var(--gray-600); line-height: 1.6; font-size: .95rem; }

        /* ── DEMO ────────────────────────────────────── */
        .demo-section { background: var(--gray-100); }

        .demo-container {
            background: var(--white);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 16px 48px rgba(0,0,0,.08);
            margin-top: 48px;
        }

        .demo-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .demo-tab {
            padding: 10px 24px;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            cursor: pointer;
            font-size: .9rem;
            font-weight: 500;
            transition: all .25s;
            color: var(--gray-600);
        }

        .demo-tab:hover { border-color: var(--blue); color: var(--blue); }
        .demo-tab.active { background: var(--blue); color: var(--white); border-color: var(--blue); }

        .demo-content { display: none; }
        .demo-content.active { display: block; animation: fadeIn .4s ease; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .demo-screen {
            width: 100%;
            min-height: 360px;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--gray-600);
            position: relative;
            overflow: hidden;
        }

        /* Simulated screen chrome */
        .screen-chrome {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 36px;
            display: flex;
            align-items: center;
            padding: 0 14px;
            gap: 6px;
        }

        .chrome-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
        }

        .screen-body {
            width: 100%;
            height: 100%;
            padding: 50px 24px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
        }

        .screen-icon { font-size: 3.5rem; margin-bottom: 8px; }
        .screen-title { font-size: 1.2rem; font-weight: 700; }
        .screen-path { font-size: .8rem; font-family: monospace; opacity: .6; }

        .demo-desc h3 { font-size: 1.2rem; font-weight: 700; color: var(--blue); margin-bottom: 8px; }
        .demo-desc p { color: var(--gray-600); line-height: 1.6; }

        /* ── BENEFITS ────────────────────────────────── */
        .benefits-section { background: var(--white); }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 48px;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            padding: 24px;
            background: var(--gray-100);
            border-radius: 14px;
            border-left: 4px solid var(--blue);
            transition: transform .25s;
        }

        .benefit-item:hover { transform: translateX(4px); }

        .benefit-icon { font-size: 1.8rem; min-width: 40px; }

        .benefit-content h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 6px;
        }

        .benefit-content p { font-size: .9rem; color: var(--gray-600); line-height: 1.55; }

        /* ── COMPLIANCE ──────────────────────────────── */
        .compliance-section { background: var(--blue-light); }

        .compliance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }

        .compliance-card {
            background: var(--white);
            padding: 28px 24px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,102,204,.08);
        }

        .compliance-card .badge-icon { font-size: 2.4rem; margin-bottom: 14px; }
        .compliance-card h4 { font-size: 1rem; font-weight: 700; color: var(--blue); margin-bottom: 8px; }
        .compliance-card p { font-size: .85rem; color: var(--gray-600); line-height: 1.5; }

        /* ── PRICING ─────────────────────────────────── */
        .pricing-section { background: var(--white); }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
            margin-top: 52px;
            align-items: start;
        }

        .pricing-card {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 20px;
            padding: 40px 36px;
            text-align: center;
            transition: all .3s;
        }

        .pricing-card:hover {
            border-color: var(--blue);
            box-shadow: 0 12px 36px rgba(0,102,204,.12);
            transform: translateY(-4px);
        }

        .pricing-card.featured {
            border-color: var(--blue);
            box-shadow: 0 12px 40px rgba(0,102,204,.18);
            position: relative;
        }

        .pricing-badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--blue);
            color: var(--white);
            font-size: .75rem;
            font-weight: 700;
            padding: 4px 18px;
            border-radius: 50px;
            letter-spacing: .5px;
            white-space: nowrap;
        }

        .pricing-title { font-size: 1.2rem; font-weight: 700; color: var(--gray-800); margin-bottom: 8px; }
        .pricing-price { font-size: 2.8rem; font-weight: 800; color: var(--blue); line-height: 1; margin: 16px 0 4px; }
        .pricing-price span { font-size: 1.1rem; font-weight: 600; vertical-align: super; }
        .pricing-period { color: #9ca3af; font-size: .85rem; margin-bottom: 28px; }

        .pricing-features {
            list-style: none;
            margin-bottom: 32px;
            text-align: left;
        }

        .pricing-features li {
            padding: 9px 0;
            border-bottom: 1px solid var(--gray-100);
            font-size: .92rem;
            color: var(--gray-600);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .pricing-features li::before {
            content: "✓";
            color: var(--green);
            font-weight: 700;
            flex-shrink: 0;
        }

        .pricing-btn {
            display: block;
            padding: 14px;
            background: var(--blue);
            color: var(--white);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: .95rem;
            transition: background .25s;
        }

        .pricing-btn:hover { background: var(--blue-dark); }
        .pricing-btn.outline { background: transparent; color: var(--blue); border: 2px solid var(--blue); }
        .pricing-btn.outline:hover { background: var(--blue); color: var(--white); }

        /* ── CTA FINAL ───────────────────────────────── */
        .cta-section {
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%);
            color: var(--white);
            text-align: center;
            padding: 100px 20px;
        }

        .cta-section h2 { font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 800; margin-bottom: 16px; }
        .cta-section p { font-size: 1.1rem; opacity: .9; margin-bottom: 40px; max-width: 560px; margin-left: auto; margin-right: auto; }

        .cta-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

        .btn-white {
            padding: 18px 52px;
            background: var(--white);
            color: var(--blue);
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            box-shadow: 0 8px 28px rgba(0,0,0,.25);
            transition: all .3s;
        }

        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 14px 36px rgba(0,0,0,.32); }

        .btn-whatsapp {
            padding: 17px 36px;
            background: #25d366;
            color: var(--white);
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all .3s;
            box-shadow: 0 8px 24px rgba(37,211,102,.35);
        }

        .btn-whatsapp:hover { background: #1da851; transform: translateY(-2px); }

        .cta-contact { margin-top: 28px; opacity: .75; font-size: .9rem; }

        /* ── FOOTER ──────────────────────────────────── */
        footer {
            background: var(--gray-800);
            color: #d1d5db;
            padding: 48px 20px 28px;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
        }

        .footer-brand { font-size: 1.3rem; font-weight: 800; color: var(--white); margin-bottom: 12px; }
        .footer-tagline { font-size: .9rem; line-height: 1.6; color: #9ca3af; max-width: 320px; }

        .footer-col h4 { font-size: .85rem; font-weight: 700; color: var(--white); letter-spacing: .6px; text-transform: uppercase; margin-bottom: 14px; }

        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 8px; }
        .footer-col ul li a { color: #9ca3af; text-decoration: none; font-size: .88rem; transition: color .2s; }
        .footer-col ul li a:hover { color: var(--white); }

        .footer-bottom {
            max-width: 1200px;
            margin: 32px auto 0;
            padding-top: 20px;
            border-top: 1px solid #374151;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            font-size: .82rem;
            color: #6b7280;
        }

        /* ── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 768px) {
            .navbar { padding: 0 18px; }
            .navbar-links { display: none; }
            .hero { padding: 80px 16px 50px; }
            .footer-inner { grid-template-columns: 1fr; gap: 28px; }
            .pricing-card.featured { transform: none; }
        }
    </style>
</head>
<body>

<!-- ── NAVBAR ─────────────────────────────────────────── -->
<nav class="navbar">
    <a href="#inicio" class="navbar-brand">
        MyHealth<span class="dot"></span>
    </a>
    <ul class="navbar-links">
        <li><a href="#problema">O Problema</a></li>
        <li><a href="#solucao">Solução</a></li>
        <li><a href="#demo">Demonstração</a></li>
        <li><a href="#planos">Planos</a></li>
        <li><a href="#contato" class="nav-cta">Falar Conosco</a></li>
    </ul>
</nav>

<!-- ── HERO ───────────────────────────────────────────── -->
<section class="hero" id="inicio">
    <div class="hero-content">
        <div class="hero-badge">🏥 Prontuário Eletrônico Unificado</div>
        <h1>Prontuário completo.<br><span>Na hora que importa.</span></h1>
        <p>MyHealth conecta pacientes e profissionais de saúde em um histórico médico centralizado, acessível de qualquer ponto de atendimento, em tempo real.</p>
        <div class="hero-buttons">
            <a href="#demo" class="btn-primary-hero">Ver Demonstração</a>
            <a href="#contato" class="btn-outline-hero">Agendar Reunião</a>
        </div>
        <div class="hero-tags">
            <span class="hero-tag">✅ Conforme LGPD</span>
            <span class="hero-tag">✅ Lei 13.787/2018</span>
            <span class="hero-tag">✅ Validação CFM</span>
            <span class="hero-tag">✅ Multi-especialidade</span>
        </div>
    </div>
</section>

<!-- ── PROBLEMA ───────────────────────────────────────── -->
<section class="section problem-section" id="problema">
    <div class="section-inner">
        <span class="section-label">O Problema</span>
        <h2 class="section-title">A crise informacional que <span class="accent">compromete vidas</span></h2>
        <p class="section-sub">Dados coletados diretamente com profissionais de saúde revelam a dimensão do problema:</p>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">56,3%</div>
                <div class="stat-label">dos profissionais atendem pacientes que <strong>sempre ou quase sempre</strong> não sabem informar dados cruciais de saúde</div>
                <div class="stat-source">Pesquisa MyHealth · 2025</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">87,5%</div>
                <div class="stat-label">consideram que a falta de informação afeta <strong>significativa ou criticamente</strong> a segurança do paciente</div>
                <div class="stat-source">Pesquisa MyHealth · 2025</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">43,8%</div>
                <div class="stat-label">apontam aumento do risco de <strong>erros de medicação</strong> como principal consequência da falta de dados</div>
                <div class="stat-source">Pesquisa MyHealth · 2025</div>
            </div>
        </div>
    </div>
</section>

<!-- ── ESPECIALIDADES ─────────────────────────────────── -->
<section class="section specialties-section" id="solucao">
    <div class="section-inner" style="text-align:center;">
        <span class="section-label">Especialidades Suportadas</span>
        <h2 class="section-title">Uma plataforma para <span class="accent">toda a equipe</span></h2>
        <p class="section-sub" style="margin:0 auto;">MyHealth atende médicos e 11 especialidades da saúde em uma única plataforma integrada.</p>
        <div class="specialties-grid">
            <div class="specialty-chip"><span class="icon">🩺</span> Médico</div>
            <div class="specialty-chip"><span class="icon">🦷</span> Dentista</div>
            <div class="specialty-chip"><span class="icon">🧠</span> Psicólogo</div>
            <div class="specialty-chip"><span class="icon">🥗</span> Nutricionista</div>
            <div class="specialty-chip"><span class="icon">💉</span> Enfermeiro</div>
            <div class="specialty-chip"><span class="icon">🦴</span> Fisioterapeuta</div>
            <div class="specialty-chip"><span class="icon">🔊</span> Fonoaudiólogo</div>
            <div class="specialty-chip"><span class="icon">☢️</span> Radiologista</div>
            <div class="specialty-chip"><span class="icon">🔬</span> Biomédico</div>
            <div class="specialty-chip"><span class="icon">🧪</span> Técnico em Análises Clínicas</div>
            <div class="specialty-chip"><span class="icon">🩹</span> Técnico em Enfermagem</div>
            <div class="specialty-chip"><span class="icon">📡</span> Técnico em Radiologia</div>
        </div>
    </div>
</section>

<!-- ── FUNCIONALIDADES ────────────────────────────────── -->
<section class="section">
    <div class="section-inner">
        <span class="section-label">Funcionalidades</span>
        <h2 class="section-title">Tudo que sua instituição <span class="accent">precisa hoje</span></h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3 class="feature-title">Busca por CPF em segundos</h3>
                <p class="feature-desc">O médico localiza qualquer paciente cadastrado digitando apenas o CPF. Todo o histórico disponível instantaneamente.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3 class="feature-title">Prontuário eletrônico completo</h3>
                <p class="feature-desc">Alergias, medicamentos em uso, tipo sanguíneo, doenças crônicas, cirurgias, sinais vitais e histórico de consultas em um único lugar.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🚦</div>
                <h3 class="feature-title">Triagem com classificação de risco</h3>
                <p class="feature-desc">Sistema digital de triagem hospitalar com classificação por protocolo de Manchester, painel de chamadas e fila em tempo real.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3 class="feature-title">Agendamentos integrados</h3>
                <p class="feature-desc">Gestão de agenda para todos os profissionais, com controle de status, histórico de consultas e vinculação direta ao prontuário.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏛️</div>
                <h3 class="feature-title">Validação oficial CFM</h3>
                <p class="feature-desc">Cadastro de médicos com verificação automática de CRM ativo via API do Conselho Federal de Medicina. Segurança profissional garantida.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏢</div>
                <h3 class="feature-title">Administração da clínica</h3>
                <p class="feature-desc">Painel do gestor para controle de profissionais vinculados, relatórios de atendimento, configurações e gestão de assinaturas.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📺</div>
                <h3 class="feature-title">Painel de chamadas</h3>
                <p class="feature-desc">Tela de display para sala de espera com chamada de pacientes, histórico de chamadas e controle de operador em tempo real.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏗️</div>
                <h3 class="feature-title">Arquitetura multi-tenant</h3>
                <p class="feature-desc">Cada clínica opera com seus dados completamente isolados. Uma plataforma, múltiplas instituições, sem interferência entre clientes.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── DEMO ───────────────────────────────────────────── -->
<section class="section demo-section" id="demo">
    <div class="section-inner">
        <span class="section-label">Demonstração</span>
        <h2 class="section-title">Veja o <span class="accent">MyHealth</span> em ação</h2>
        <p class="section-sub">Navegue pelas principais telas do sistema.</p>

        <div class="demo-container">
            <div class="demo-tabs">
                <button class="demo-tab active" onclick="showDemo('login', event)">Login</button>
                <button class="demo-tab" onclick="showDemo('medico', event)">Dashboard Médico</button>
                <button class="demo-tab" onclick="showDemo('triagem', event)">Triagem</button>
                <button class="demo-tab" onclick="showDemo('paciente', event)">Painel do Paciente</button>
                <button class="demo-tab" onclick="showDemo('admin', event)">Admin da Clínica</button>
            </div>

            <!-- Login -->
            <div id="demo-login" class="demo-content active">
                <div class="demo-screen" style="background:#f8faff; min-height:340px;">
                    <div class="screen-chrome" style="background:#e8f0fe;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">🔐</div>
                        <div class="screen-title" style="color:#0066cc;">Login Unificado</div>
                        <div class="screen-path">/publics/login.php</div>
                        <p style="font-size:.85rem;color:#6b7280;text-align:center;max-width:300px;margin-top:8px;">Acesso separado para paciente, médico e 11 especialidades. Redirecionamento automático ao dashboard correto após autenticação.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Interface limpa e profissional</h3>
                    <p>Cada perfil de usuário tem seu fluxo de acesso dedicado. Recuperação de senha com token seguro por e-mail e prevenção de conflito de sessão ativa.</p>
                </div>
            </div>

            <!-- Dashboard médico -->
            <div id="demo-medico" class="demo-content">
                <div class="demo-screen" style="background:#f0f9ff; min-height:340px;">
                    <div class="screen-chrome" style="background:#bfdbfe;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">👨‍⚕️</div>
                        <div class="screen-title" style="color:#0066cc;">Dashboard Médico</div>
                        <div class="screen-path">/medico/dashboard_medico.php</div>
                        <p style="font-size:.85rem;color:#6b7280;text-align:center;max-width:300px;margin-top:8px;">Busca de paciente por CPF, registro de consulta, acesso ao prontuário completo e histórico de atendimentos.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Tudo ao alcance de uma busca</h3>
                    <p>O médico digita o CPF do paciente e acessa alergias, medicamentos, cirurgias, sinais vitais e histórico de consultas em segundos. Registro de novas consultas integrado ao prontuário.</p>
                </div>
            </div>

            <!-- Triagem -->
            <div id="demo-triagem" class="demo-content">
                <div class="demo-screen" style="background:#fff7ed; min-height:340px;">
                    <div class="screen-chrome" style="background:#fed7aa;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">🚦</div>
                        <div class="screen-title" style="color:#d97706;">Sistema de Triagem</div>
                        <div class="screen-path">/profissionais_saude/triagem/</div>
                        <p style="font-size:.85rem;color:#6b7280;text-align:center;max-width:340px;margin-top:8px;">Nova triagem, classificação de risco por cor, fila em tempo real e painel de chamadas para a sala de espera.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Triagem digital com classificação de risco</h3>
                    <p>Enfermeiros registram a triagem digitalmente com protocolo de classificação por cores. O painel de chamadas exibe a fila em tempo real na tela da recepção, substituindo a chamada verbal.</p>
                </div>
            </div>

            <!-- Paciente -->
            <div id="demo-paciente" class="demo-content">
                <div class="demo-screen" style="background:#f0fdf4; min-height:340px;">
                    <div class="screen-chrome" style="background:#bbf7d0;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">👤</div>
                        <div class="screen-title" style="color:#16a34a;">Dashboard do Paciente</div>
                        <div class="screen-path">/paciente/dashBoard_Paciente.php</div>
                        <p style="font-size:.85rem;color:#6b7280;text-align:center;max-width:300px;margin-top:8px;">Prontuário, alergias, metas de saúde, sinais vitais, exames e agenda — tudo visível e controlado pelo próprio paciente.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>O paciente no controle dos próprios dados</h3>
                    <p>Cada paciente visualiza e gerencia seu histórico completo: cadastra alergias, registra sinais vitais, acompanha suas metas de saúde e mantém seus dados sempre atualizados.</p>
                </div>
            </div>

            <!-- Admin -->
            <div id="demo-admin" class="demo-content">
                <div class="demo-screen" style="background:#faf5ff; min-height:340px;">
                    <div class="screen-chrome" style="background:#e9d5ff;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">🏢</div>
                        <div class="screen-title" style="color:#7c3aed;">Admin da Clínica</div>
                        <div class="screen-path">/admin_clinica/dashboard.php</div>
                        <p style="font-size:.85rem;color:#6b7280;text-align:center;max-width:340px;margin-top:8px;">Gestão de profissionais vinculados, agendamentos, relatórios, configurações da clínica e controle de assinatura.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Gestão completa para o administrador</h3>
                    <p>O gestor da instituição controla profissionais vinculados, acompanha agendamentos, acessa relatórios de atendimento e gerencia as configurações operacionais da clínica em um painel dedicado.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── BENEFÍCIOS ─────────────────────────────────────── -->
<section class="section benefits-section">
    <div class="section-inner">
        <span class="section-label">Benefícios</span>
        <h2 class="section-title">Impacto real no <span class="accent">dia a dia</span></h2>
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon">⏱️</div>
                <div class="benefit-content">
                    <h3>Menos tempo na anamnese</h3>
                    <p>O médico acessa alergias, medicamentos e histórico instantaneamente — sem questionários repetitivos no atendimento.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">🎯</div>
                <div class="benefit-content">
                    <h3>Decisões clínicas mais seguras</h3>
                    <p>Histórico completo disponível evita duplicação de exames, interações medicamentosas e diagnósticos sem contexto.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">💊</div>
                <div class="benefit-content">
                    <h3>Prevenção de erros de medicação</h3>
                    <p>Alergias e medicamentos em uso registrados e visíveis a todos os profissionais que atendem o paciente.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">📈</div>
                <div class="benefit-content">
                    <h3>Maior satisfação dos pacientes</h3>
                    <p>Pacientes que se sentem acolhidos e percebem que seu histórico está protegido e acessível avaliam melhor o atendimento.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">💰</div>
                <div class="benefit-content">
                    <h3>Redução de custos operacionais</h3>
                    <p>Triagem digital, agendamento integrado e prontuário eletrônico eliminam processos manuais e retrabalho administrativo.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">🔗</div>
                <div class="benefit-content">
                    <h3>Continuidade do cuidado</h3>
                    <p>O paciente leva seu histórico entre especialidades. Cada profissional vê o quadro completo, não apenas seu recorte.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CONFORMIDADE ───────────────────────────────────── -->
<section class="section compliance-section">
    <div class="section-inner" style="text-align:center;">
        <span class="section-label">Conformidade Legal</span>
        <h2 class="section-title">Construído dentro da <span class="accent">lei</span></h2>
        <p class="section-sub" style="margin:0 auto;">MyHealth foi projetado desde a arquitetura para atender as legislações brasileiras de dados e saúde digital.</p>
        <div class="compliance-grid">
            <div class="compliance-card">
                <div class="badge-icon">🛡️</div>
                <h4>LGPD — Lei 13.709/2018</h4>
                <p>Dados de saúde tratados como dados sensíveis. Isolamento por cliente, consentimento de uso e controle de acesso.</p>
            </div>
            <div class="compliance-card">
                <div class="badge-icon">📄</div>
                <h4>Lei 13.787/2018</h4>
                <p>Digitalização e utilização de prontuários de pacientes em conformidade com a legislação federal.</p>
            </div>
            <div class="compliance-card">
                <div class="badge-icon">🏛️</div>
                <h4>Validação CFM</h4>
                <p>Cadastro de médicos vinculado à API oficial do Conselho Federal de Medicina, com verificação de CRM ativo.</p>
            </div>
            <div class="compliance-card">
                <div class="badge-icon">🔐</div>
                <h4>Segurança de Senhas</h4>
                <p>Senhas armazenadas com hashing bcrypt. Comunicações via HTTPS em produção.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── PLANOS ─────────────────────────────────────────── -->
<section class="section pricing-section" id="planos">
    <div class="section-inner" style="text-align:center;">
        <span class="section-label">Planos</span>
        <h2 class="section-title">Planos que <span class="accent">crescem com você</span></h2>
        <p class="section-sub" style="margin:0 auto;">Todos os planos incluem atualizações, onboarding e suporte técnico. Sem taxa de adesão.</p>

        <div class="pricing-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
            <div class="pricing-card">
                <h3 class="pricing-title">Solo</h3>
                <div class="pricing-price"><span>R$</span> 79</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>1 profissional de saúde</li>
                    <li>Até 500 pacientes</li>
                    <li>Prontuário eletrônico completo</li>
                    <li>Agendamento incluso</li>
                    <li>Relatórios básicos</li>
                </ul>
                <a href="#contato" class="pricing-btn outline">Começar</a>
            </div>

            <div class="pricing-card">
                <h3 class="pricing-title">Clínica</h3>
                <div class="pricing-price"><span>R$</span> 249</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>Até 10 profissionais</li>
                    <li>Até 2.000 pacientes</li>
                    <li>Painel de chamadas incluso</li>
                    <li>Relatórios avançados</li>
                    <li>Agendamento incluso</li>
                </ul>
                <a href="#contato" class="pricing-btn outline">Solicitar Demo</a>
            </div>

            <div class="pricing-card featured">
                <div class="pricing-badge">MAIS POPULAR</div>
                <h3 class="pricing-title">Premium</h3>
                <div class="pricing-price"><span>R$</span> 599</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>Até 50 profissionais</li>
                    <li>Pacientes ilimitados</li>
                    <li>Triagem hospitalar inclusa</li>
                    <li>Painel de chamadas incluso</li>
                    <li>Relatórios avançados</li>
                    <li>Todas as 11 especialidades</li>
                </ul>
                <a href="#contato" class="pricing-btn">Plano Recomendado</a>
            </div>

            <div class="pricing-card">
                <h3 class="pricing-title">Hospital</h3>
                <div class="pricing-price"><span>R$</span> 1.499</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>Profissionais ilimitados</li>
                    <li>Pacientes ilimitados</li>
                    <li>Triagem + painel de chamadas</li>
                    <li>Acesso à API</li>
                    <li>Relatórios completos</li>
                    <li>Suporte prioritário</li>
                </ul>
                <a href="#contato" class="pricing-btn outline">Falar com Consultor</a>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA FINAL ──────────────────────────────────────── -->
<section class="cta-section" id="contato">
    <h2>Transforme o atendimento<br>da sua instituição</h2>
    <p>Agende uma demonstração personalizada e veja o MyHealth funcionando com os dados da sua clínica.</p>
    <div class="cta-buttons">
        <a href="mailto:contato@netonerd.com" class="btn-white">📧 Agendar Demonstração</a>
        <a href="https://wa.me/5521977395867" class="btn-whatsapp" target="_blank" rel="noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            WhatsApp
        </a>
    </div>
    <p class="cta-contact">(21) 97739-5867 · contato@netonerd.com</p>
</section>

<!-- ── FOOTER ─────────────────────────────────────────── -->
<footer>
    <div class="footer-inner">
        <div>
            <div class="footer-brand">MyHealth.</div>
            <p class="footer-tagline">Prontuário eletrônico unificado para instituições de saúde brasileiras. Desenvolvido pela NetoNerd.</p>
        </div>
        <div class="footer-col">
            <h4>Produto</h4>
            <ul>
                <li><a href="#solucao">Funcionalidades</a></li>
                <li><a href="#demo">Demonstração</a></li>
                <li><a href="#planos">Planos</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contato</h4>
            <ul>
                <li><a href="mailto:contato@netonerd.com">contato@netonerd.com</a></li>
                <li><a href="https://wa.me/5521977395867">(21) 97739-5867</a></li>
                <li><a href="https://github.com/rondirio/MyHealth" target="_blank" rel="noopener">GitHub</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2025 NetoNerd · MyHealth. Todos os direitos reservados.</span>
        <span>Conforme LGPD · Lei 13.787/2018</span>
    </div>
</footer>

<script>
    function showDemo(id, e) {
        document.querySelectorAll('.demo-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.demo-tab').forEach(el => el.classList.remove('active'));
        document.getElementById('demo-' + id).classList.add('active');
        if (e && e.target) e.target.classList.add('active');
    }

    // Smooth scroll para links âncora
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Fade-in ao entrar na viewport
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.stat-card, .feature-card, .benefit-item, .compliance-card, .pricing-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        observer.observe(el);
    });
</script>
</body>
</html>
