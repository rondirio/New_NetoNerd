<?php 
require_once "../controller/validador_acesso.php";
require_once "../config/bandoDeDados/conexao.php";

$dados_cliente = obterDadosCliente();

include('../includes/header.php');

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte - NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <style>
        .support-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #667eea;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title::before {
            content: "";
            width: 4px;
            height: 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin-right: 12px;
            border-radius: 2px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            color: white;
        }
        
        .action-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 10px;
        }
        
        .action-description {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .faq-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .faq-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .faq-question {
            font-weight: 600;
            color: #212529;
            font-size: 1.1rem;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .faq-answer {
            color: #495057;
            line-height: 1.6;
            display: none;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            margin-top: 10px;
        }
        
        .faq-answer.show {
            display: block;
        }
        
        .faq-toggle {
            background: #667eea;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: transform 0.3s;
        }
        
        .faq-item.active .faq-toggle {
            transform: rotate(45deg);
        }
        
        .contact-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .contact-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .contact-item:last-child {
            margin-bottom: 0;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }
        
        .contact-details h4 {
            margin: 0;
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .contact-details p {
            margin: 5px 0 0 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .priority-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .priority-high {
            background: #fee;
            color: #c33;
        }
        
        .priority-medium {
            background: #ffeaa7;
            color: #d63031;
        }
        
        .priority-low {
            background: #dfe6e9;
            color: #636e72;
        }
        .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
   <div class="top-navbar">
        <div class="container">
            
        </div>
    </div>
<br><br><br><br>
    <div class="container mt-5">
        <h1 style="text-align: center; color: #212529; margin-bottom: 30px;">
            Central de Suporte
        </h1>

        <!-- Ações Rápidas -->
        <div class="quick-actions">
            <div class="action-card" onclick="window.location.href='abrir_chamado.php'">
                <div class="action-icon">📝</div>
                <div class="action-title">Abrir Chamado</div>
                <div class="action-description">Relate um problema ou solicite suporte técnico</div>
            </div>
            
            <div class="action-card" onclick="window.location.href='home.php'">
                <div class="action-icon">📋</div>
                <div class="action-title">Meus Chamados</div>
                <div class="action-description">Acompanhe o status dos seus chamados</div>
            </div>
            
            <div class="action-card" onclick="scrollToFAQ()">
                <div class="action-icon">❓</div>
                <div class="action-title">Perguntas Frequentes</div>
                <div class="action-description">Encontre respostas para dúvidas comuns</div>
            </div>
            
            <div class="action-card" onclick="window.open('https://wa.me/5521977395867', '_blank')">
                <div class="action-icon">💬</div>
                <div class="action-title">WhatsApp</div>
                <div class="action-description">Fale conosco pelo WhatsApp</div>
            </div>
        </div>

        <!-- Informações de Contato -->
        <div class="contact-info">
            <h3 style="margin-bottom: 25px; font-size: 1.5rem;">📞 Entre em Contato</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="contact-item">
                        <div class="contact-icon">📱</div>
                        <div class="contact-details">
                            <h4>Telefone</h4>
                            <p>(21) 97739-5867</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p>rondi.rio@hotmail.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="contact-item">
                        <div class="contact-icon">🕐</div>
                        <div class="contact-details">
                            <h4>Horário</h4>
                            <p>Seg-Sex: 8h às 18h</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="support-section" id="faq-section">
            <h3 class="section-title">Perguntas Frequentes</h3>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <span>Como abro um chamado de suporte?</span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    Para abrir um chamado, acesse o menu "Abrir Chamado" no topo da página ou clique no botão "Abrir Chamado" na página inicial. Preencha o formulário com título, categoria e descrição detalhada do problema. Você também pode anexar arquivos como screenshots para ajudar nossa equipe a entender melhor a situação.
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <span>Quanto tempo leva para receber uma resposta?</span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    O tempo de resposta depende da prioridade do chamado:
                    <br><br>
                    <span class="priority-badge priority-high">URGENTE</span> Até 2 horas úteis<br>
                    <span class="priority-badge priority-high">ALTA</span> Até 4 horas úteis<br>
                    <span class="priority-badge priority-medium">MÉDIA</span> Até 24 horas úteis<br>
                    <span class="priority-badge priority-low">BAIXA</span> Até 48 horas úteis
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <span>Como acompanho o status do meu chamado?</span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    Você pode acompanhar todos os seus chamados na página "Meus Chamados". Lá você verá o status atualizado de cada chamado (Aberto, Em Atendimento, Aguardando Cliente, Resolvido, Fechado). Você também receberá notificações por email sempre que houver uma atualização.
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <span>Posso alterar um chamado depois de aberto?</span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    Sim! Enquanto o chamado estiver aberto ou em atendimento, você pode adicionar comentários e anexos através da página de detalhes do chamado. Para alterações maiores no título ou categoria, entre em contato com nossa equipe.
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <span>Como alterar meus dados cadastrais?</span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    Acesse a página "Minha Conta" no menu superior. Lá você pode atualizar seu nome, telefone, endereço e senha. O email não pode ser alterado por questões de segurança. Caso precise alterar o email, entre em contato com nossa equipe.
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <span>O que são as categorias de chamado?</span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
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
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <span>Vocês atendem fora do horário comercial?</span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    Nosso horário padrão de atendimento é de segunda a sexta, das 8h às 18h. No entanto, para clientes com planos especiais ou situações de emergência, oferecemos atendimento 24/7. Entre em contato conosco para conhecer nossos planos empresariais.
                </div>
            </div>
        </div>

        <!-- Ainda não encontrou? -->
        <div class="support-section">
            <h3 class="section-title">Ainda precisa de ajuda?</h3>
            <p style="color: #6c757d; margin-bottom: 20px;">
                Se você não encontrou a resposta para sua dúvida, entre em contato conosco pelos canais abaixo ou abra um chamado de suporte.
            </p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="abrir_chamado.php" class="btn btn-custom btn-block" style="padding: 15px;">
                        📝 Abrir Chamado de Suporte
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="https://wa.me/5521977395867" target="_blank" class="btn btn-custom btn-block" style="padding: 15px;">
                        💬 Falar no WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-primary text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-2">© 2025 NetoNerd - Todos os direitos reservados</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleFAQ(element) {
            const answer = element.querySelector('.faq-answer');
            const isActive = element.classList.contains('active');
            
            // Fechar todas as outras FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                item.querySelector('.faq-answer').classList.remove('show');
            });
            
            // Abrir/fechar a FAQ clicada
            if (!isActive) {
                element.classList.add('active');
                answer.classList.add('show');
            }
        }
        
        function scrollToFAQ() {
            document.getElementById('faq-section').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>