<?php
require_once "../controller/validador_acesso.php";

// Apenas clientes podem acessar
// if (!isCliente()) {
//     header('Location: ' . (isAdmin() ? 'dashboard.php' : 'paineltecnico.php'));
//     exit();
// }

require_once "../config/bandoDeDados/conexao.php";
$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Estatísticas do cliente
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status IN ('aberto', 'em andamento') THEN 1 ELSE 0 END) as ativos,
        SUM(CASE WHEN status = 'resolvido' THEN 1 ELSE 0 END) as resolvidos,
        SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados
    FROM chamados 
    WHERE cliente_id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - NetoNerd</title>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    
    <style>
        body {
            background: #f8f9fa;
        }
        
        .top-navbar {
            background: linear-gradient(135deg, #007bff, #0056b3);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .top-navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #007bff;
            font-size: 1.2rem;
        }
        
        .user-details h6 {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .user-details small {
            opacity: 0.9;
            font-size: 0.85rem;
        }
        
        .dashboard-header {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .dashboard-subtitle {
            color: #666;
            font-size: 1rem;
        }
        
        .stats-row {
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .stat-icon-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        
        .stat-icon-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        
        .stat-icon-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: white;
        }
        
        .stat-icon-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.95rem;
            font-weight: 600;
        }
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            background: white;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .action-btn:hover {
            border-color: #007bff;
            background: #f8f9fa;
            transform: translateX(5px);
            text-decoration: none;
            color: #007bff;
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .chamados-recentes {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .chamado-item {
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .chamado-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .chamado-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .chamado-titulo {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .chamado-protocolo {
            color: #666;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .chamado-info {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .badge-custom {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-aberto {
            background: #007bff;
            color: white;
        }
        
        .badge-andamento {
            background: #ffc107;
            color: white;
        }
        
        .badge-resolvido {
            background: #28a745;
            color: white;
        }
        
        .badge-cancelado {
            background: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .user-info {
                flex-direction: column;
                text-align: center;
            }
            
            .stat-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="container">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h6>
                        <?php 
                        echo ($cliente['genero'] === 'Feminino' ? 'Bem-vinda, ' : 'Bem-vindo, ') . 
                             htmlspecialchars(explode(' ', $cliente['nome'])[0]); 
                        ?>
                    </h6>
                    <small><?php echo htmlspecialchars($cliente['email']); ?></small>
                </div>
            </div>
            <div>
                <a href="logoff.php" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-title">Minha Conta</h1>
            <p class="dashboard-subtitle">Gerencie seus chamados e acompanhe o status dos atendimentos</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Estatísticas -->
        <div class="row stats-row">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total de Chamados</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['ativos']; ?></div>
                    <div class="stat-label">Em Atendimento</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['resolvidos']; ?></div>
                    <div class="stat-label">Resolvidos</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['cancelados']; ?></div>
                    <div class="stat-label">Cancelados</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Ações Rápidas -->
            <div class="col-lg-4 mb-4">
                <div class="quick-actions">
                    <h5 class="section-title">
                        <i class="fas fa-bolt"></i> Ações Rápidas
                    </h5>
                    
                    <a href="abrir_chamado.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <strong>Novo Chamado</strong><br>
                            <small>Solicitar atendimento técnico</small>
                        </div>
                    </a>
                    
                    <a href="meus_chamados.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div>
                            <strong>Meus Chamados</strong><br>
                            <small>Ver todos os atendimentos</small>
                        </div>
                    </a>
                    
                    <a href="minha_conta.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div>
                            <strong>Minha Conta</strong><br>
                            <small>Editar dados pessoais</small>
                        </div>
                    </a>
                    
                    <a href="contato.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div>
                            <strong>Suporte</strong><br>
                            <small>Falar com nossa equipe</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Chamados Recentes -->
            <div class="col-lg-8 mb-4">
                <div class="chamados-recentes">
                    <h5 class="section-title">
                        <i class="fas fa-history"></i> Chamados Recentes
                    </h5>
                    
                    <?php
                    $stmt = $conn->prepare("
                        SELECT * FROM chamados 
                        WHERE cliente_id = ? 
                        ORDER BY data_abertura DESC 
                        LIMIT 5
                    ");
                    $stmt->bind_param("i", $usuario_id);
                    $stmt->execute();
                    $chamados = $stmt->get_result();
                    
                    if ($chamados->num_rows > 0):
                        while ($chamado = $chamados->fetch_assoc()):
                    ?>
                    <div class="chamado-item" onclick="window.location.href='visualizar_chamado.php?id=<?php echo $chamado['id']; ?>'">
                        <div class="chamado-header">
                            <div class="chamado-titulo">
                                <?php echo htmlspecialchars($chamado['titulo']); ?>
                            </div>
                            <div class="chamado-protocolo">
                                #<?php echo htmlspecialchars($chamado['protocolo']); ?>
                            </div>
                        </div>
                        <div class="chamado-info">
                            <span class="badge-custom badge-<?php 
                                echo $chamado['status'] == 'aberto' ? 'aberto' : 
                                     ($chamado['status'] == 'em andamento' ? 'andamento' : 
                                     ($chamado['status'] == 'resolvido' ? 'resolvido' : 'cancelado')); 
                            ?>">
                                <?php echo ucfirst($chamado['status']); ?>
                            </span>
                            <span class="text-muted">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y', strtotime($chamado['data_abertura'])); ?>
                            </span>
                            <span class="text-muted">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($chamado['categoria']); ?>
                            </span>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Nenhum chamado ainda</h5>
                        <p>Você ainda não abriu nenhum chamado de suporte.</p>
                        <a href="abrir_chamado.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Abrir Primeiro Chamado
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($chamados->num_rows > 0): ?>
                    <div class="text-center mt-3">
                        <a href="meus_chamados.php" class="btn btn-outline-primary">
                            Ver Todos os Chamados <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animação dos cards ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .quick-actions, .chamados-recentes');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>