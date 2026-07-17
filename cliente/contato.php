<?php
require_once "../controller/validador_acesso.php";
require_once "../controller/auth_middleware.php";
require_once "../config/bandoDeDados/conexao.php";

requireCliente();

$dados_cliente = obterDadosCliente();

$page_title = "Suporte - NetoNerd ITSM";
$extra_css = '<style>
    .nn-quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }

    .nn-action-card {
        background: white;
        padding: 25px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .nn-action-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-blue);
    }

    .nn-action-icon {
        width: 55px;
        height: 55px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 1.6rem;
        color: white;
    }

    .nn-action-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .nn-contact-info {
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--radius-lg);
    }

    .nn-contact-item {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .nn-contact-icon {
        width: 46px;
        height: 46px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .nn-contact-details h4 {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
        font-weight: 500;
    }

    .nn-contact-details p {
        margin: 4px 0 0 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .nn-faq-item {
        background: var(--bg-light);
        border-radius: var(--radius-md);
        padding: 18px 20px;
        margin-bottom: 12px;
        border-left: 4px solid var(--primary-blue);
        cursor: pointer;
        transition: all 0.3s;
    }

    .nn-faq-item:hover {
        background: var(--bg-lighter);
    }

    .nn-faq-question {
        font-weight: 600;
        font-size: 1.05rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nn-faq-answer {
        color: var(--text-medium);
        line-height: 1.6;
        display: none;
        padding-top: 10px;
        border-top: 1px solid var(--bg-lighter);
        margin-top: 10px;
    }

    .nn-faq-answer.show {
        display: block;
    }

    .nn-faq-toggle {
        background: var(--primary-blue);
        color: white;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        transition: transform 0.3s;
    }

    .nn-faq-item.active .nn-faq-toggle {
        transform: rotate(45deg);
    }

    .nn-priority-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: var(--radius-full);
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 8px;
    }

    .nn-priority-high { background: #fee; color: #c33; }
    .nn-priority-medium { background: #ffeaa7; color: #d63031; }
    .nn-priority-low { background: #dfe6e9; color: #636e72; }
</style>';
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-headset"></i>
                    Central de Suporte
                </h1>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="nn-card">
            <div class="nn-card-body">
                <div class="nn-quick-actions">
                    <div class="nn-action-card" onclick="window.location.href='abrir_chamado.php'">
                        <div class="nn-action-icon"><i class="fas fa-file-circle-plus"></i></div>
                        <div class="nn-action-title">Abrir Chamado</div>
                        <div class="nn-text-light">Relate um problema ou solicite suporte técnico</div>
                    </div>

                    <div class="nn-action-card" onclick="window.location.href='home.php'">
                        <div class="nn-action-icon"><i class="fas fa-list-check"></i></div>
                        <div class="nn-action-title">Meus Chamados</div>
                        <div class="nn-text-light">Acompanhe o status dos seus chamados</div>
                    </div>

                    <div class="nn-action-card" onclick="scrollToFAQ()">
                        <div class="nn-action-icon"><i class="fas fa-circle-question"></i></div>
                        <div class="nn-action-title">Perguntas Frequentes</div>
                        <div class="nn-text-light">Encontre respostas para dúvidas comuns</div>
                    </div>

                    <div class="nn-action-card" onclick="window.open('https://wa.me/5521977395867', '_blank')">
                        <div class="nn-action-icon"><i class="fab fa-whatsapp"></i></div>
                        <div class="nn-action-title">WhatsApp</div>
                        <div class="nn-text-light">Fale conosco pelo WhatsApp</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações de Contato -->
        <div class="nn-card nn-contact-info">
            <div class="nn-card-body">
                <h3 class="nn-mb-2" style="color: white;"><i class="fas fa-phone"></i> Entre em Contato</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="nn-contact-item">
                            <div class="nn-contact-icon"><i class="fas fa-mobile-screen"></i></div>
                            <div class="nn-contact-details">
                                <h4>Telefone</h4>
                                <p>(21) 97739-5867</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="nn-contact-item">
                            <div class="nn-contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="nn-contact-details">
                                <h4>Email</h4>
                                <p>rondi.rio@hotmail.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="nn-contact-item">
                            <div class="nn-contact-icon"><i class="fas fa-clock"></i></div>
                            <div class="nn-contact-details">
                                <h4>Horário</h4>
                                <p>Seg-Sex: 8h às 18h</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="nn-card" id="faq-section">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-circle-question"></i>
                    Perguntas Frequentes
                </h2>
            </div>
            <div class="nn-card-body">
                <div class="nn-faq-item" onclick="toggleFAQ(this)">
                    <div class="nn-faq-question">
                        <span>Como abro um chamado de suporte?</span>
                        <span class="nn-faq-toggle">+</span>
                    </div>
                    <div class="nn-faq-answer">
                        Para abrir um chamado, acesse o menu "Abrir Chamado" no topo da página ou clique no botão "Abrir Chamado" na página inicial. Preencha o formulário com título, categoria e descrição detalhada do problema. Você também pode anexar arquivos como screenshots para ajudar nossa equipe a entender melhor a situação.
                    </div>
                </div>

                <div class="nn-faq-item" onclick="toggleFAQ(this)">
                    <div class="nn-faq-question">
                        <span>Quanto tempo leva para receber uma resposta?</span>
                        <span class="nn-faq-toggle">+</span>
                    </div>
                    <div class="nn-faq-answer">
                        O tempo de resposta depende da prioridade do chamado:
                        <br><br>
                        <span class="nn-priority-badge nn-priority-high">URGENTE</span> Até 2 horas úteis<br>
                        <span class="nn-priority-badge nn-priority-high">ALTA</span> Até 4 horas úteis<br>
                        <span class="nn-priority-badge nn-priority-medium">MÉDIA</span> Até 24 horas úteis<br>
                        <span class="nn-priority-badge nn-priority-low">BAIXA</span> Até 48 horas úteis
                    </div>
                </div>

                <div class="nn-faq-item" onclick="toggleFAQ(this)">
                    <div class="nn-faq-question">
                        <span>Como acompanho o status do meu chamado?</span>
                        <span class="nn-faq-toggle">+</span>
                    </div>
                    <div class="nn-faq-answer">
                        Você pode acompanhar todos os seus chamados na página "Meus Chamados". Lá você verá o status atualizado de cada chamado (Aberto, Em Atendimento, Aguardando Cliente, Resolvido, Fechado). Você também receberá notificações por email sempre que houver uma atualização.
                    </div>
                </div>

                <div class="nn-faq-item" onclick="toggleFAQ(this)">
                    <div class="nn-faq-question">
                        <span>Posso alterar um chamado depois de aberto?</span>
                        <span class="nn-faq-toggle">+</span>
                    </div>
                    <div class="nn-faq-answer">
                        Sim! Enquanto o chamado estiver aberto ou em atendimento, você pode adicionar comentários e anexos através da página de detalhes do chamado. Para alterações maiores no título ou categoria, entre em contato com nossa equipe.
                    </div>
                </div>

                <div class="nn-faq-item" onclick="toggleFAQ(this)">
                    <div class="nn-faq-question">
                        <span>Como alterar meus dados cadastrais?</span>
                        <span class="nn-faq-toggle">+</span>
                    </div>
                    <div class="nn-faq-answer">
                        Acesse a página "Minha Conta" no menu superior. Lá você pode atualizar seu nome, telefone, endereço e senha. O email não pode ser alterado por questões de segurança. Caso precise alterar o email, entre em contato com nossa equipe.
                    </div>
                </div>

                <div class="nn-faq-item" onclick="toggleFAQ(this)">
                    <div class="nn-faq-question">
                        <span>O que são as categorias de chamado?</span>
                        <span class="nn-faq-toggle">+</span>
                    </div>
                    <div class="nn-faq-answer">
                        As categorias ajudam nossa equipe a direcionar seu chamado para o técnico especialista correto. Escolha a categoria que melhor descreve seu problema:
                        <ul style="margin-top: 10px; padding-left: 20px;">
                            <li><strong>Hardware:</strong> Problemas físicos com computadores, periféricos</li>
                            <li><strong>Software:</strong> Problemas com programas, instalações, atualizações</li>
                            <li><strong>Rede:</strong> Internet, Wi-Fi, conexões</li>
                            <li><strong>Segurança:</strong> Vírus, antivírus, firewall</li>
                            <li><strong>Outros:</strong> Problemas que não se encaixam nas categorias acima</li>
                        </ul>
                    </div>
                </div>

                <div class="nn-faq-item" onclick="toggleFAQ(this)">
                    <div class="nn-faq-question">
                        <span>Vocês atendem fora do horário comercial?</span>
                        <span class="nn-faq-toggle">+</span>
                    </div>
                    <div class="nn-faq-answer">
                        Nosso horário padrão de atendimento é de segunda a sexta, das 8h às 18h. No entanto, para clientes com planos especiais ou situações de emergência, oferecemos atendimento 24/7. Entre em contato conosco para conhecer nossos planos empresariais.
                    </div>
                </div>
            </div>
        </div>

        <!-- Ainda não encontrou? -->
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-life-ring"></i>
                    Ainda precisa de ajuda?
                </h2>
            </div>
            <div class="nn-card-body">
                <p class="nn-text-medium nn-mb-2">
                    Se você não encontrou a resposta para sua dúvida, entre em contato conosco pelos canais abaixo ou abra um chamado de suporte.
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="abrir_chamado.php" class="nn-btn nn-btn-primary nn-btn-lg" style="width: 100%; justify-content: center;">
                            <i class="fas fa-file-circle-plus"></i>
                            Abrir Chamado de Suporte
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="https://wa.me/5521977395867" target="_blank" class="nn-btn nn-btn-primary nn-btn-lg" style="width: 100%; justify-content: center;">
                            <i class="fab fa-whatsapp"></i>
                            Falar no WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$extra_js = '<script>
    function toggleFAQ(element) {
        const answer = element.querySelector(".nn-faq-answer");
        const isActive = element.classList.contains("active");

        document.querySelectorAll(".nn-faq-item").forEach(function (item) {
            item.classList.remove("active");
            item.querySelector(".nn-faq-answer").classList.remove("show");
        });

        if (!isActive) {
            element.classList.add("active");
            answer.classList.add("show");
        }
    }

    function scrollToFAQ() {
        document.getElementById("faq-section").scrollIntoView({ behavior: "smooth" });
    }
</script>';
require_once '../includes/footer.php';
?>
