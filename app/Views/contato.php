<?php
/**
 * NetoNerd - Página de Contato
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
$pageTitle = 'Contato - NetoNerd';
$pageDescription = 'Entre em contato com a NetoNerd para soluções em tecnologia';

// Dados de contato
$contatos = [
    [
        'tipo' => 'whatsapp',
        'icone' => 'fab fa-whatsapp',
        'titulo' => 'WhatsApp',
        'valor' => '(21) 97739-5867',
        'link' => 'https://wa.me/5521977395867?text=Olá,%20quero%20mais%20informações%20sobre%20os%20serviços%20da%20NetoNerd.',
        'descricao' => 'Atendimento rápido via WhatsApp',
        'cor' => 'success'
    ],
    [
        'tipo' => 'email',
        'icone' => 'fas fa-envelope',
        'titulo' => 'E-mail',
        'valor' => 'contato@netonerd.com',
        'link' => 'mailto:contato@netonerd.com',
        'descricao' => 'Envie-nos um e-mail',
        'cor' => 'primary'
    ],
    [
        'tipo' => 'telefone',
        'icone' => 'fas fa-phone',
        'titulo' => 'Telefone',
        'valor' => '(21) 977395867',
        'link' => 'tel:+5521977395867',
        'descricao' => 'Ligue para nós',
        'cor' => 'info'
    ],
    [
        'tipo' => 'localizacao',
        'icone' => 'fas fa-map-marker-alt',
        'titulo' => 'Localização',
        'valor' => 'Teresópolis, RJ',
        'link' => '#',
        'descricao' => 'Atendemos toda região',
        'cor' => 'warning'
    ]
];

// Horários de atendimento
$horarios = [
    'Segunda a Sábado' => '09:00 - 18:00',
    'Domingo' => 'Fechado',
    // 'Emergências' => '24h (WhatsApp)'
];

// Inclui o header
include_once 'layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-contact">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 font-weight-bold text-white mb-3">
                    Entre em Contato
                </h1>
                <p class="lead text-white-50 mb-4">
                    Estamos prontos para ajudar você com as melhores soluções em tecnologia. 
                    Escolha a forma de contato mais conveniente para você!
                </p>
                <div class="d-flex gap-3">
                    <a href="#contatos" class="btn btn-light btn-lg">
                        <i class="fas fa-comments"></i> Falar Agora
                    </a>
                    <a href="#formulario" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="public/assets/images/contact-illustration.svg" alt="Contato" class="img-fluid" 
                     onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>

<!-- Formas de Contato -->
<section class="contact-methods" id="contatos">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Como Podemos Ajudar?</h2>
                <p class="section-subtitle">Escolha a melhor forma de entrar em contato conosco</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($contatos as $contato): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-card text-center h-100">
                    <div class="contact-icon bg-<?= $contato['cor'] ?>">
                        <i class="<?= $contato['icone'] ?>"></i>
                    </div>
                    <h5 class="mt-4 mb-3"><?= htmlspecialchars($contato['titulo']) ?></h5>
                    <p class="text-muted mb-3"><?= htmlspecialchars($contato['descricao']) ?></p>
                    <p class="contact-value"><?= htmlspecialchars($contato['valor']) ?></p>
                    <a href="<?= htmlspecialchars($contato['link']) ?>" 
                       class="btn btn-outline-<?= $contato['cor'] ?> btn-sm mt-2"
                       target="_blank" rel="noopener">
                        <i class="fas fa-arrow-right"></i> Contatar
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- WhatsApp CTA -->
<section class="whatsapp-cta">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="whatsapp-card">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <i class="fab fa-whatsapp whatsapp-icon"></i>
                            <h3 class="text-white mb-2">Atendimento Rápido pelo WhatsApp</h3>
                            <p class="text-white-50 mb-0">
                                Clique no botão ao lado e inicie uma conversa agora mesmo. 
                                Respondemos rapidamente!
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <a href="https://wa.me/5521977395867?text=Olá,%20quero%20mais%20informações%20sobre%20os%20serviços%20da%20NetoNerd." 
                               class="btn btn-light btn-lg btn-block whatsapp-btn"
                               target="_blank" rel="noopener">
                                <i class="fab fa-whatsapp"></i> Abrir WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Formulário de Contato -->
<section class="contact-form-section" id="formulario">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h2 class="mb-4">Envie sua Mensagem</h2>
                <p class="text-muted mb-4">
                    Preencha o formulário abaixo e entraremos em contato o mais breve possível.
                </p>
                
                <form action="app/Controllers/ContatoController.php" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="enviar_mensagem">
                    
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" name="nome" id="nome" class="form-control" required>
                        <div class="invalid-feedback">Por favor, informe seu nome</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">E-mail *</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                                <div class="invalid-feedback">Por favor, informe um e-mail válido</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefone">Telefone *</label>
                                <input type="tel" name="telefone" id="telefone" class="form-control" 
                                       data-mask="phone" required>
                                <div class="invalid-feedback">Por favor, informe seu telefone</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="assunto">Assunto *</label>
                        <select name="assunto" id="assunto" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="suporte">Suporte Técnico</option>
                            <option value="orcamento">Solicitar Orçamento</option>
                            <option value="duvida">Dúvidas</option>
                            <option value="reclamacao">Reclamação</option>
                            <option value="outros">Outros</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione um assunto</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensagem">Mensagem *</label>
                        <textarea name="mensagem" id="mensagem" rows="5" class="form-control" 
                                  required placeholder="Descreva sua solicitação..."></textarea>
                        <div class="invalid-feedback">Por favor, escreva sua mensagem</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </form>
            </div>
            
            <div class="col-lg-6">
                <!-- Horários de Atendimento -->
                <div class="info-card mb-4">
                    <h4 class="mb-4"><i class="fas fa-clock text-primary"></i> Horários de Atendimento</h4>
                    <ul class="horarios-list">
                        <?php foreach ($horarios as $dia => $horario): ?>
                        <li>
                            <span class="dia"><?= htmlspecialchars($dia) ?></span>
                            <span class="horario"><?= htmlspecialchars($horario) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Informações Adicionais -->
                <div class="info-card">
                    <h4 class="mb-4"><i class="fas fa-info-circle text-info"></i> Por que nos escolher?</h4>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check text-success"></i> Atendimento personalizado</li>
                        <li><i class="fas fa-check text-success"></i> Resposta rápida</li>
                        <li><i class="fas fa-check text-success"></i> Profissionais qualificados</li>
                        <!-- <li><i class="fas fa-check text-success"></i> Suporte 24h emergencial</li> -->
                        <li><i class="fas fa-check text-success"></i> Preços competitivos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mapa -->
<section class="map-section">
    <div class="container-fluid p-0">
        <div id="map" style="height: 400px; width: 100%;">
            <!-- Aqui você pode integrar Google Maps ou outro serviço de mapas -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d146416.7649886984!2d-43.05319!3d-22.41667!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9eaba18a3abae5d9%3A0x0!2sTeres%C3%B3polis%2C%20RJ!5e0!3m2!1spt-BR!2sbr!4v1696790400000"
                width="100%" 
                height="400" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>
</section>

<?php
// Inclui o footer
include_once 'layouts/footer.php';
?>

<style>
/* ============================================================================
   ESTILOS - PÁGINA DE CONTATO
   ============================================================================ */

/* Hero Section */
.hero-contact {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    padding: 100px 0 80px;
    margin-top: -76px;
    padding-top: 176px;
}

/* Contact Methods */
.contact-methods {
    padding: 80px 0;
    background: #f8f9fa;
}

.contact-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.contact-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    font-size: 2rem;
}

.contact-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
}

/* WhatsApp CTA */
.whatsapp-cta {
    padding: 60px 0;
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
}

.whatsapp-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 20px;
    border: 2px solid rgba(255,255,255,0.2);
}

.whatsapp-icon {
    font-size: 4rem;
    color: white;
    float: left;
    margin-right: 20px;
}

.whatsapp-btn {
    font-weight: 600;
    padding: 15px 30px;
}

.whatsapp-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* Formulário */
.contact-form-section {
    padding: 80px 0;
}

.info-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.horarios-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.horarios-list li {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #e9ecef;
}

.horarios-list li:last-child {
    border-bottom: none;
}

.horarios-list .dia {
    font-weight: 600;
    color: #495057;
}

.horarios-list .horario {
    color: #007bff;
    font-weight: 500;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.benefits-list li {
    padding: 10px 0;
    font-size: 1.05rem;
}

.benefits-list i {
    margin-right: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-contact {
        padding: 80px 0 60px;
        padding-top: 156px;
    }
    
    .whatsapp-icon {
        float: none;
        display: block;
        margin: 0 auto 20px;
    }
    
    .contact-methods,
    .contact-form-section {
        padding: 50px 0;
    }
}
</style>

<script>
// Validação do formulário
(function() {
    'use strict';
    window.addEventListener('load', function() {
        const forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>