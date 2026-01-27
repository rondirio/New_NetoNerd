<?php 
require_once "../controller/validador_acesso.php";
include '../config/bandoDeDados/conexao.php';

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Verificar se ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: home.php');
    exit();
}

$chamado_id = intval($_GET['id']);

// Buscar dados do chamado
$stmt = $conn->prepare("
    SELECT c.*, 
           cl.nome as nome_cliente, cl.email as email_cliente, cl.telefone as telefone_cliente,
           t.nome as nome_tecnico, t.email as email_tecnico,
           DATE_FORMAT(c.data_abertura, '%d/%m/%Y às %H:%i') as data_abertura_formatada,
           DATE_FORMAT(c.data_fechamento, '%d/%m/%Y às %H:%i') as data_fechamento_formatada,
           TIMESTAMPDIFF(HOUR, c.data_abertura, COALESCE(c.data_fechamento, NOW())) as horas_decorridas
    FROM chamados c
    INNER JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN tecnicos t ON c.tecnico_id = t.id
    WHERE c.id = ? AND c.cliente_id = ?
");

$stmt->bind_param("ii", $chamado_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: home.php?erro=chamado_nao_encontrado');
    exit();
}

$chamado = $result->fetch_assoc();
$stmt->close();

// Buscar respostas/comentários
$stmt = $conn->prepare("
    SELECT r.*, 
           COALESCE(cl.nome, t.nome) as autor_nome,
           DATE_FORMAT(r.data_resposta, '%d/%m/%Y às %H:%i') as data_formatada,
           CASE 
               WHEN r.id_usuario = c.cliente_id THEN 'cliente'
               ELSE 'tecnico'
           END as tipo_autor
    FROM respostas_chamado r
    LEFT JOIN clientes cl ON r.id_usuario = cl.id
    LEFT JOIN tecnicos t ON r.id_usuario = t.id
    INNER JOIN chamados c ON r.id_chamado = c.id
    WHERE r.id_chamado = ? AND r.tipo_resposta = 'publica'
    ORDER BY r.data_resposta ASC
");

$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$respostas = $stmt->get_result();
$stmt->close();

// Buscar anexos
$stmt = $conn->prepare("
    SELECT * FROM anexos_chamado 
    WHERE id_chamado = ? 
    ORDER BY data_upload DESC
");

$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$anexos = $stmt->get_result();
$stmt->close();

$conn->close();

// Função helper para status
function getStatusBadgeClass($status) {
    $classes = [
        'aberto' => 'badge-status-aberto',
        'em andamento' => 'badge-status-em_andamento',
        'aguardando_cliente' => 'badge-status-aguardando_cliente',
        'resolvido' => 'badge-status-resolvido',
        'fechado' => 'badge-status-fechado',
        'cancelado' => 'badge-status-cancelado'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

function getPrioridadeBadgeClass($prioridade) {
    $classes = [
        'baixa' => 'badge-prioridade-baixa',
        'media' => 'badge-prioridade-media',
        'alta' => 'badge-prioridade-alta',
        'urgente' => 'badge-prioridade-urgente'
    ];
    return $classes[$prioridade] ?? 'badge-secondary';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamado #<?php echo htmlspecialchars($chamado['protocolo']); ?> - NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <style>
        .detalhe-container {
            max-width: 1200px;
            margin: 30px auto;
        }
        
        .chamado-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .protocolo {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .chamado-titulo {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .chamado-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .content-card {
            background: white;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .info-section {
            padding: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1rem;
            color: #212529;
            font-weight: 500;
        }
        
        .descricao-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .timeline {
            padding: 30px;
        }
        
        .timeline-item {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            position: relative;
        }
        
        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 50px;
            bottom: -25px;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
            z-index: 1;
            position: relative;
        }
        
        .timeline-icon.cliente {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .timeline-icon.tecnico {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .timeline-content {
            flex: 1;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
        }
        
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .timeline-autor {
            font-weight: 700;
            color: #212529;
            font-size: 1.1rem;
        }
        
        .timeline-data {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .timeline-texto {
            color: #495057;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .anexos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .anexo-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .anexo-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-3px);
        }
        
        .anexo-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .anexo-nome {
            font-size: 0.9rem;
            font-weight: 600;
            color: #212529;
            word-break: break-all;
            margin-bottom: 5px;
        }
        
        .anexo-data {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .form-responder {
            padding: 30px;
            background: #f8f9fa;
        }
        
        .badge-custom {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-status-aberto { background: #e3f2fd; color: #1976d2; }
        .badge-status-em_andamento { background: #fff3e0; color: #f57c00; }
        .badge-status-aguardando_cliente { background: #f3e5f5; color: #7b1fa2; }
        .badge-status-resolvido { background: #e8f5e9; color: #388e3c; }
        .badge-status-fechado { background: #e0e0e0; color: #616161; }
        .badge-status-cancelado { background: #ffebee; color: #d32f2f; }
        
        .badge-prioridade-baixa { background: #e8f5e9; color: #2e7d32; }
        .badge-prioridade-media { background: #fff3e0; color: #ef6c00; }
        .badge-prioridade-alta { background: #ffebee; color: #c62828; }
        .badge-prioridade-urgente { background: #212529; color: #fff; }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-voltar {
            background: white;
            border: 2px solid #e9ecef;
            color: #495057;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-voltar:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
            text-decoration: none;
            color: #495057;
        }
        
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .chamado-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .timeline-item {
                flex-direction: column;
            }
            
            .timeline-item::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom bg-primary">
        <a class="navbar-brand" href="home.php">
            <img class="logo" src="imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse LinksNav" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="home.php">Meus Chamados</a></li>
                <li class="nav-item"><a class="nav-link" href="minha_conta.php">Minha Conta</a></li>
                <li class="nav-item"><a class="nav-link" href="suporte.php">Suporte</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-white bg-dark ml-2" href="logoff.php">Sair</a></li>
            </ul>
        </div>
    </nav>

    <div class="container detalhe-container">
        <!-- Actions Bar -->
        <div class="actions-bar">
            <a href="home.php" class="btn-voltar">← Voltar para Meus Chamados</a>
            
            <?php if (in_array($chamado['status'], ['aberto', 'em andamento'])): ?>
                <a href="editar_chamado.php?id=<?php echo $chamado['id']; ?>" class="btn btn-warning">
                    ✏️ Editar Chamado
                </a>
            <?php endif; ?>
        </div>

        <!-- Header do Chamado -->
        <div class="chamado-header">
            <div class="protocolo">Protocolo: #<?php echo htmlspecialchars($chamado['protocolo']); ?></div>
            <div class="chamado-titulo"><?php echo htmlspecialchars($chamado['titulo']); ?></div>
            <div class="chamado-meta">
                <div class="meta-item">
                    <span class="badge-custom <?php echo getStatusBadgeClass($chamado['status']); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $chamado['status'])); ?>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="badge-custom <?php echo getPrioridadeBadgeClass($chamado['prioridade']); ?>">
                        Prioridade: <?php echo ucfirst($chamado['prioridade']); ?>
                    </span>
                </div>
                <div class="meta-item">
                    📅 Aberto em: <?php echo $chamado['data_abertura_formatada']; ?>
                </div>
                <div class="meta-item">
                    ⏱️ <?php echo $chamado['horas_decorridas']; ?> horas decorridas
                </div>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="content-card">
            <!-- Informações Gerais -->
            <div class="info-section">
                <div class="section-title">📋 Informações do Chamado</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Categoria</div>
                        <div class="info-value"><?php echo htmlspecialchars($chamado['categoria']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Técnico Responsável</div>
                        <div class="info-value">
                            <?php echo $chamado['nome_tecnico'] ? htmlspecialchars($chamado['nome_tecnico']) : 'Aguardando atribuição'; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data de Abertura</div>
                        <div class="info-value"><?php echo $chamado['data_abertura_formatada']; ?></div>
                    </div>
                    <?php if ($chamado['data_fechamento']): ?>
                    <div class="info-item">
                        <div class="info-label">Data de Fechamento</div>
                        <div class="info-value"><?php echo $chamado['data_fechamento_formatada']; ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Descrição -->
            <div class="info-section">
                <div class="section-title">📝 Descrição do Problema</div>
                <div class="descricao-box">
<?php echo htmlspecialchars($chamado['descricao']); ?>
                </div>
            </div>

            <!-- Anexos -->
            <?php if ($anexos->num_rows > 0): ?>
            <div class="info-section">
                <div class="section-title">📎 Anexos (<?php echo $anexos->num_rows; ?>)</div>
                <div class="anexos-grid">
                    <?php while ($anexo = $anexos->fetch_assoc()): ?>
                        <a href="<?php echo htmlspecialchars($anexo['caminho_arquivo']); ?>" 
                           target="_blank" class="anexo-card" style="text-decoration: none;">
                            <div class="anexo-icon">
                                <?php 
                                $ext = pathinfo($anexo['nome_arquivo'], PATHINFO_EXTENSION);
                                echo in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? '🖼️' : '📄';
                                ?>
                            </div>
                            <div class="anexo-nome"><?php echo htmlspecialchars($anexo['nome_arquivo']); ?></div>
                            <div class="anexo-data">
                                <?php echo date('d/m/Y', strtotime($anexo['data_upload'])); ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Timeline de Interações -->
            <div class="timeline">
                <div class="section-title">💬 Histórico de Interações</div>
                
                <?php if ($respostas->num_rows > 0): ?>
                    <?php while ($resposta = $respostas->fetch_assoc()): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon <?php echo $resposta['tipo_autor']; ?>">
                                <?php echo $resposta['tipo_autor'] === 'cliente' ? '👤' : '🔧'; ?>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="timeline-autor">
                                        <?php echo htmlspecialchars($resposta['autor_nome']); ?>
                                        <span style="font-weight: normal; color: #6c757d; font-size: 0.9rem;">
                                            (<?php echo $resposta['tipo_autor'] === 'cliente' ? 'Você' : 'Técnico'; ?>)
                                        </span>
                                    </div>
                                    <div class="timeline-data"><?php echo $resposta['data_formatada']; ?></div>
                                </div>
                                <div class="timeline-texto"><?php echo htmlspecialchars($resposta['resposta']); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">💬</div>
                        <p>Ainda não há interações neste chamado.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulário de Resposta -->
            <?php if (!in_array($chamado['status'], ['fechado', 'cancelado'])): ?>
            <div class="form-responder">
                <div class="section-title">✍️ Adicionar Comentário</div>
                <form method="POST" action="adicionar_resposta.php" id="formResposta">
                    <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
                    
                    <div class="form-group">
                        <textarea name="resposta" class="form-control" rows="5" 
                                  placeholder="Digite sua mensagem aqui..." 
                                  required maxlength="5000"></textarea>
                        <small class="form-text text-muted">
                            Use este espaço para adicionar informações, fazer perguntas ou enviar atualizações.
                        </small>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <?php if ($chamado['status'] === 'resolvido'): ?>
                                <button type="button" class="btn btn-success" onclick="confirmarResolucao()">
                                    ✅ Confirmar Resolução
                                </button>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary-custom">
                            📤 Enviar Comentário
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
                <div class="info-section text-center" style="background: #f8f9fa;">
                    <p style="margin: 0; color: #6c757d;">
                        Este chamado está <?php echo $chamado['status']; ?>. Não é possível adicionar novos comentários.
                    </p>
                </div>
            <?php endif; ?>
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
        function confirmarResolucao() {
            if (confirm('Confirma que o problema foi resolvido? O chamado será marcado como fechado.')) {
                window.location.href = 'fechar_chamado.php?id=<?php echo $chamado['id']; ?>';
            }
        }
        
        // Validação do formulário
        document.getElementById('formResposta').addEventListener('submit', function(e) {
            const textarea = this.querySelector('textarea');
            if (textarea.value.trim().length < 10) {
                e.preventDefault();
                alert('Por favor, escreva uma mensagem com pelo menos 10 caracteres.');
                return false;
            }
        });
    </script>
</body>
</html>