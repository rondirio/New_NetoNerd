<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - NetoNerd</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .cadastro-container {
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .cadastro-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .cadastro-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .cadastro-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid white;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .cadastro-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .cadastro-header p {
            margin: 10px 0 0;
            opacity: 0.95;
            font-size: 0.95rem;
        }
        
        .cadastro-body {
            padding: 40px 30px;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: #e9ecef;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #999;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background: #007bff;
            color: white;
            box-shadow: 0 0 0 4px rgba(0,123,255,0.2);
        }
        
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        
        .step-label {
            font-size: 0.75rem;
            color: #999;
            font-weight: 600;
        }
        
        .step.active .step-label {
            color: #007bff;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px 12px 45px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .form-control.is-valid {
            border-color: #28a745;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .valid-feedback {
            display: block;
            color: #28a745;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .password-strength {
            margin-top: 10px;
        }
        
        .strength-bar {
            height: 5px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 10px;
        }
        
        .strength-text {
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .btn-step {
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,123,255,0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .back-login {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .back-login a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-login a:hover {
            text-decoration: underline;
        }
        
        .terms-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            max-height: 150px;
            overflow-y: auto;
            font-size: 0.85rem;
            color: #666;
        }
        
        @media (max-width: 576px) {
            .cadastro-body {
                padding: 30px 20px;
            }
            
            .btn-step {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="cadastro-container">
        <div class="cadastro-card">
            <!-- Header -->
            <div class="cadastro-header">
                <img src="../src/imagens/logoNetoNerd.jpg" alt="NetoNerd" class="cadastro-logo">
                <h2>Criar Conta</h2>
                <p>Preencha os dados abaixo para se cadastrar</p>
            </div>

            <!-- Body -->
            <div class="cadastro-body">
                <!-- Indicador de Etapas -->
                <div class="step-indicator">
                    <div class="step active" id="step-indicator-1">
                        <div class="step-number">1</div>
                        <div class="step-label">Dados Pessoais</div>
                    </div>
                    <div class="step" id="step-indicator-2">
                        <div class="step-number">2</div>
                        <div class="step-label">Endereço</div>
                    </div>
                    <div class="step" id="step-indicator-3">
                        <div class="step-number">3</div>
                        <div class="step-label">Senha</div>
                    </div>
                </div>

                <!-- Mensagens de Erro -->
                <?php if(isset($_GET['erro'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                    $erros = [
                        'email_existe' => 'Este email já está cadastrado.',
                        'senhas_diferentes' => 'As senhas não conferem.',
                        'campos_vazios' => 'Por favor, preencha todos os campos obrigatórios.',
                        'email_invalido' => 'Email inválido.',
                        'senha_fraca' => 'A senha deve ter no mínimo 8 caracteres.'
                    ];
                    echo $erros[$_GET['erro']] ?? 'Erro ao processar cadastro.';
                    ?>
                </div>
                <?php endif; ?>

                <!-- Formulário Multi-Etapas -->
                <form action="../controller/processa_cadastro.php" method="POST" id="cadastroForm">
                    <!-- ETAPA 1: Dados Pessoais -->
                    <div class="form-step active" id="step-1">
                        <div class="form-group">
                            <label for="nome">
                                <i class="fas fa-user"></i> Nome Completo *
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="nome" 
                                       name="nome" 
                                       placeholder="Seu nome completo"
                                       required>
                            </div>
                            <small class="text-muted">Mínimo 3 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email *
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="seu@email.com"
                                       required>
                            </div>
                            <div class="invalid-feedback" id="email-feedback"></div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefone">
                                        <i class="fas fa-phone"></i> Telefone/WhatsApp *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="telefone" 
                                               name="telefone" 
                                               placeholder="(21) 99999-9999"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="genero">
                                        <i class="fas fa-venus-mars"></i> Gênero
                                    </label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-venus-mars"></i>
                                        </span>
                                        <select class="form-control" id="genero" name="genero">
                                            <option value="">Selecione</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                            <option value="Outro">Prefiro não informar</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary btn-step" onclick="nextStep(2)">
                            Próximo <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                    <!-- ETAPA 2: Endereço -->
                    <div class="form-step" id="step-2">
                        <div class="form-group">
                            <label for="cep">
                                <i class="fas fa-map-marker-alt"></i> CEP
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="cep" 
                                       name="cep" 
                                       placeholder="00000-000"
                                       maxlength="9">
                            </div>
                            <small class="text-muted">Preencha para buscar automaticamente</small>
                        </div>

                        <div class="form-group">
                            <label for="endereco">
                                <i class="fas fa-home"></i> Endereço *
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-home"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="endereco" 
                                       name="endereco" 
                                       placeholder="Rua, número, bairro"
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="complemento">
                                <i class="fas fa-building"></i> Complemento
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-building"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="complemento" 
                                       name="complemento" 
                                       placeholder="Apto, bloco, etc (opcional)">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-secondary btn-step" onclick="previousStep(1)">
                                    <i class="fas fa-arrow-left"></i> Voltar
                                </button>
                            </div>
                            <div class="col-6 text-right">
                                <button type="button" class="btn btn-primary btn-step" onclick="nextStep(3)">
                                    Próximo <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ETAPA 3: Senha e Confirmação -->
                    <div class="form-step" id="step-3">
                        <div class="form-group">
                            <label for="senha">
                                <i class="fas fa-lock"></i> Senha *
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="senha" 
                                       name="senha" 
                                       placeholder="Mínimo 8 caracteres"
                                       required
                                       onkeyup="checkPasswordStrength()">
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text" id="strengthText"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirma_senha">
                                <i class="fas fa-lock"></i> Confirmar Senha *
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirma_senha" 
                                       name="confirma_senha" 
                                       placeholder="Digite a senha novamente"
                                       required
                                       onkeyup="checkPasswordMatch()">
                            </div>
                            <div class="invalid-feedback" id="password-match-feedback"></div>
                        </div>

                        <div class="terms-box">
                            <strong>Termos e Condições:</strong><br>
                            Ao criar uma conta na NetoNerd, você concorda com nossos termos de uso e política de privacidade. 
                            Seus dados pessoais serão utilizados exclusivamente para prestação de serviços e não serão compartilhados 
                            com terceiros sem sua autorização. Você pode solicitar exclusão de seus dados a qualquer momento.
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="aceito_termos" required>
                            <label class="form-check-label" for="aceito_termos">
                                Li e aceito os <a href="termos.php" target="_blank">termos de uso</a> e 
                                <a href="privacidade.php" target="_blank">política de privacidade</a>
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-secondary btn-step" onclick="previousStep(2)">
                                    <i class="fas fa-arrow-left"></i> Voltar
                                </button>
                            </div>
                            <div class="col-6 text-right">
                                <button type="submit" class="btn btn-primary btn-step">
                                    <i class="fas fa-check"></i> Cadastrar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="back-login">
                    <p>Já tem uma conta? <a href="login.php">Fazer Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        let currentStep = 1;

        // Navegação entre etapas
        function nextStep(step) {
            if (validateStep(currentStep)) {
                document.getElementById('step-' + currentStep).classList.remove('active');
                document.getElementById('step-indicator-' + currentStep).classList.remove('active');
                document.getElementById('step-indicator-' + currentStep).classList.add('completed');
                
                currentStep = step;
                document.getElementById('step-' + step).classList.add('active');
                document.getElementById('step-indicator-' + step).classList.add('active');
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function previousStep(step) {
            document.getElementById('step-' + currentStep).classList.remove('active');
            document.getElementById('step-indicator-' + currentStep).classList.remove('active');
            
            currentStep = step;
            document.getElementById('step-' + step).classList.add('active');
            document.getElementById('step-indicator-' + step).classList.add('active');
            document.getElementById('step-indicator-' + step).classList.remove('completed');
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Validação de cada etapa
        function validateStep(step) {
            if (step === 1) {
                const nome = document.getElementById('nome').value.trim();
                const email = document.getElementById('email').value.trim();
                const telefone = document.getElementById('telefone').value.trim();

                if (nome.length < 3) {
                    alert('Nome deve ter no mínimo 3 caracteres');
                    return false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Email inválido');
                    return false;
                }

                if (telefone.replace(/\D/g, '').length < 10) {
                    alert('Telefone inválido');
                    return false;
                }

                return true;
            }

            if (step === 2) {
                const endereco = document.getElementById('endereco').value.trim();
                
                if (endereco.length < 5) {
                    alert('Por favor, preencha o endereço completo');
                    return false;
                }

                return true;
            }

            return true;
        }

        // Força senha
        function checkPasswordStrength() {
            const senha = document.getElementById('senha').value;
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');

            let strength = 0;
            if (senha.length >= 8) strength++;
            if (senha.match(/[a-z]/) && senha.match(/[A-Z]/)) strength++;
            if (senha.match(/\d/)) strength++;
            if (senha.match(/[^a-zA-Z\d]/)) strength++;

            const colors = ['#dc3545', '#ffc107', '#28a745', '#007bff'];
            const texts = ['Fraca', 'Média', 'Forte', 'Muito Forte'];
            const widths = ['25%', '50%', '75%', '100%'];

            if (senha.length > 0) {
                fill.style.width = widths[strength];
                fill.style.background = colors[strength];
                text.textContent = texts[strength];
                text.style.color = colors[strength];
            } else {
                fill.style.width = '0%';
                text.textContent = '';
            }
        }

        // Conferir senhas
        function checkPasswordMatch() {
            const senha = document.getElementById('senha').value;
            const confirmaSenha = document.getElementById('confirma_senha').value;
            const feedback = document.getElementById('password-match-feedback');

            if (confirmaSenha.length > 0) {
                if (senha === confirmaSenha) {
                    document.getElementById('confirma_senha').classList.remove('is-invalid');
                    document.getElementById('confirma_senha').classList.add('is-valid');
                    feedback.textContent = '✓ Senhas conferem';
                    feedback.classList.remove('invalid-feedback');
                    feedback.classList.add('valid-feedback');
                } else {
                    document.getElementById('confirma_senha').classList.remove('is-valid');
                    document.getElementById('confirma_senha').classList.add('is-invalid');
                    feedback.textContent = '✗ Senhas não conferem';
                    feedback.classList.remove('valid-feedback');
                    feedback.classList.add('invalid-feedback');
                }
            }
        }

        // Máscara de telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d)(\d{4})$/, '$1-$2');
                e.target.value = value;
            }
        });

        // Máscara de CEP
        document.getElementById('cep').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });

        // Busca CEP
        document.getElementById('cep').addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco').value = 
                                `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                        }
                    })
                    .catch(error => console.log('Erro ao buscar CEP:', error));
            }
        });

        // Validação final do formulário
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmaSenha = document.getElementById('confirma_senha').value;
            const aceitoTermos = document.getElementById('aceito_termos').checked;

            if (senha.length < 8) {
                e.preventDefault();
                alert('A senha deve ter no mínimo 8 caracteres');
                return false;
            }

            if (senha !== confirmaSenha) {
                e.preventDefault();
                alert('As senhas não conferem');
                return false;
            }

            if (!aceitoTermos) {
                e.preventDefault();
                alert('Você precisa aceitar os termos de uso');
                return false;
            }
        });
    </script>
</body>
</html>