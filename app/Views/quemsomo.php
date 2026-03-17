<?php
/**
 * NetoNerd - Quem Somos
 * 
 * @package NetoNerd
 * @author NetoNerd Team
 * @version 2.0
 */

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações da página
$pageTitle = 'Quem Somos - NetoNerd';
$pageDescription = 'Conheça a história e os valores da NetoNerd';

// Valores da empresa
$valores = [
    [
        'icone' => 'fas fa-cross',
        'titulo' => 'Valores Cristãos',
        'descricao' => 'Baseamos nossas ações em princípios cristãos de honestidade, respeito e integridade'
    ],
    [
        'icone' => 'fas fa-handshake',
        'titulo' => 'Confiança',
        'descricao' => 'Construímos relacionamentos duradouros baseados em confiança e transparência'
    ],
    [
        'icone' => 'fas fa-rocket',
        'titulo' => 'Inovação',
        'descricao' => 'Buscamos constantemente as melhores soluções tecnológicas para nossos clientes'
    ],
    [
        'icone' => 'fas fa-users',
        'titulo' => 'Compromisso',
        'descricao' => 'Dedicação total ao sucesso e satisfação de cada cliente que atendemos'
    ]
];

// Timeline da empresa
$timeline = [
    [
        'ano' => '2023',
        'titulo' => 'Fundação da Four_BA',
        'descricao' => 'Quatro sócios unidos pela paixão por tecnologia fundam a empresa na UNIFESO, Teresópolis'
    ],
    [
        'ano' => '2024',
        'titulo' => 'Nascimento da NetoNerd',
        'descricao' => 'Rondineli assume como sócio majoritário e cria o projeto NetoNerd'
    ],
    [
        'ano' => '2025',
        'titulo' => 'Consolidação',
        'descricao' => 'NetoNerd se torna a marca principal, representando nossa missão e valores'
    ]
];

// Inclui o header
include_once 'layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-3 font-weight-bold text-white mb-3">
                    Quem Somos
                </h1>
                <p class="lead text-white mb-4">
                    Conheça a história da NetoNerd e descubra como levamos 
                    <strong>independência tecnológica</strong> para pessoas e empresas
                </p>
                <a href="#historia" class="btn btn-light btn-lg">
                    <i class="fas fa-book-open"></i> Nossa História
                </a>
            </div>
            <div class="col-lg-6 text-center">
                <div class="hero-illustration">
                    <i class="fas fa-building text-white"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nossa História -->
<section class="our-history" id="historia">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="history-card">
                    <div class="row">
                        <div class="col-md-4 mb-4 mb-md-0">
                            <div class="history-image">
                                <img src="public/assets/images/netonerd-logo-large.jpg" 
                                     alt="NetoNerd Logo" 
                                     class="img-fluid rounded"
                                     onerror="this.src='public/assets/images/logoNetoNerd.jpg'">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h2 class="mb-4">Nossa História</h2>
                            
                            <p class="text-justify">
                                A <strong>NetoNerd</strong> tem suas raízes na cidade de Teresópolis, 
                                mais especificamente na UNIFESO, onde nasceu a <strong>Four_BA</strong>, 
                                uma empresa criada por quatro sócios unidos pela paixão por tecnologia 
                                e inovação.
                            </p>
                            
                            <p class="text-justify">
                                Após um ano de intenso desenvolvimento e aprendizado, o sócio majoritário 
                                <strong>Rondineli</strong> assumiu a lideranÃ§a da empresa, trazendo 
                                consigo uma visão clara e determinada de onde queríamos chegar.
                            </p>
                            
                            <p class="text-justify">
                                Durante esse período de transição, nasceu o projeto <strong>NetoNerd</strong>, 
                                que rapidamente se tornou muito mais do que apenas um projeto – tornou-se 
                                a identidade e o coração da nossa empresa.
                            </p>
                            
                            <p class="text-justify">
                                Ligada aos valores cristãos que nos guiam, a NetoNerd tem como missão 
                                fundamental proporcionar <strong>independência tecnológica</strong> para 
                                todas as pessoas, sejam elas físicas ou jurídicas.
                            </p>
                            
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-lightbulb"></i>
                                <strong>Hoje:</strong> A Four_BA já não é mais nossa identidade principal. 
                                A <strong>NetoNerd</strong> se consolidou como nosso verdadeiro nome, 
                                representando nossa visão, missão e valores em cada atendimento e projeto 
                                que realizamos.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline -->
<section class="timeline-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Nossa Jornada</h2>
                <p class="section-subtitle">Veja como chegamos até aqui</p>
            </div>
        </div>
        
        <div class="timeline">
            <?php foreach ($timeline as $index => $evento): ?>
            <div class="timeline-item <?= $index % 2 === 0 ? 'left' : 'right' ?>">
                <div class="timeline-content">
                    <div class="timeline-year"><?= htmlspecialchars($evento['ano']) ?></div>
                    <h4><?= htmlspecialchars($evento['titulo']) ?></h4>
                    <p><?= htmlspecialchars($evento['descricao']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Missão e Valores -->
<section class="mission-values">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="mission-card">
                    <i class="fas fa-bullseye"></i>
                    <h3>Nossa Missão</h3>
                    <p>
                        Levar mais <strong>tecnologia</strong>, <strong>praticidade</strong> e 
                        <strong>confiança</strong> a cada pessoa e empresa que atendemos, sempre 
                        com respeito, comprometimento e excelência.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mission-card">
                    <i class="fas fa-eye"></i>
                    <h3>Nossa Visão</h3>
                    <p>
                        Ser referência em soluções tecnológicas, proporcionando 
                        <strong>independência digital</strong> e transformando a relação das 
                        pessoas com a tecnologia através de valores cristãos sólidos.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h3 class="mb-4">Nossos Valores</h3>
            </div>
            <?php foreach ($valores as $valor): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon">
                        <i class="<?= $valor['icone'] ?>"></i>
                    </div>
                    <h5 class="mt-3 mb-3"><?= htmlspecialchars($valor['titulo']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($valor['descricao']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Equipe -->
<section class="team-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Nossa Equipe</h2>
                <p class="section-subtitle">Profissionais dedicados ao seu sucesso</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="team-card text-center">
                    <div class="team-photo">
                        <img src="public/assets/images/team/rondineli.jpg" 
                             alt="Rondineli" 
                             onerror="this.src='public/assets/images/avatar-placeholder.png'">
                    </div>
                    <h4 class="mt-3">Rondineli</h4>
                    <p class="text-primary font-weight-bold">Fundador & CEO</p>
                    <p class="text-muted">
                        Visionário e líder, responsável pela criação da NetoNerd e 
                        direcionamento estratégico da empresa.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="cta-about">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="text-white mb-4">Faça Parte da Nossa História</h2>
                <p class="lead text-white-50 mb-4">
                    Entre em contato conosco e descubra como podemos ajudar você 
                    a alcançar a independência tecnológica
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="contato.php" class="btn btn-light btn-lg">
                        <i class="fas fa-phone"></i> Entre em Contato
                    </a>
                    <a href="planos.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-tags"></i> Conheça os Planos
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Inclui o footer
include_once 'layouts/footer.php';
?>

<style>
/* ============================================================================
   ESTILOS - QUEM SOMOS
   ============================================================================ */

/* Hero Section */
.hero-about {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    padding: 100px 0 80px;
    margin-top: -76px;
    padding-top: 176px;
    position: relative;
    overflow: hidden;
}

.hero-illustration {
    font-size: 15rem;
    opacity: 0.1;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Nossa História */
.our-history {
    padding: 80px 0;
    background: #f8f9fa;
}

.history-card {
    background: white;
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.history-image img {
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.history-card p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #495057;
    margin-bottom: 20px;
}

/* Timeline */
.timeline-section {
    padding: 80px 0;
    background: white;
}

.timeline {
    position: relative;
    max-width: 1000px;
    margin: 0 auto;
    padding: 40px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    width: 4px;
    background: linear-gradient(180deg, #007bff, #0056b3);
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -2px;
}

.timeline-item {
    position: relative;
    width: 50%;
    padding: 30px 40px;
    animation: fadeInUp 0.6s ease-out;
}

.timeline-item.left {
    left: 0;
    text-align: right;
}

.timeline-item.right {
    left: 50%;
    text-align: left;
}

.timeline-item::after {
    content: '';
    position: absolute;
    width: 25px;
    height: 25px;
    background: white;
    border: 4px solid #007bff;
    border-radius: 50%;
    top: 40px;
    z-index: 1;
}

.timeline-item.left::after {
    right: -13px;
}

.timeline-item.right::after {
    left: -13px;
}

.timeline-content {
    padding: 30px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.timeline-year {
    display: inline-block;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 15px;
}

.timeline-content h4 {
    color: #212529;
    font-size: 1.3rem;
    margin-bottom: 10px;
}

.timeline-content p {
    color: #6c757d;
    margin-bottom: 0;
}

/* Missão e Valores */
.mission-values {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.mission-card {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    height: 100%;
    text-align: center;
    transition: all 0.3s ease;
}

.mission-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.mission-card i {
    font-size: 4rem;
    color: #007bff;
    margin-bottom: 20px;
}

.mission-card h3 {
    color: #212529;
    margin-bottom: 20px;
    font-size: 1.8rem;
}

.mission-card p {
    color: #6c757d;
    font-size: 1.05rem;
    line-height: 1.8;
}

.value-card {
    background: white;
    padding: 35px 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    height: 100%;
    transition: all 0.3s ease;
}

.value-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.value-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 50%;
    color: white;
    font-size: 2rem;
}

.value-card h5 {
    color: #212529;
    font-weight: 700;
}

/* Equipe */
.team-section {
    padding: 80px 0;
    background: white;
}

.team-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.team-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.team-photo {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 5px solid #007bff;
    box-shadow: 0 5px 20px rgba(0,123,255,0.3);
}

.team-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.team-card h4 {
    color: #212529;
    margin-bottom: 5px;
}

.team-card .social-links {
    margin-top: 20px;
}

.team-card .social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    color: #007bff;
    border-radius: 50%;
    margin: 0 5px;
    transition: all 0.3s ease;
}

.team-card .social-links a:hover {
    background: #007bff;
    color: white;
    transform: translateY(-3px);
}

/* CTA Final */
.cta-about {
    padding: 80px 0;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.cta-about h2 {
    font-size: 2.5rem;
    font-weight: 700;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-about {
        padding: 80px 0 60px;
        padding-top: 156px;
    }
    
    .hero-illustration {
        font-size: 8rem;
    }
    
    .history-card {
        padding: 30px 20px;
    }
    
    .timeline::before {
        left: 30px;
    }
    
    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
        text-align: left !important;
    }
    
    .timeline-item.left,
    .timeline-item.right {
        left: 0;
    }
    
    .timeline-item::after {
        left: 18px !important;
    }
    
    .mission-values,
    .team-section,
    .our-history {
        padding: 50px 0;
    }
    
    .cta-about h2 {
        font-size: 1.8rem;
    }
}

/* Animações */
.timeline-item {
    opacity: 0;
    animation: fadeInUp 0.6s ease-out forwards;
}

.timeline-item:nth-child(1) { animation-delay: 0.2s; }
.timeline-item:nth-child(2) { animation-delay: 0.4s; }
.timeline-item:nth-child(3) { animation-delay: 0.6s; }

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
</style>

<script>
// Animação de entrada dos elementos ao scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observa os cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.value-card, .team-card, .mission-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        observer.observe(card);
    });
});
</script>