<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escritorius — Sistema de Gestão Jurídica com Inteligência Artificial</title>
    <meta name="description" content="Escritorius é o sistema completo de gestão para escritórios de advocacia: processos, prazos, clientes, financeiro e um módulo de IA jurídica que gera peças processuais e busca precedentes automaticamente.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary:        #06253D;
            --primary-light:  #0a3a5f;
            --primary-dark:   #041a2d;
            --secondary:      #CC8C5D;
            --secondary-light:#d9a479;
            --accent:         #1a4f72;
            --bg:             #F4F5F7;
            --surface:        #FFFFFF;
            --text:           #1A2535;
            --text-secondary: #5A6679;
            --success:        #1D9A6C;
            --warning:        #D97706;
            --danger:         #C0392B;
            --info:           #1565C0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: var(--text);
            overflow-x: hidden;
            background: var(--surface);
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
            border-bottom: 1px solid #e5e7eb;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--primary);
            text-decoration: none;
        }

        .navbar-brand .dot { color: var(--secondary); }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .navbar-links a {
            text-decoration: none;
            color: var(--text-secondary);
            font-size: .9rem;
            font-weight: 500;
            transition: color .2s;
        }

        .navbar-links a:hover { color: var(--primary); }

        .nav-cta {
            padding: 8px 22px;
            background: var(--secondary);
            color: var(--surface) !important;
            border-radius: 8px;
        }

        .nav-cta:hover { background: var(--secondary-light); color: var(--surface) !important; }

        .navbar-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
            width: 40px;
            height: 40px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
        }

        .navbar-toggle span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--primary);
            border-radius: 2px;
            transition: transform .25s, opacity .25s;
        }

        .navbar-toggle.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .navbar-toggle.open span:nth-child(2) { opacity: 0; }
        .navbar-toggle.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ── HERO ────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--surface);
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
            background: radial-gradient(circle, rgba(204,140,93,.10) 1px, transparent 1px);
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
            background: rgba(204,140,93,.16);
            border: 1px solid rgba(204,140,93,.45);
            border-radius: 50px;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 24px;
            letter-spacing: .5px;
            color: var(--secondary-light);
        }

        .hero h1 {
            font-size: clamp(2.4rem, 6vw, 4rem);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 20px;
            animation: fadeUp .9s ease both;
        }

        .hero h1 span { color: var(--secondary-light); }

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
            background: var(--secondary);
            color: var(--surface);
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all .3s;
            box-shadow: 0 8px 24px rgba(0,0,0,.25);
        }

        .btn-primary-hero:hover {
            transform: translateY(-2px);
            background: var(--secondary-light);
            box-shadow: 0 12px 32px rgba(0,0,0,.3);
        }

        .btn-outline-hero {
            padding: 15px 36px;
            border: 2px solid rgba(255,255,255,.7);
            color: var(--surface);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all .3s;
        }

        .btn-outline-hero:hover {
            background: rgba(255,255,255,.12);
            border-color: var(--surface);
        }

        .hero-tags {
            margin-top: 50px;
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            opacity: .85;
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
            background: rgba(6,37,61,.08);
            color: var(--primary);
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
            color: var(--text);
            margin-bottom: 14px;
        }

        .section-title .accent { color: var(--secondary); }

        .section-sub {
            font-size: 1.1rem;
            color: var(--text-secondary);
            max-width: 640px;
            line-height: 1.65;
        }

        /* ── PROBLEM ─────────────────────────────────── */
        .problem-section { background: #fdf6ef; }

        .problem-section .section-title .accent { color: var(--danger); }
        .problem-section .section-label { background: #fbe4de; color: var(--danger); }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-top: 48px;
        }

        .stat-card {
            background: var(--surface);
            padding: 32px 28px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(6,37,61,.07);
            text-align: center;
            border-top: 4px solid var(--danger);
            transition: transform .3s;
        }

        .stat-card:hover { transform: translateY(-4px); }

        .stat-number {
            font-size: 3.2rem;
            font-weight: 800;
            color: var(--danger);
            line-height: 1;
            margin-bottom: 12px;
        }

        .stat-label { font-size: 1rem; color: var(--text-secondary); line-height: 1.5; }
        .stat-source { font-size: .75rem; color: #9ca3af; margin-top: 10px; }

        /* ── ÁREAS STRIP ──────────────────────────────── */
        .specialties-section { background: var(--bg); }

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
            background: var(--surface);
            border: 1px solid #e5e7eb;
            border-radius: 50px;
            font-size: .9rem;
            font-weight: 500;
            color: var(--text);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            transition: all .2s;
        }

        .specialty-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(6,37,61,.12);
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
            background: var(--surface);
            padding: 36px 32px;
            border-radius: 18px;
            box-shadow: 0 4px 20px rgba(6,37,61,.08);
            border: 1px solid #e5e7eb;
            transition: all .3s;
        }

        .feature-card:hover {
            box-shadow: 0 12px 36px rgba(6,37,61,.14);
            transform: translateY(-4px);
            border-color: rgba(6,37,61,.2);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(6,37,61,.07);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            margin-bottom: 20px;
        }

        .feature-card.ai .feature-icon { background: rgba(204,140,93,.16); }

        .feature-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }

        .feature-desc { color: var(--text-secondary); line-height: 1.6; font-size: .95rem; }

        .feature-card.ai {
            border-color: rgba(204,140,93,.35);
            background: linear-gradient(180deg, #fffaf5 0%, var(--surface) 100%);
        }

        .feature-badge-ai {
            display: inline-block;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .5px;
            color: var(--secondary);
            background: rgba(204,140,93,.14);
            padding: 3px 10px;
            border-radius: 50px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        /* ── DEMO ────────────────────────────────────── */
        .demo-section { background: var(--bg); }

        .demo-container {
            background: var(--surface);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 16px 48px rgba(6,37,61,.1);
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
            background: var(--bg);
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            cursor: pointer;
            font-size: .9rem;
            font-weight: 500;
            transition: all .25s;
            color: var(--text-secondary);
        }

        .demo-tab:hover { border-color: var(--primary); color: var(--primary); }
        .demo-tab.active { background: var(--primary); color: var(--surface); border-color: var(--primary); }

        .demo-content { display: none; }
        .demo-content.active { display: block; animation: fadeIn .4s ease; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .demo-screen {
            width: 100%;
            min-height: 360px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--text-secondary);
            position: relative;
            overflow: hidden;
        }

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

        .demo-desc h3 { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .demo-desc p { color: var(--text-secondary); line-height: 1.6; }

        /* ── IA THAINA DESTAQUE ──────────────────────── */
        .ai-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: var(--surface);
            position: relative;
            overflow: hidden;
        }

        .ai-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(204,140,93,.18) 0%, transparent 55%);
        }

        .ai-section .section-inner { position: relative; z-index: 1; }

        .ai-section .section-label { background: rgba(204,140,93,.22); color: var(--secondary-light); }
        .ai-section .section-title { color: var(--surface); }
        .ai-section .section-title .accent { color: var(--secondary-light); }
        .ai-section .section-sub { color: rgba(255,255,255,.82); }

        .ai-flow {
            margin-top: 52px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .ai-flow-step {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 16px;
            padding: 28px 24px;
            backdrop-filter: blur(4px);
            transition: transform .3s, background .3s;
        }

        .ai-flow-step:hover {
            transform: translateY(-4px);
            background: rgba(255,255,255,.1);
        }

        .ai-flow-num {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--secondary);
            color: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .95rem;
            margin-bottom: 16px;
        }

        .ai-flow-step h4 { font-size: 1.02rem; font-weight: 700; margin-bottom: 8px; }
        .ai-flow-step p { font-size: .88rem; color: rgba(255,255,255,.75); line-height: 1.55; }

        /* ── BENEFITS ────────────────────────────────── */
        .benefits-section { background: var(--surface); }

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
            background: var(--bg);
            border-radius: 14px;
            border-left: 4px solid var(--primary);
            transition: transform .25s;
        }

        .benefit-item:hover { transform: translateX(4px); }

        .benefit-icon { font-size: 1.8rem; min-width: 40px; }

        .benefit-content h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }

        .benefit-content p { font-size: .9rem; color: var(--text-secondary); line-height: 1.55; }

        /* ── COMPLIANCE ──────────────────────────────── */
        .compliance-section { background: var(--bg); }

        .compliance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }

        .compliance-card {
            background: var(--surface);
            padding: 28px 24px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(6,37,61,.08);
        }

        .compliance-card .badge-icon { font-size: 2.4rem; margin-bottom: 14px; }
        .compliance-card h4 { font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .compliance-card p { font-size: .85rem; color: var(--text-secondary); line-height: 1.5; }

        /* ── FAQ ─────────────────────────────────────── */
        .faq-section { background: var(--surface); }

        .faq-list {
            margin-top: 44px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            max-width: 820px;
        }

        .faq-item {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            background: var(--bg);
        }

        .faq-question {
            width: 100%;
            text-align: left;
            padding: 20px 24px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .faq-question .plus {
            font-size: 1.3rem;
            color: var(--secondary);
            transition: transform .3s;
            flex-shrink: 0;
        }

        .faq-item.open .faq-question .plus { transform: rotate(45deg); }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height .35s ease, padding .35s ease;
            padding: 0 24px;
        }

        .faq-item.open .faq-answer {
            max-height: 320px;
            padding: 0 24px 22px;
        }

        .faq-answer p { color: var(--text-secondary); line-height: 1.65; font-size: .93rem; }

        /* ── PRICING ─────────────────────────────────── */
        .pricing-section { background: var(--bg); }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
            margin-top: 52px;
            align-items: start;
        }

        .pricing-card {
            background: var(--surface);
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            padding: 40px 36px;
            text-align: center;
            transition: all .3s;
        }

        .pricing-card:hover {
            border-color: var(--primary);
            box-shadow: 0 12px 36px rgba(6,37,61,.12);
            transform: translateY(-4px);
        }

        .pricing-card.featured {
            border-color: var(--secondary);
            box-shadow: 0 12px 40px rgba(204,140,93,.22);
            position: relative;
        }

        .pricing-badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--secondary);
            color: var(--surface);
            font-size: .75rem;
            font-weight: 700;
            padding: 4px 18px;
            border-radius: 50px;
            letter-spacing: .5px;
            white-space: nowrap;
        }

        .pricing-title { font-size: 1.2rem; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .pricing-price { font-size: 2.6rem; font-weight: 800; color: var(--primary); line-height: 1; margin: 16px 0 4px; }
        .pricing-card.featured .pricing-price { color: var(--secondary); }
        .pricing-price span { font-size: 1.05rem; font-weight: 600; vertical-align: super; }
        .pricing-period { color: #9ca3af; font-size: .85rem; margin-bottom: 28px; }

        .pricing-features {
            list-style: none;
            margin-bottom: 32px;
            text-align: left;
        }

        .pricing-features li {
            padding: 9px 0;
            border-bottom: 1px solid #eceef1;
            font-size: .92rem;
            color: var(--text-secondary);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .pricing-features li::before {
            content: "✓";
            color: var(--success);
            font-weight: 700;
            flex-shrink: 0;
        }

        .pricing-btn {
            display: block;
            padding: 14px;
            background: var(--primary);
            color: var(--surface);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: .95rem;
            transition: background .25s;
        }

        .pricing-btn:hover { background: var(--primary-light); }
        .pricing-btn.outline { background: transparent; color: var(--primary); border: 2px solid var(--primary); }
        .pricing-btn.outline:hover { background: var(--primary); color: var(--surface); }
        .pricing-card.featured .pricing-btn { background: var(--secondary); }
        .pricing-card.featured .pricing-btn:hover { background: var(--secondary-light); }

        .pricing-note {
            margin-top: 36px;
            font-size: .88rem;
            color: var(--text-secondary);
        }

        /* ── CTA FINAL ───────────────────────────────── */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--surface);
            text-align: center;
            padding: 100px 20px;
        }

        .cta-section h2 { font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 800; margin-bottom: 16px; }
        .cta-section p { font-size: 1.1rem; opacity: .9; margin-bottom: 40px; max-width: 560px; margin-left: auto; margin-right: auto; }

        .cta-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

        .btn-white {
            padding: 18px 52px;
            background: var(--surface);
            color: var(--primary);
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
            color: var(--surface);
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
            background: var(--primary-dark);
            color: #c7ceda;
            padding: 48px 20px 28px;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
        }

        .footer-brand { font-size: 1.3rem; font-weight: 800; color: var(--surface); margin-bottom: 12px; }
        .footer-tagline { font-size: .9rem; line-height: 1.6; color: #9aa5b8; max-width: 320px; }

        .footer-col h4 { font-size: .85rem; font-weight: 700; color: var(--surface); letter-spacing: .6px; text-transform: uppercase; margin-bottom: 14px; }

        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 8px; }
        .footer-col ul li a { color: #9aa5b8; text-decoration: none; font-size: .88rem; transition: color .2s; }
        .footer-col ul li a:hover { color: var(--secondary-light); }

        .footer-bottom {
            max-width: 1200px;
            margin: 32px auto 0;
            padding-top: 20px;
            border-top: 1px solid #163350;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            font-size: .82rem;
            color: #6b7a94;
        }

        /* ── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 768px) {
            .navbar { padding: 0 18px; }

            .navbar-toggle { display: flex; }

            .navbar-links {
                position: absolute;
                top: 64px;
                left: 0;
                right: 0;
                flex-direction: column;
                align-items: flex-start;
                gap: 0;
                background: var(--surface);
                border-bottom: 1px solid #e5e7eb;
                box-shadow: 0 12px 24px rgba(0,0,0,.08);
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                transition: max-height .3s ease, opacity .25s ease;
            }

            .navbar-links.open {
                max-height: 420px;
                opacity: 1;
            }

            .navbar-links li {
                width: 100%;
                padding: 0 18px;
            }

            .navbar-links li a {
                display: block;
                padding: 14px 0;
                width: 100%;
                border-bottom: 1px solid #f1f2f4;
            }

            .navbar-links li:last-child a {
                border-bottom: none;
            }

            .navbar-links .nav-cta {
                margin: 12px 0;
                text-align: center;
            }

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
        Escritorius<span class="dot">.</span>
    </a>
    <button class="navbar-toggle" id="navToggle" aria-label="Abrir menu">
        <span></span><span></span><span></span>
    </button>
    <ul class="navbar-links" id="navLinks">
        <li><a href="#problema">O Problema</a></li>
        <li><a href="#solucao">Funcionalidades</a></li>
        <li><a href="#ia">IA Thaina</a></li>
        <li><a href="#demo">Demonstração</a></li>
        <li><a href="#planos">Planos</a></li>
        <li><a href="#contato" class="nav-cta">Falar Conosco</a></li>
    </ul>
</nav>

<!-- ── HERO ───────────────────────────────────────────── -->
<section class="hero" id="inicio">
    <div class="hero-content">
        <div class="hero-badge">⚖️ Gestão Jurídica Inteligente · Em Breve</div>
        <h1>Seu escritório organizado.<br><span>Suas peças, geradas por IA.</span></h1>
        <p>Escritorius centraliza processos, prazos, clientes e financeiro do seu escritório de advocacia — e conta com a Thaina, a assistente de inteligência artificial que gera petições, busca precedentes e analisa documentos por você. Atualmente em fase piloto com um escritório parceiro, antes do lançamento oficial.</p>
        <div class="hero-buttons">
            <a href="#demo" class="btn-primary-hero">Ver Demonstração</a>
            <a href="#contato" class="btn-outline-hero">Entrar na Lista de Espera</a>
        </div>
        <div class="hero-tags">
            <span class="hero-tag">✅ Conforme LGPD</span>
            <span class="hero-tag">✅ IA jurídica nativa</span>
            <span class="hero-tag">✅ Multi-escritório isolado</span>
            <span class="hero-tag">✅ Site institucional incluso</span>
        </div>
    </div>
</section>

<!-- ── PROBLEMA ───────────────────────────────────────── -->
<section class="section problem-section" id="problema">
    <div class="section-inner">
        <span class="section-label">O Problema</span>
        <h2 class="section-title">A rotina que <span class="accent">consome o escritório</span></h2>
        <p class="section-sub">Planilhas soltas, prazos anotados em agendas separadas e horas gastas redigindo peças do zero. O dia a dia jurídico ainda é, na maioria dos escritórios, manual demais.</p>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">+40%</div>
                <div class="stat-label">do tempo de um advogado é consumido em tarefas <strong>repetitivas e administrativas</strong>, não na estratégia do caso</div>
                <div class="stat-source">Estimativa de mercado · gestão jurídica</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">1 em 3</div>
                <div class="stat-label">escritórios já perdeu prazo processual por <strong>falta de controle centralizado</strong> de agenda e processos</div>
                <div class="stat-source">Estimativa de mercado · gestão jurídica</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">2-10s</div>
                <div class="stat-label">é o tempo médio que a IA do Escritorius leva para <strong>gerar a estrutura de uma peça</strong> processual completa</div>
                <div class="stat-source">Escritorius · módulo de IA</div>
            </div>
        </div>
    </div>
</section>

<!-- ── ÁREAS DE ATUAÇÃO ────────────────────────────────── -->
<section class="section specialties-section" id="solucao">
    <div class="section-inner" style="text-align:center;">
        <span class="section-label">Áreas Suportadas</span>
        <h2 class="section-title">Uma plataforma para <span class="accent">todo o escritório</span></h2>
        <p class="section-sub" style="margin:0 auto;">O Escritorius acompanha o fluxo de trabalho das principais áreas do Direito, do contencioso ao consultivo.</p>
        <div class="specialties-grid">
            <div class="specialty-chip"><span class="icon">⚖️</span> Direito Civil</div>
            <div class="specialty-chip"><span class="icon">👔</span> Direito Trabalhista</div>
            <div class="specialty-chip"><span class="icon">🏛️</span> Direito Penal</div>
            <div class="specialty-chip"><span class="icon">📊</span> Direito Tributário</div>
            <div class="specialty-chip"><span class="icon">🛒</span> Direito do Consumidor</div>
            <div class="specialty-chip"><span class="icon">🏢</span> Direito Empresarial</div>
            <div class="specialty-chip"><span class="icon">👨‍👩‍👧</span> Direito de Família</div>
            <div class="specialty-chip"><span class="icon">🏠</span> Direito Imobiliário</div>
            <div class="specialty-chip"><span class="icon">📝</span> Contratos</div>
        </div>
    </div>
</section>

<!-- ── FUNCIONALIDADES ────────────────────────────────── -->
<section class="section">
    <div class="section-inner">
        <span class="section-label">Funcionalidades</span>
        <h2 class="section-title">Tudo que seu escritório <span class="accent">precisa em um só lugar</span></h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📁</div>
                <h3 class="feature-title">Gestão completa de processos</h3>
                <p class="feature-desc">Cadastro de processos judiciais com vara, comarca, tipo de ação, partes envolvidas e histórico de andamentos em um só lugar.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏰</div>
                <h3 class="feature-title">Agenda com alertas de prazos</h3>
                <p class="feature-desc">Audiências, vencimentos e compromissos com alertas automáticos. Nenhum prazo processual passa despercebido.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3 class="feature-title">Clientes e contratos</h3>
                <p class="feature-desc">Cadastro completo de clientes, vinculação a processos e contratos, histórico de atendimento e comunicação centralizada.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3 class="feature-title">Financeiro integrado</h3>
                <p class="feature-desc">Faturamento do escritório, honorários por processo ou contrato, controle de recebíveis e visão consolidada do caixa.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📄</div>
                <h3 class="feature-title">Geração automática de documentos</h3>
                <p class="feature-desc">Modelos de petições, contratos e documentos gerados automaticamente a partir dos dados já cadastrados do cliente e do processo.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌐</div>
                <h3 class="feature-title">Site institucional incluso</h3>
                <p class="feature-desc">Página do escritório com sobre, áreas de atuação, equipe, blog e formulário de contato — pronta para publicar, sem custo adicional.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔐</div>
                <h3 class="feature-title">Segurança e conformidade LGPD</h3>
                <p class="feature-desc">Proteção contra CSRF e XSS, senhas com hash bcrypt, logs de auditoria completos e tratamento de dados sensíveis conforme a LGPD.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏗️</div>
                <h3 class="feature-title">Arquitetura multi-escritório</h3>
                <p class="feature-desc">Cada escritório opera com seus dados completamente isolados. Uma plataforma, múltiplos clientes, sem interferência entre eles.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── IA THAINA ───────────────────────────────────────── -->
<section class="section ai-section" id="ia">
    <div class="section-inner">
        <span class="section-label">Inteligência Artificial Jurídica</span>
        <h2 class="section-title">Conheça a <span class="accent">Thaina</span></h2>
        <p class="section-sub">A assistente de IA do Escritorius lê o caso, busca fundamentação e entrega uma peça estruturada — para o advogado revisar, ajustar e assinar. Ela não substitui o advogado: acelera o trabalho que consome mais tempo.</p>

        <div class="features-grid" style="margin-top: 48px;">
            <div class="feature-card ai">
                <span class="feature-badge-ai">Geração de Peças</span>
                <div class="feature-icon">✍️</div>
                <h3 class="feature-title">Petições, contestações, recursos e contratos</h3>
                <p class="feature-desc">A Thaina gera a estrutura completa da peça a partir dos dados do cliente, do processo e dos fatos relevantes descritos pelo advogado.</p>
            </div>
            <div class="feature-card ai">
                <span class="feature-badge-ai">Busca por Precedentes</span>
                <div class="feature-icon">🔎</div>
                <h3 class="feature-title">Busca semântica de jurisprudência</h3>
                <p class="feature-desc">Encontra precedentes e julgados relacionados ao caso por similaridade de conteúdo, não apenas por palavra-chave exata.</p>
            </div>
            <div class="feature-card ai">
                <span class="feature-badge-ai">Análise de Documentos</span>
                <div class="feature-icon">📑</div>
                <h3 class="feature-title">Extração de fundamentos jurídicos</h3>
                <p class="feature-desc">Identifica automaticamente artigos de lei, teses e fundamentos aplicáveis a partir do texto do caso, com processamento de linguagem natural em português.</p>
            </div>
            <div class="feature-card ai">
                <span class="feature-badge-ai">Exportação</span>
                <div class="feature-icon">📥</div>
                <h3 class="feature-title">Download em PDF e DOCX</h3>
                <p class="feature-desc">A peça gerada pode ser baixada em PDF para impressão ou em DOCX, já formatada e pronta para edição no Word.</p>
            </div>
        </div>

        <div class="ai-flow">
            <div class="ai-flow-step">
                <div class="ai-flow-num">1</div>
                <h4>Descreva o caso</h4>
                <p>Informe o cliente, o processo vinculado (se houver), os fatos relevantes e os pedidos.</p>
            </div>
            <div class="ai-flow-step">
                <div class="ai-flow-num">2</div>
                <h4>A Thaina processa</h4>
                <p>Busca precedentes semelhantes, extrai fundamentos jurídicos aplicáveis e estrutura o texto da peça.</p>
            </div>
            <div class="ai-flow-step">
                <div class="ai-flow-num">3</div>
                <h4>Você revisa</h4>
                <p>O advogado lê, ajusta o texto gerado e refina os fundamentos antes de finalizar — a decisão é sempre humana.</p>
            </div>
            <div class="ai-flow-step">
                <div class="ai-flow-num">4</div>
                <h4>Baixe e protocole</h4>
                <p>Exporte em PDF ou DOCX e siga para o protocolo, com o documento já pronto.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── DEMO ───────────────────────────────────────────── -->
<section class="section demo-section" id="demo">
    <div class="section-inner">
        <span class="section-label">Demonstração</span>
        <h2 class="section-title">Veja o <span class="accent">Escritorius</span> em ação</h2>
        <p class="section-sub">Navegue pelas principais telas do sistema.</p>

        <div class="demo-container">
            <div class="demo-tabs">
                <button class="demo-tab active" onclick="showDemo('dashboard', event)">Dashboard</button>
                <button class="demo-tab" onclick="showDemo('processos', event)">Processos</button>
                <button class="demo-tab" onclick="showDemo('ia', event)">IA Thaina</button>
                <button class="demo-tab" onclick="showDemo('financeiro', event)">Financeiro</button>
                <button class="demo-tab" onclick="showDemo('site', event)">Site Institucional</button>
            </div>

            <!-- Dashboard -->
            <div id="demo-dashboard" class="demo-content active">
                <div class="demo-screen" style="background:#f2f6fa; min-height:340px;">
                    <div class="screen-chrome" style="background:#dbe6ee;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">📊</div>
                        <div class="screen-title" style="color:#06253D;">Dashboard Administrativo</div>
                        <div class="screen-path">/src/views/admin/dashboard.php</div>
                        <p style="font-size:.85rem;color:#5A6679;text-align:center;max-width:320px;margin-top:8px;">Estatísticas em tempo real: processos ativos, prazos próximos, faturamento do mês e atividades recentes da equipe.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Visão geral do escritório em um clique</h3>
                    <p>O sócio ou gestor acompanha o andamento de todos os processos, os próximos prazos e a saúde financeira do escritório sem precisar navegar por múltiplas telas.</p>
                </div>
            </div>

            <!-- Processos -->
            <div id="demo-processos" class="demo-content">
                <div class="demo-screen" style="background:#f5f1ea; min-height:340px;">
                    <div class="screen-chrome" style="background:#e7dcc9;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">📁</div>
                        <div class="screen-title" style="color:#CC8C5D;">Gestão de Processos</div>
                        <div class="screen-path">/src/views/admin/processos/</div>
                        <p style="font-size:.85rem;color:#5A6679;text-align:center;max-width:320px;margin-top:8px;">Cadastro de processos com número, vara, comarca, partes envolvidas, andamentos e prazos vinculados.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Cada processo, com tudo o que importa</h3>
                    <p>Número do processo, vara, comarca, tipo de ação, partes, documentos anexados e histórico de andamentos organizados em uma única tela por processo.</p>
                </div>
            </div>

            <!-- IA -->
            <div id="demo-ia" class="demo-content">
                <div class="demo-screen" style="background:#fdf6ef; min-height:340px;">
                    <div class="screen-chrome" style="background:#f0ddc4;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">🤖</div>
                        <div class="screen-title" style="color:#CC8C5D;">Inteligência Jurídica</div>
                        <div class="screen-path">/src/views/admin/ia/gerar-peca.php</div>
                        <p style="font-size:.85rem;color:#5A6679;text-align:center;max-width:340px;margin-top:8px;">Formulário de geração de peça: tipo, cliente, processo vinculado, fatos relevantes e pedidos. A Thaina gera o texto estruturado.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Da descrição do caso à peça pronta</h3>
                    <p>O advogado escolhe o tipo de peça, descreve o caso e a Thaina devolve um documento estruturado com fundamentação jurídica, pronto para revisão e exportação.</p>
                </div>
            </div>

            <!-- Financeiro -->
            <div id="demo-financeiro" class="demo-content">
                <div class="demo-screen" style="background:#f0f7f3; min-height:340px;">
                    <div class="screen-chrome" style="background:#cfe8da;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">💰</div>
                        <div class="screen-title" style="color:#1D9A6C;">Controle Financeiro</div>
                        <div class="screen-path">/src/views/admin/financeiro/</div>
                        <p style="font-size:.85rem;color:#5A6679;text-align:center;max-width:320px;margin-top:8px;">Honorários por processo, contas a receber, faturamento mensal e relatórios financeiros consolidados.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Faturamento do escritório sob controle</h3>
                    <p>Cada honorário fica vinculado ao cliente e ao processo de origem. O gestor acompanha recebíveis, inadimplência e faturamento sem depender de planilhas paralelas.</p>
                </div>
            </div>

            <!-- Site institucional -->
            <div id="demo-site" class="demo-content">
                <div class="demo-screen" style="background:#f4f5f7; min-height:340px;">
                    <div class="screen-chrome" style="background:#dfe2e7;">
                        <div class="chrome-dot" style="background:#ff5f57;"></div>
                        <div class="chrome-dot" style="background:#febc2e;"></div>
                        <div class="chrome-dot" style="background:#28c840;"></div>
                    </div>
                    <div class="screen-body">
                        <div class="screen-icon">🌐</div>
                        <div class="screen-title" style="color:#06253D;">Site Institucional</div>
                        <div class="screen-path">/src/views/pages/</div>
                        <p style="font-size:.85rem;color:#5A6679;text-align:center;max-width:320px;margin-top:8px;">Página inicial, sobre o escritório, áreas de atuação, equipe, blog e formulário de agendamento — tudo editável pelo painel.</p>
                    </div>
                </div>
                <div class="demo-desc">
                    <h3>Presença online sem custo adicional</h3>
                    <p>O plano já inclui um site institucional completo, com blog e agendamento online, editável pelo próprio painel administrativo do escritório.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── BENEFÍCIOS ─────────────────────────────────────── -->
<section class="section benefits-section">
    <div class="section-inner">
        <span class="section-label">Benefícios</span>
        <h2 class="section-title">Impacto real na <span class="accent">rotina do escritório</span></h2>
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon">⏱️</div>
                <div class="benefit-content">
                    <h3>Menos tempo redigindo, mais tempo estrategizando</h3>
                    <p>A estrutura da peça sai pronta em segundos — o advogado foca em revisar e refinar a estratégia do caso.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">🎯</div>
                <div class="benefit-content">
                    <h3>Prazos nunca mais esquecidos</h3>
                    <p>Agenda integrada com alertas automáticos de audiências e vencimentos elimina o risco de perda de prazo.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">📚</div>
                <div class="benefit-content">
                    <h3>Fundamentação mais rica</h3>
                    <p>Busca semântica de precedentes traz jurisprudência relevante que uma busca manual por palavra-chave poderia deixar passar.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">💼</div>
                <div class="benefit-content">
                    <h3>Visão financeira unificada</h3>
                    <p>Honorários, recebíveis e faturamento em um único painel, sem depender de planilhas paralelas.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">📈</div>
                <div class="benefit-content">
                    <h3>Escritório mais escalável</h3>
                    <p>Processos, clientes e documentos organizados permitem crescer a equipe sem perder controle da operação.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">🔗</div>
                <div class="benefit-content">
                    <h3>Tudo conectado</h3>
                    <p>Cliente, processo, documento, financeiro e site institucional falam entre si — sem retrabalho de digitação.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CONFORMIDADE ───────────────────────────────────── -->
<section class="section compliance-section">
    <div class="section-inner" style="text-align:center;">
        <span class="section-label">Conformidade e Segurança</span>
        <h2 class="section-title">Construído dentro da <span class="accent">lei</span></h2>
        <p class="section-sub" style="margin:0 auto;">O Escritorius foi projetado desde a arquitetura para proteger dados sensíveis de clientes e processos.</p>
        <div class="compliance-grid">
            <div class="compliance-card">
                <div class="badge-icon">🛡️</div>
                <h4>LGPD — Lei 13.709/2018</h4>
                <p>Tratamento de dados pessoais e sensíveis com isolamento por escritório e controle de acesso por perfil de usuário.</p>
            </div>
            <div class="compliance-card">
                <div class="badge-icon">🔐</div>
                <h4>Proteção CSRF e XSS</h4>
                <p>Formulários protegidos contra ataques de falsificação de requisição e sanitização automática de entradas do usuário.</p>
            </div>
            <div class="compliance-card">
                <div class="badge-icon">🗝️</div>
                <h4>Senhas com Hash Bcrypt</h4>
                <p>Senhas nunca armazenadas em texto puro. Recuperação de senha com tokens seguros e limite de tentativas.</p>
            </div>
            <div class="compliance-card">
                <div class="badge-icon">📋</div>
                <h4>Logs de Auditoria</h4>
                <p>Registro completo de ações administrativas, com usuário, data e IP, para rastreabilidade total do sistema.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── FAQ ─────────────────────────────────────────────── -->
<section class="section faq-section">
    <div class="section-inner">
        <span class="section-label">Perguntas Frequentes</span>
        <h2 class="section-title">Tire suas <span class="accent">dúvidas</span></h2>
        <div class="faq-list">
            <div class="faq-item open">
                <button class="faq-question" onclick="toggleFaq(this)">
                    A IA substitui o trabalho do advogado?
                    <span class="plus">+</span>
                </button>
                <div class="faq-answer">
                    <p>Não. A Thaina gera uma estrutura inicial de peça com fundamentação e sugestão de precedentes, mas toda revisão, ajuste de estratégia e decisão final permanece com o advogado responsável antes do protocolo.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Meus dados ficam isolados dos de outros escritórios?
                    <span class="plus">+</span>
                </button>
                <div class="faq-answer">
                    <p>Sim. O Escritorius usa arquitetura multi-escritório: cada cliente opera com seus próprios dados completamente isolados dos demais, sem qualquer interferência entre contas.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Preciso instalar algo no meu computador?
                    <span class="plus">+</span>
                </button>
                <div class="faq-answer">
                    <p>Não. O Escritorius é um sistema web acessado pelo navegador, de qualquer computador com internet, sem necessidade de instalação local.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    O site institucional incluso pode ser personalizado?
                    <span class="plus">+</span>
                </button>
                <div class="faq-answer">
                    <p>Sim. O site vem com página inicial, sobre o escritório, áreas de atuação, equipe e blog, todos editáveis diretamente pelo painel administrativo, sem depender de outro fornecedor.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Como funciona o suporte técnico?
                    <span class="plus">+</span>
                </button>
                <div class="faq-answer">
                    <p>Todos os planos incluem suporte técnico da equipe NetoNerd via e-mail e WhatsApp para dúvidas de uso, configuração e eventuais problemas do sistema.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    Posso migrar de outro sistema de gestão jurídica?
                    <span class="plus">+</span>
                </button>
                <div class="faq-answer">
                    <p>Sim. Nossa equipe auxilia no processo de importação de clientes, processos e contratos durante a implantação, para que a transição não pare a rotina do escritório.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── PLANOS ─────────────────────────────────────────── -->
<section class="section pricing-section" id="planos">
    <div class="section-inner" style="text-align:center;">
        <span class="section-label">Planos</span>
        <h2 class="section-title">Planos que <span class="accent">crescem com seu escritório</span></h2>
        <p class="section-sub" style="margin:0 auto;">Estrutura de planos prevista para o lançamento oficial. O Escritorius está em fase piloto — entre na lista de espera para ser um dos primeiros a contratar.</p>

        <div class="pricing-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            <div class="pricing-card">
                <h3 class="pricing-title">Solo</h3>
                <div class="pricing-price"><span>R$</span> 349</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>1 advogado</li>
                    <li>Até 100 processos ativos</li>
                    <li>Agenda e prazos integrados</li>
                    <li>Financeiro básico</li>
                    <li>Site institucional incluso</li>
                    <li>10 peças com IA por mês</li>
                </ul>
                <a href="#contato" class="pricing-btn outline">Entrar na Lista de Espera</a>
            </div>

            <div class="pricing-card featured">
                <div class="pricing-badge">MAIS POPULAR</div>
                <h3 class="pricing-title">Escritório</h3>
                <div class="pricing-price"><span>R$</span> 890</div>
                <p class="pricing-period">por mês</p>
                <ul class="pricing-features">
                    <li>Até 10 advogados</li>
                    <li>Processos ilimitados</li>
                    <li>Financeiro completo</li>
                    <li>Peças com IA ilimitadas</li>
                    <li>Busca de precedentes com IA</li>
                    <li>Site institucional + blog</li>
                    <li>Suporte técnico prioritário</li>
                </ul>
                <a href="#contato" class="pricing-btn">Entrar na Lista de Espera</a>
            </div>

            <div class="pricing-card">
                <h3 class="pricing-title">Corporativo</h3>
                <div class="pricing-price" style="font-size:1.7rem;">Sob consulta</div>
                <p class="pricing-period">plano personalizado</p>
                <ul class="pricing-features">
                    <li>Advogados ilimitados</li>
                    <li>Múltiplas unidades/filiais</li>
                    <li>Integrações personalizadas</li>
                    <li>Onboarding dedicado</li>
                    <li>Relatórios gerenciais avançados</li>
                    <li>Gerente de conta dedicado</li>
                </ul>
                <a href="#contato" class="pricing-btn outline">Falar com a Equipe</a>
            </div>
        </div>
        <p class="pricing-note">Valores de referência para o lançamento oficial. Condições comerciais podem variar — confirme com nossa equipe.</p>
    </div>
</section>

<!-- ── CTA FINAL ──────────────────────────────────────── -->
<section class="cta-section" id="contato">
    <h2>Coloque a IA para trabalhar<br>pelo seu escritório</h2>
    <p>O Escritorius está em fase piloto. Entre em contato para conhecer o sistema de perto e ser avisado assim que o lançamento oficial abrir novas vagas.</p>
    <div class="cta-buttons">
        <a href="mailto:contato@netonerd.com.br" class="btn-white">📧 Entrar na Lista de Espera</a>
        <a href="https://wa.me/5521977395867" class="btn-whatsapp" target="_blank" rel="noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            WhatsApp
        </a>
    </div>
    <p class="cta-contact">(21) 97739-5867 · contato@netonerd.com.br</p>
</section>

<!-- ── FOOTER ─────────────────────────────────────────── -->
<footer>
    <div class="footer-inner">
        <div>
            <div class="footer-brand">Escritorius.</div>
            <p class="footer-tagline">Sistema de gestão jurídica com inteligência artificial para escritórios de advocacia brasileiros. Desenvolvido pela NetoNerd.</p>
        </div>
        <div class="footer-col">
            <h4>Produto</h4>
            <ul>
                <li><a href="#solucao">Funcionalidades</a></li>
                <li><a href="#ia">IA Thaina</a></li>
                <li><a href="#demo">Demonstração</a></li>
                <li><a href="#planos">Planos</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contato</h4>
            <ul>
                <li><a href="mailto:contato@netonerd.com.br">contato@netonerd.com.br</a></li>
                <li><a href="https://wa.me/5521977395867">(21) 97739-5867</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; 2026 NetoNerd &middot; Escritorius. Todos os direitos reservados.</span>
        <span>Conforme LGPD</span>
    </div>
</footer>

<script>
    function showDemo(id, e) {
        document.querySelectorAll('.demo-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.demo-tab').forEach(el => el.classList.remove('active'));
        document.getElementById('demo-' + id).classList.add('active');
        if (e && e.target) e.target.classList.add('active');
    }

    function toggleFaq(btn) {
        const item = btn.closest('.faq-item');
        const wasOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item').forEach(el => el.classList.remove('open'));
        if (!wasOpen) item.classList.add('open');
    }

    // Menu mobile
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navToggle.classList.toggle('open');
            navLinks.classList.toggle('open');
        });
        navLinks.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => {
                navToggle.classList.remove('open');
                navLinks.classList.remove('open');
            });
        });
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

    document.querySelectorAll('.stat-card, .feature-card, .benefit-item, .compliance-card, .pricing-card, .ai-flow-step').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        observer.observe(el);
    });
</script>
</body>
</html>
