<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - NetoNerd Soluções Tecnológicas</title>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
            --dark-blue: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
        }

        /* Navbar Style (Igual à Index) */
        .navbar-brand img { width: 70px; }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .product-detail-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: -50px;
            border: none;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }

        .feature-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-right: 15px;
            width: 30px;
            text-align: center;
        }

        .price-box {
            background: #eef6ff;
            border-left: 5px solid var(--primary-color);
            padding: 20px;
            border-radius: 8px;
        }

        .btn-buy {
            background: var(--primary-color);
            color: white;
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .btn-buy:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.4);
            color: white;
        }

        .other-products-title {
            margin-top: 50px;
            font-weight: 700;
            color: var(--dark-blue);
        }

        .footer {
            background: var(--dark-blue);
            color: white;
            padding: 40px 0 20px;
            margin-top: 80px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../src/imagens/logoNetoNerd.jpg" alt="NetoNerd">
            </a>
            <a href="../index.php" class="btn btn-outline-primary btn-sm">Voltar para Home</a>
        </div>
    </nav>

    <header class="page-header">
        <div class="container">
            <h1 class="display-4 font-weight-bold">Nossas Soluções</h1>
            <p class="lead">Tecnologia de ponta para cada necessidade do seu negócio.</p>
        </div>
    </header>

    <main class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card product-detail-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="row">
                            <div class="col-md-4 text-center border-right">
                                <img src="../src/imagens/logoNetoNerd.jpg" id="product-img" class="img-fluid mb-4 rounded" style="max-width: 200px;">
                                <h2 id="product-title" class="font-weight-bold">Carregando...</h2>
                                <p id="product-tagline" class="text-muted"></p>
                                <hr>
                                <div class="price-box text-left">
                                    <small class="text-uppercase text-muted d-block">A partir de</small>
                                    <h3 id="product-price" class="text-primary font-weight-bold mb-0">R$ --</h3>
                                </div>
                            </div>
                            
                            <div class="col-md-8 pl-md-5">
                                <h4 class="mb-4 mt-3 mt-md-0">O que a solução oferece?</h4>
                                <div id="product-features">
                                    </div>

                                <div class="mt-5">
                                    <a href="contato.php" class="btn btn-buy btn-lg mr-2 mb-2">Contratar Agora</a>
                                    <a href="https://wa.me/5521977395867" class="btn btn-outline-success btn-lg mb-2">
                                        <i class="fab fa-whatsapp"></i> Tirar Dúvidas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="text-center other-products-title">Conheça outras soluções</h3>
        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <a href="?id=myhealth" class="card shadow-sm border-0 text-decoration-none text-dark h-100 p-3 text-center">
                    <h6>MyHealth</h6>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="?id=stylemanager" class="card shadow-sm border-0 text-decoration-none text-dark h-100 p-3 text-center">
                    <h6>Style Manager</h6>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="?id=pj" class="card shadow-sm border-0 text-decoration-none text-dark h-100 p-3 text-center">
                    <h6>NetoNerd PJ</h6>
                </a>
            </div>
        </div>
    </main>

    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2026 Neto Nerd Soluções Digitais LTDA.</p>
        </div>
    </footer>

    <script>
        // Dados dos produtos (Simulando um banco de dados)
        const produtos = {
            'myhealth': {
                titulo: 'MyHealth',
                tagline: 'Prontuário Eletrônico Nacional',
                preco: 'Sob Consulta',
                img: '../src/imagens/Logo_MyHealth.png',
                features: [
                    { icon: 'fa-file-medical', text: 'Histórico médico unificado e acessível em qualquer lugar.' },
                    { icon: 'fa-shield-halved', text: 'Segurança absoluta de dados seguindo normas da LGPD.' },
                    { icon: 'fa-user-doctor', text: 'Painel exclusivo para profissionais da saúde.' },
                    { icon: 'fa-mobile-screen', text: 'Aplicativo para acompanhamento do paciente.' }
                ]
            },
            'stylemanager': {
                titulo: 'Style Manager',
                tagline: 'Gestão para Salões e Barbearias',
                preco: 'R$ 139,90/mês',
                img: '../src/imagens/Logo_StyleManager.png',
                features: [
                    { icon: 'fa-calendar-check', text: 'Agendamento online 24h para seus clientes.' },
                    { icon: 'fa-boxes-stacked', text: 'Controle de estoque rigoroso e alertas de reposição.' },
                    { icon: 'fa-comments-dollar', text: 'Cálculo automático de comissões de profissionais.' },
                    { icon: 'fa-whatsapp', text: 'Lembretes automáticos via WhatsApp.' }
                ]
            },
            'pj': {
                titulo: 'NetoNerd PJ',
                tagline: 'Seu braço direito em Tecnologia',
                preco: 'R$ 400/mês',
                img: '../src/imagens/logoNetoNerd.jpg',
                features: [
                    { icon: 'fa-headset', text: 'Suporte técnico prioritário para sua equipe.' },
                    { icon: 'fa-cloud-arrow-up', text: 'Backup em nuvem gerenciado e seguro.' },
                    { icon: 'fa-network-wired', text: 'Manutenção e gestão de infraestrutura de rede.' },
                    { icon: 'fa-user-tie', text: 'Consultoria estratégica para novos projetos de TI.' }
                ]
            }
        };

        // Função para carregar o produto da URL
        function carregarProduto() {
            const params = new URLSearchParams(window.location.search);
            const id = params.get('id') || 'myhealth'; // Padrão
            const p = produtos[id] || produtos['myhealth'];

            document.getElementById('product-title').innerText = p.titulo;
            document.getElementById('product-tagline').innerText = p.tagline;
            document.getElementById('product-price').innerText = p.preco;
            document.getElementById('product-img').src = p.img;

            const featuresContainer = document.getElementById('product-features');
            featuresContainer.innerHTML = p.features.map(f => `
                <div class="d-flex align-items-center mb-3">
                    <div class="feature-icon"><i class="fas ${f.icon}"></i></div>
                    <div>${f.text}</div>
                </div>
            `).join('');
        }

        window.onload = carregarProduto;
    </script>
</body>
</html>