<?php
/**
 * NetoNerd - Contato Interno (Funcionários)
 * Página de comunicação interna para técnicos e colaboradores
 * 
 * @package NetoNerd
 * @author NetoNerd Team
 * @version 2.0
 */

// Inicia sessão
session_start();

// Verifica se é funcionário autenticado (adaptar conforme seu sistema)
// if (!isset($_SESSION['funcionario_id'])) {
//     header('Location: loginTecnico.php?login=erro2');
//     exit;
// }

// Configurações da página
$pageTitle = 'Contato Interno - NetoNerd';
$pageDescription = 'Canal de comunicação para funcionários e técnicos';

// Dados de contato interno
$contatos = [
    [
        'tipo' => 'whatsapp',
        'icone' => 'fab fa-whatsapp',
        'titulo' => 'WhatsApp Corporativo',
        'valor' => '(21) 97739-5867',
        'link' => 'https://wa.me/5521977395867?text=Olá,%20sou%20funcionário%20da%20NetoNerd',
        'descricao' => 'Canal prioritário de comunicação',
        'cor' => 'success',
        'destaque' => true,
        'disponibilidade' => 'Seg - Sáb: 8h às 18h'
    ],
    [
        'tipo' => 'email',
        'icone' => 'fas fa-envelope',
        'titulo' => 'E-mail Principal',
        'valor' => 'rondi.rio@hotmail.com',
        'link' => 'mailto:rondi.rio@hotmail.com',
        'descricao' => 'Comunicações formais e relatórios',
        'cor' => 'primary',
        'destaque' => false,
        'disponibilidade' => 'Resposta em até 4h úteis'
    ],
    [
        'tipo' => 'email',
        'icone' => 'fas fa-envelope',
        'titulo' => 'E-mail Alternativo',
        'valor' => 'rondi.rio@gmail.com',
        'link' => 'mailto:rondi.rio@gmail.com',
        'descricao' => 'Canal secundário',
        'cor' => 'info',
        'destaque' => false,
        'disponibilidade' => 'Backup de comunicação'
    ],
    [
        'tipo' => 'emergencia',
        'icone' => 'fas fa-exclamation-triangle',
        'titulo' => 'Emergências',
        'valor' => 'WhatsApp 24h',
        'link' => 'https://wa.me/5521977395867?text=URGENTE:%20',
        'descricao' => 'Apenas casos críticos',
        'cor' => 'danger',
        'destaque' => false,
        'disponibilidade' => 'Disponível 24/7'
    ]
];

// Horários de atendimento interno
$horarios = [
    [
        'dias' => 'Segunda a Sábado',
        'horario' => '08:00 - 18:00',
        'status' => 'aberto',
        'observacao' => 'Horário de atendimento aos funcionários',
        'icone' => 'fas fa-clock'
    ],
    [
        'dias' => 'Domingo',
        'horario' => 'Fechado',
        'status' => 'fechado',
        'observacao' => 'Descanso semanal - Apenas emergências',
        'icone' => 'fas fa-moon'
    ],
    [
        'dias' => 'Emergências',
        'horario' => '24 horas',
        'status' => 'emergencia',
        'observacao' => 'WhatsApp para casos críticos',
        'icone' => 'fas fa-ambulance'
    ]
];

// Mensagem de sucesso
$mensagemEnviada = isset($_GET['enviado']) && $_GET['enviado'] === 'sucesso';

// Inclui o header
include_once '../layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-internal">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <span class="badge badge-warning mb-3">
                    <i class="fas fa-users-cog"></i> Área Interna
                </span>
                <h1 class="display-4 font-weight-bold text-white mb-3">
                    Canal de Comunicação Interna
                </h1>
                <p class="lead text-white mb-4">
                    Central de contato para técnicos e colaboradores da NetoNerd. 
                    Utilize os canais abaixo para comunicação oficial.
                </p>
                <div class="alert alert-warning bg-warning text-dark border-0">
                    <i class="fas fa-shield-alt mr-2"></i>
                    <strong>Importante:</strong> Este canal é exclusivo para funcionários. 
                    Mantenha sempre conduta profissional e ética.
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="hero-icon">
                    <i class="fas fa-headset"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Alerta de Sucesso -->
<?php if ($mensagemEnviada): ?>
<div class="container mt-4">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        <strong>Mensagem enviada!</strong> Seu contato foi registrado no sistema interno.
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Canais de Contato -->
<section class="contact-methods-internal">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Canais de Comunicação</h2>
                <p class="section-subtitle">Escolha o canal adequado para sua necessidade</p>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($contatos as $contato): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-card-internal text-center h-100 <?= $contato['destaque'] ? 'card-featured' : '' ?>">
                    <?php if ($contato['destaque']): ?>
                    <div class="featured-badge">
                        <i class="fas fa-star"></i> Prioritário
                    </div>
                    <?php endif; ?>
                    
                    <div class="contact-icon-internal bg-<?= $contato['cor'] ?>">
                        <i class="<?= $contato['icone'] ?>"></i>
                    </div>
                    
                    <h5 class="mt-4 mb-2"><?= htmlspecialchars($contato['titulo']) ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($contato['descricao']) ?></p>
                    <p class="contact-value-internal mb-2"><?= htmlspecialchars($contato['valor']) ?></p>
                    
                    <div class="disponibilidade mb-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <?= htmlspecialchars($contato['disponibilidade']) ?>
                        </small>
                    </div>
                    
                    <a href="<?= htmlspecialchars($contato['link']) ?>" 
                       class="btn btn-<?= $contato['cor'] ?> btn-sm"
                       target="_blank" rel="noopener">
                        <i class="fas fa-paper-plane"></i> Contatar
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Horários e Regras -->
<section class="schedule-rules">
    <div class="container">
        <div class="row">
            <!-- Horários -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="info-card-internal">
                    <h3 class="mb-4">
                        <i class="fas fa-calendar-alt text-primary"></i> Horários de Atendimento Interno
                    </h3>
                    
                    <ul class="schedule-list">
                        <?php foreach ($horarios as $horario): ?>
                        <li class="schedule-item schedule-<?= $horario['status'] ?>">
                            <div class="schedule-icon">
                                <i class="<?= $horario['icone'] ?>"></i>
                            </div>
                            <div class="schedule-info">
                                <strong><?= htmlspecialchars($horario['dias']) ?></strong>
                                <span class="badge badge-<?= $horario['status'] === 'aberto' ? 'success' : ($horario['status'] === 'emergencia' ? 'danger' : 'secondary') ?>">
                                    <?= htmlspecialchars($horario['horario']) ?>
                                </span>
                                <small class="d-block text-muted mt-1">
                                    <?= htmlspecialchars($horario['observacao']) ?>
                                </small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Atenção:</strong> Atendimento interno exclusivo até às 18:00h. 
                        Após este horário, apenas emergências serão atendidas via WhatsApp.
                    </div>
                </div>
            </div>
            
            <!-- Código de Conduta -->
            <div class="col-lg-6">
                <div class="info-card-internal">
                    <h3 class="mb-4">
                        <i class="fas fa-gavel text-danger"></i> Código de Conduta
                    </h3>
                    
                    <div class="conduct-rules">
                        <div class="rule-item rule-required">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Profissionalismo</strong>
                                <p>Mantenha sempre postura profissional e respeitosa</p>
                            </div>
                        </div>
                        
                        <div class="rule-item rule-required">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Honestidade</strong>
                                <p>Transparência total em todas as comunicações</p>
                            </div>
                        </div>
                        
                        <div class="rule-item rule-required">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Pontualidade</strong>
                                <p>Responda comunicações em até 2 horas úteis</p>
                            </div>
                        </div>
                        
                        <div class="rule-item rule-forbidden">
                            <i class="fas fa-times-circle"></i>
                            <div>
                                <strong>Desonestidade</strong>
                                <p>Mentir, omitir informações ou manipular dados</p>
                            </div>
                        </div>
                        
                        <div class="rule-item rule-forbidden">
                            <i class="fas fa-times-circle"></i>
                            <div>
                                <strong>Apropriação Indevida</strong>
                                <p>Uso não autorizado de recursos da empresa</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger mt-4">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Violações Éticas:</strong> Qualquer comportamento desonesto 
                        resultará em medidas disciplinares severas, incluindo demissão imediata.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Interno -->
<section class="faq-section-internal">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Perguntas Frequentes</h2>
                <p class="section-subtitle">Respostas rápidas para dúvidas internas</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="accordionFAQ">
                    
                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq1">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse1">
                                <i class="fas fa-question-circle"></i> Qual o tempo de resposta esperado para atendimentos internos?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse1" class="collapse show" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Técnicos externos devem atualizar o status do chamado em até 2 horas durante o horário comercial. Para emergências, contatar o suporte interno imediatamente.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq2">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse2">
                                <i class="fas fa-question-circle"></i> Como solicitar informações de contato interno?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse2" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Use a página de contatos internos para localizar e contatar qualquer funcionário. Priorize mensagens pelo chat interno ou telefone corporativo.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq3">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse3">
                                <i class="fas fa-question-circle"></i> Como registrar um atendimento concluído?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse3" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Atualize o status do chamado no sistema, anexe fotos se houver, e registre qualquer observação relevante no histórico do chamado.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq4">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse4">
                                <i class="fas fa-question-circle"></i> Onde encontrar manuais e procedimentos internos?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse4" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Todos os manuais, protocolos e procedimentos estão disponíveis no portal interno. Consulte a seção "Documentos Técnicos" antes de iniciar o atendimento.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq5">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse5">
                                <i class="fas fa-question-circle"></i> Como reportar problemas técnicos durante o atendimento?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse5" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Contate imediatamente o suporte interno via chat corporativo ou telefone. Registre o incidente no sistema e aguarde instruções antes de continuar.
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq6">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse6">
                                <i class="fas fa-question-circle"></i> Qual a postura ética esperada dos técnicos?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse6" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Todos os técnicos devem agir com honestidade, transparência e respeito. Qualquer comportamento desonesto, como manipulação de dados, omissão de informações ou apropriação indevida, será considerado inaceitável e pode levar à demissão imediata.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq7">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse7">
                                <i class="fas fa-question-circle"></i> Como lidar com situações que violam a ética?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse7" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Situações de conduta inadequada devem ser reportadas imediatamente ao suporte interno ou supervisor. A empresa repudia qualquer ato que comprometa a honestidade ou integridade profissional.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq8">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse8">
                                <i class="fas fa-question-circle"></i> Quais atitudes são consideradas inaceitáveis?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse8" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Mentir para clientes, omitir falhas, apropriar-se de equipamentos ou informações da empresa e qualquer violação das regras internas são atitudes inaceitáveis e resultam em medidas disciplinares severas, incluindo demissão.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq9">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse9">
                                <i class="fas fa-question-circle"></i> Qual a postura em relação à comunicação com clientes?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse9" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Comunicação clara, honesta e profissional é obrigatória. Nunca forneça informações falsas ou enganosas. O respeito ao cliente é requisito mínimo de conduta ética.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item-internal">
                        <div class="faq-header-internal" id="faq10">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse10">
                                <i class="fas fa-question-circle"></i> Como a empresa lida com infratores de ética?
                                <i class="fas fa-chevron-down float-right"></i>
                            </button>
                        </div>
                        <div id="collapse10" class="collapse" data-parent="#accordionFAQ">
                            <div class="faq-body-internal">
                                Qualquer violação comprovada das normas éticas é tratada com rigor. A empresa não tolera desonestidade, e infratores podem ser desligados imediatamente, sem aviso prévio.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Acesso Rápido -->
<section class="quick-access">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h3>Acesso Rápido</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="paineltecnico.php" class="quick-link-card">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="app/Views/chamados/criar.php" class="quick-link-card">
                    <i class="fas fa-plus-circle"></i>
                    <span>Abrir Chamado</span>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="#" class="quick-link-card">
                    <i class="fas fa-book"></i>
                    <span>Documentação</span>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="https://wa.me/5521977395867?text=URGENTE:%20" class="quick-link-card quick-link-emergency" target="_blank">
                    <i class="fas fa-ambulance"></i>
                    <span>Emergência</span>
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// Inclui o footer
include_once 'app/Views/layouts/footer.php';
?>

<style>
/* ============================================================================
   ESTILOS - CONTATO INTERNO
   ============================================================================ */

/* Hero Section */
.hero-internal {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 100px 0 80px;
    margin-top: -76px;
    padding-top: 176px;
}

.hero-icon {
    font-size: 10rem;
    color: rgba(255,255,255,0.2);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Contact Cards */
.contact-methods-internal {
    padding: 80px 0;
    background: #f8f9fa;
}

.contact-card-internal {
    background: white;
    padding: 35px 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
}

.contact-card-internal:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.card-featured {
    border: 3px solid #28a745;
    transform: scale(1.02);
}

.featured-badge {
    position: absolute;
    top: -12px;
    right: 15px;
    background: #28a745;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.contact-icon-internal {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    font-size: 1.8rem;
}

.contact-value-internal {
    font-size: 1.05rem;
    font-weight: 600;
    color: #212529;
}

.disponibilidade {
    padding: 8px;
    background: #f8f9fa;
    border-radius: 8px;
}

/* Schedule & Rules */
.schedule-rules {
    padding: 60px 0;
}

.info-card-internal {
    background: white;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    height: 100%;
}

.schedule-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.schedule-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    margin-bottom: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid;
}

.schedule-aberto { border-left-color: #28a745; }
.schedule-fechado { border-left-color: #6c757d; }
.schedule-emergencia { border-left-color: #dc3545; }

.schedule-icon {
    font-size: 2rem;
    color: #007bff;
}

.schedule-info {
    flex: 1;
}

/* Conduct Rules */
.conduct-rules {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.rule-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-radius: 10px;
    background: #f8f9fa;
}

.rule-required {
    border-left: 4px solid #28a745;
}

.rule-required i {
    color: #28a745;
    font-size: 1.5rem;
}

.rule-forbidden {
    border-left: 4px solid #dc3545;
}

.rule-forbidden i {
    color: #dc3545;
    font-size: 1.5rem;
}

.rule-item strong {
    display: block;
    color: #212529;
    margin-bottom: 5px;
}

.rule-item p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

/* FAQ */
.faq-section-internal {
    padding: 80px 0;
    background: #f8f9fa;
}

.faq-item-internal {
    background: white;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.faq-header-internal {
    padding: 0;
}

.faq-header-internal .btn-link {
    width: 100%;
    text-align: left;
    padding: 20px 25px;
    color: #212529;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.faq-header-internal .btn-link:hover {
    background: #f8f9fa;
    border-radius: 10px;
}

.faq-header-internal .btn-link i:first-child {
    color: #007bff;
    margin-right: 10px;
}

.faq-body-internal {
    padding: 0 25px 20px;
    color: #6c757d;
    line-height: 1.8;
}

/* Quick Access */
.quick-access {
    padding: 60px 0;
    background: white;
}

.quick-link-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 15px;
    text-decoration: none;
    color: #495057;
    transition: all 0.3s ease;
    height: 150px;
}

.quick-link-card:hover {
    background: #007bff;
    color: white;
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,123,255,0.3);
    text-decoration: none;
}

.quick-link-card i {
    font-size: 3rem;
    margin-bottom: 10px;
}

.quick-link-card span {
    font-weight: 600;
    font-size: 1.1rem;
}

.quick-link-emergency {
    background: #dc3545;
    color: white;
}

.quick-link-emergency:hover {
    background: #c82333;
    color: white;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Responsive */
@media (max-width: 768px) {
    .hero-internal {
        padding: 80px 0 60px;
        padding-top: 156px;
    }
    
    .hero-icon {
        font-size: 6rem;
    }
    
    .contact-methods-internal,
    .schedule-rules,
    .faq-section-internal {
        padding: 50px 0;
    }
    
    .schedule-item {
        flex-direction: column;
        text-align: center;
    }
    
    .rule-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
// Auto-dismiss de alertas
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);

// Scroll suave para âncoras
$('a[href*="#"]').on('click', function(e) {
    if (this.hash !== "") {
        e.preventDefault();
        const hash = this.hash;
        $('html, body').animate({
            scrollTop: $(hash).offset().top - 100
        }, 800);
    }
});

// Highlight FAQ ativo
$('.collapse').on('show.bs.collapse', function() {
    $(this).prev().find('.btn-link').addClass('active');
    $(this).prev().find('.fa-chevron-down').addClass('fa-rotate-180');
});

$('.collapse').on('hide.bs.collapse', function() {
    $(this).prev().find('.btn-link').removeClass('active');
    $(this).prev().find('.fa-chevron-down').removeClass('fa-rotate-180');
});

// Animação de entrada dos cards
$(document).ready(function() {
    $('.contact-card-internal, .info-card-internal, .quick-link-card').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(30px)'
        });
        
        setTimeout(() => {
            $(this).css({
                'transition': 'all 0.6s ease-out',
                'opacity': '1',
                'transform': 'translateY(0)'
            });
        }, index * 100);
    });
});

// Confirmação para links de emergência
$('.quick-link-emergency').on('click', function(e) {
    if (!confirm('Este canal é apenas para EMERGÊNCIAS reais. Deseja continuar?')) {
        e.preventDefault();
        return false;
    }
});

// Contador de tempo real
function atualizarHorario() {
    const agora = new Date();
    const dia = agora.getDay();
    const hora = agora.getHours();
    
    let status = 'fechado';
    let mensagem = 'Fora do horário de atendimento';
    
    // Segunda a Sábado (1-6), 8h às 18h
    if (dia >= 1 && dia <= 6 && hora >= 8 && hora < 18) {
        status = 'aberto';
        mensagem = 'Horário de atendimento - Estamos disponíveis!';
    } else if (dia === 0) {
        mensagem = 'Domingo - Apenas emergências';
    } else {
        mensagem = 'Fora do horário - WhatsApp disponível para emergências';
    }
    
    // Atualiza badge de status (se existir)
    $('.status-badge').remove();
    $('.hero-internal .container').prepend(`
        <div class="status-badge alert alert-${status === 'aberto' ? 'success' : 'warning'} text-center">
            <i class="fas fa-${status === 'aberto' ? 'check-circle' : 'clock'}"></i>
            <strong>${mensagem}</strong>
        </div>
    `);
}

// Atualiza a cada minuto
atualizarHorario();
setInterval(atualizarHorario, 60000);

// Validação de formulário (se houver)
$('form').on('submit', function(e) {
    const requiredFields = $(this).find('[required]');
    let isValid = true;
    
    requiredFields.each(function() {
        if (!$(this).val()) {
            isValid = false;
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Por favor, preencha todos os campos obrigatórios');
    }
});

// Copiar informação de contato
function copiarContato(texto, elemento) {
    const temp = $('<textarea>');
    $('body').append(temp);
    temp.val(texto).select();
    document.execCommand('copy');
    temp.remove();
    
    // Feedback visual
    const original = $(elemento).html();
    $(elemento).html('<i class="fas fa-check"></i> Copiado!');
    setTimeout(() => {
        $(elemento).html(original);
    }, 2000);
}

// Adiciona botão de copiar nos valores de contato
$('.contact-value-internal').each(function() {
    const valor = $(this).text();
    $(this).css('cursor', 'pointer').attr('title', 'Clique para copiar');
    $(this).on('click', function() {
        copiarContato(valor, this);
    });
});
</script>
</body>
</html>