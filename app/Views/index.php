<?php
/**
 * NetoNerd - Landing Page Principal
 * 
 * @package NetoNerd
 * @author NetoNerd Team
 * @version 2.0
 */

// Inicia sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constantes de caminho (se não definidas)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Configurações da página
$pageTitle = 'NetoNerd - Soluções em Tecnologia';
$pageDescription = 'Suporte técnico especializado para pessoas físicas e jurídicas';

// Dados dos planos (pode vir do banco depois)
$planos = [
    [
        'titulo' => 'Plano Mensal',
        'preco' => 'R$ 400/mês',
        'beneficios' => [
            '1 visita presencial por mês para manutenção e ajustes',
            'Diagnóstico e recomendações para evitar problemas futuros'
        ]
    ],
    [
        'titulo' => 'Plano Quinzenal',
        'preco' => 'R$ 500/mês',
        'beneficios' => [
            '2 visitas presenciais por mês',
            'Suporte prioritário para emergências',
            'Treinamento básico para equipe sobre segurança digital e boas práticas'
        ]
    ],
    [
        'titulo' => 'Plano Semanal',
        'preco' => 'R$ 600/mês',
        'beneficios' => [
            '1 visita presencial por semana',
            'Atendimento emergencial garantido',
            'Treinamento aprimorado para equipe sobre organização de arquivos, backups e segurança'
        ]
    ]
];

// Dados dos serviços
$servicos = [
    [
        'titulo' => 'Atendimento para Pessoas Físicas',
        'descricao' => 'Soluções tecnológicas para seu dia a dia! Oferecemos manutenção de computadores, suporte remoto e aulas personalizadas.',
        'imagem' => 'public/assets/images/suporte-pf.jpg',
        'alt' => 'Atendimento Pessoa Física'
    ],
    [
        'titulo' => 'Atendimento para Empresas',
        'descricao' => 'Serviços especializados para negócios! Suporte para infraestrutura de TI, redes corporativas e soluções empresariais personalizadas.',
        'imagem' => 'public/assets/images/suporte-pj.jpg',
        'alt' => 'Atendimento Pessoa Jurídica'
    ]
];

// Processa mensagens de erro do login
$loginErro = '';
if (isset($_GET['login'])) {
    switch ($_GET['login']) {
        case 'erro':
            $loginErro = 'Usuário ou senha inválido(s)';
            break;
        case 'erro2':
            $loginErro = 'Por favor, faça login antes de acessar as páginas protegidas';
            break;
        case 'logout':
            $loginErro = 'Logout realizado com sucesso!';
            break;
    }
}

// Inclui o header
include_once 'layouts/header.php';
?>

<!-- Hero Section com Carousel de Planos -->
<section class="hero-section">
    <div class="container">
        <div id="planosCarousel" class="carousel slide mt-5" data-ride="carousel">
            <div class="carousel-header">
                <h2 class="text-center carousel-title">Conheça nossos planos para empresas!</h2>
            </div>
            
            <div class="carousel-inner">
                <?php foreach ($planos as $index => $plano): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <div class="plano-card">
                        <h3 class="plano-titulo"><?= htmlspecialchars($plano['titulo']) ?></h3>
                        <p class="plano-preco"><?= htmlspecialchars($plano['preco']) ?></p>
                        <ul class="plano-beneficios">
                            <?php foreach ($plano['beneficios'] as $beneficio): ?>
                            <li>✅ <?= htmlspecialchars($beneficio) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="planos.php" class="btn btn-primary btn-lg">Saiba Mais</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <a class="carousel-control-prev" href="#planosCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Anterior</span>
            </a>
            <a class="carousel-control-next" href="#planosCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Próximo</span>
            </a>
        </div>
    </div>
</section>

<!-- Login Section -->
<section class="login-section" id="login">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg login-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Área do Cliente</h4>
                    </div>
                    <div class="card-body">
                        <form action="app/Controllers/AuthController.php" method="post" id="loginForm">
                            <input type="hidden" name="action" value="login">
                            
                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    class="form-control" 
                                    placeholder="seu@email.com" 
                                    required 
                                    autofocus
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="senha">Senha</label>
                                <input 
                                    type="password" 
                                    name="senha" 
                                    id="senha" 
                                    class="form-control" 
                                    placeholder="••••••••" 
                                    required
                                >
                            </div>
                            
                            <?php if (!empty($loginErro)): ?>
                            <div class="alert alert-<?= $_GET['login'] === 'logout' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($loginErro) ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary btn-block btn-lg mb-3">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </button>
                            
                            <div class="text-center">
                                <a href="app/Views/clientes/cadastro.php" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-user-plus"></i> Criar Conta
                                </a>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="text-center">
                                <small>
                                    <a href="#" class="text-muted">Esqueceu sua senha?</a>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Nossos Serviços</h2>
                <p class="section-subtitle">Soluções completas em tecnologia para você e sua empresa</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($servicos as $servico): ?>
            <div class="col-md-6 mb-4">
                <div class="card service-card h-100 shadow-sm">
                    <img 
                        src="<?= htmlspecialchars($servico['imagem']) ?>" 
                        class="card-img-top" 
                        alt="<?= htmlspecialchars($servico['alt']) ?>"
                        onerror="this.src='public/assets/images/placeholder.jpg'"
                    >
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($servico['titulo']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($servico['descricao']) ?></p>
                        <a href="atendimento.php" class="btn btn-outline-primary">
                            Saiba Mais <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section bg-primary text-white py-5">
    <div class="container text-center">
        <h2 class="mb-3">Pronto para transformar sua experiência tecnológica?</h2>
        <p class="lead mb-4">Entre em contato conosco e descubra como podemos ajudar!</p>
        <a href="contato.php" class="btn btn-light btn-lg">
            <i class="fas fa-phone"></i> Fale Conosco
        </a>
    </div>
</section>

<?php
// Inclui o footer
include_once 'layouts/footer.php';
?>

<style>
/* ============================================================================
   ESTILOS PERSONALIZADOS - INDEX
   ============================================================================ */

/* Hero Section - Planos */
.hero-section {
    padding: 40px 0;
}

.carousel-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    padding: 30px;
    border-radius: 15px 15px 0 0;
    margin-bottom: 0;
}

.carousel-title {
    color: white;
    font-weight: 700;
    font-size: 1.8rem;
    margin: 0;
}

.carousel-inner {
    background: #f8f9fa;
    border-radius: 0 0 15px 15px;
    padding: 40px 20px;
}

.plano-card {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
    min-height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.plano-titulo {
    color: #007bff;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 15px;
}

.plano-preco {
    color: #28a745;
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 25px;
}

.plano-beneficios {
    list-style: none;
    padding: 0;
    margin: 30px 0;
    text-align: left;
}

.plano-beneficios li {
    padding: 12px 0;
    font-size: 1.1rem;
    border-bottom: 1px solid #eee;
}

.plano-beneficios li:last-child {
    border-bottom: none;
}

/* Login Section */
.login-section {
    padding: 60px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.login-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}

.login-card .card-header {
    padding: 25px;
    border-bottom: 3px solid rgba(255,255,255,0.2);
}

.login-card .card-body {
    padding: 35px;
}

.login-card .form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.login-card .form-control {
    padding: 12px 15px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.login-card .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

/* Services Section */
.services-section {
    padding: 80px 0;
    background: white;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 15px;
}

.section-subtitle {
    font-size: 1.2rem;
    color: #6c757d;
}

.service-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important;
}

.service-card img {
    height: 300px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.service-card:hover img {
    transform: scale(1.05);
}

.service-card .card-body {
    padding: 30px;
}

.service-card .card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 15px;
}

.service-card .card-text {
    color: #6c757d;
    line-height: 1.8;
    margin-bottom: 20px;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}

.cta-section h2 {
    font-weight: 700;
    font-size: 2.2rem;
}

.cta-section .btn-light {
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.cta-section .btn-light:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .carousel-title {
        font-size: 1.4rem;
    }
    
    .plano-titulo {
        font-size: 1.5rem;
    }
    
    .plano-preco {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .service-card img {
        height: 200px;
    }
}

/* Animações */
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

.service-card {
    animation: fadeInUp 0.6s ease-out;
}

.service-card:nth-child(2) {
    animation-delay: 0.2s;
}
</style>

<script>
// Validação do formulário de login
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;
    
    if (!email || !senha) {
        e.preventDefault();
        alert('Por favor, preencha todos os campos!');
        return false;
    }
    
    // Validação básica de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Por favor, insira um e-mail válido!');
        return false;
    }
    
    return true;
});

// Auto-dismiss de alertas após 5 segundos
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>