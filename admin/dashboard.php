<?php
/**
 * Dashboard Administração - NetoNerd ITSM v2.0
 * Página principal do administrador com estatísticas e gestão de técnicos
 */

session_start();

// Verificar se é admin
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

include_once '../config/bandoDeDados/conexao.php';

// Configuração da página
$page_title = "Dashboard Administração - NetoNerd ITSM";

// Incluir header
require_once '../includes/header.php';

// ========== QUERIES PARA DASHBOARD STATS ==========

// Total de Chamados Abertos
$sql = "SELECT COUNT(*) AS total_chamados FROM chamados WHERE status = 'Aberto'";
$result = $conn->query($sql);
$totalChamados = ($result && $row = $result->fetch_assoc()) ? $row['total_chamados'] : 0;

// Atendimentos Hoje
$today = date('Y-m-d');
$sql = "SELECT COUNT(*) AS atendimentos_hoje FROM chamados WHERE DATE(data_fechamento) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $today);
$stmt->execute();
$result = $stmt->get_result();
$atendimentosHoje = ($result && $row = $result->fetch_assoc()) ? $row['atendimentos_hoje'] : 0;
$stmt->close();

// Formas de Pagamento
$contagens = [
    'PIX' => 0,
    'Dinheiro' => 0,
    'Cartão Crédito' => 0,
    'Cartão Débito' => 0,
    'Boleto' => 0
];

$sql = "SELECT TRIM(LOWER(pagamento_forma)) AS pagamento_forma, COUNT(*) AS total
        FROM chamados
        GROUP BY TRIM(LOWER(pagamento_forma))";
$result = $conn->query($sql);

$mapa_pagamento = [
    'pix' => 'PIX',
    'dinheiro' => 'Dinheiro',
    'cartão crédito' => 'Cartão Crédito',
    'cartão de crédito' => 'Cartão Crédito',
    'cartão débito' => 'Cartão Débito',
    'cartão de débito' => 'Cartão Débito',
    'boleto' => 'Boleto'
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $forma_banco = $row['pagamento_forma'];
        $forma_normalizada = $mapa_pagamento[$forma_banco] ?? null;
        if ($forma_normalizada && isset($contagens[$forma_normalizada])) {
            $contagens[$forma_normalizada] = $row['total'];
        }
    }
}

$pix = $contagens['PIX'];
$dinheiro = $contagens['Dinheiro'];
$cartaoCredito = $contagens['Cartão Crédito'];
$cartaoDebito = $contagens['Cartão Débito'];
$boleto = $contagens['Boleto'];

// Total de Técnicos Ativos
$sql = "SELECT COUNT(*) AS total FROM tecnicos WHERE status_tecnico = 'Active'";
$result = $conn->query($sql);
$tecnicosAtivos = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

// Próxima matrícula disponível
$sql = "SELECT matricula FROM tecnicos WHERE matricula LIKE '2025F1%' ORDER BY matricula DESC LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastMatricula = $row['matricula'];
    $newMatricula = '2025F1' . str_pad((int)substr($lastMatricula, 6) + 1, 3, '0', STR_PAD_LEFT);
} else {
    $newMatricula = '2025F1000';
}
?>
<!-- Coloque isso logo após o header do dashboard -->

<?php if (isset($_GET['sucesso'])): ?>
    <?php if ($_GET['sucesso'] === 'tecnico_cadastrado'): ?>
        <div class="nn-alert nn-alert-success nn-animate-fade">
            <i class="fas fa-check-circle"></i>
            Técnico cadastrado com sucesso!
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_GET['erro'])): ?>
    <?php 
    $erro_msg = '';
    switch($_GET['erro']) {
        case 'campos_obrigatorios':
            $erro_msg = 'Por favor, preencha todos os campos obrigatórios.';
            break;
        case 'email_invalido':
            $erro_msg = 'O email informado não é válido.';
            break;
        case 'email_existente':
            $erro_msg = 'Este email já está cadastrado no sistema.';
            break;
        case 'matricula_existente':
            $erro_msg = 'Esta matrícula já está cadastrada no sistema.';
            break;
        case 'erro_banco':
            $erro_msg = 'Erro ao salvar no banco de dados: ' . ($_GET['msg'] ?? 'Erro desconhecido');
            break;
        default:
            $erro_msg = 'Ocorreu um erro ao processar a solicitação.';
    }
    ?>
    <div class="nn-alert nn-alert-danger nn-animate-fade">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($erro_msg) ?>
    </div>
<?php endif; ?>
<!-- Conteúdo Principal -->
<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho da Página -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-chart-line"></i>
                    Dashboard Administração
                </h1>
                <div>
                    <button class="nn-btn nn-btn-primary" data-bs-toggle="modal" data-bs-target="#addTecnicoModal">
                        <i class="fas fa-user-plus"></i>
                        Adicionar Técnico
                    </button>
                </div>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="nn-stats-grid nn-animate-slide">
            <!-- Chamados Abertos -->
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="nn-stat-value"><?php echo htmlspecialchars($totalChamados); ?></div>
                <div class="nn-stat-label">Chamados Abertos</div>
            </div>

            <!-- Atendimentos Hoje -->
            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="nn-stat-value"><?php echo htmlspecialchars($atendimentosHoje); ?></div>
                <div class="nn-stat-label">Atendimentos Hoje</div>
            </div>

            <!-- Técnicos Ativos -->
            <div class="nn-stat-card info">
                <div class="nn-stat-icon info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="nn-stat-value"><?php echo htmlspecialchars($tecnicosAtivos); ?></div>
                <div class="nn-stat-label">Técnicos Ativos</div>
            </div>

            <!-- PIX -->
            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="nn-stat-value"><?php echo htmlspecialchars($pix); ?></div>
                <div class="nn-stat-label">Pagamentos PIX</div>
            </div>
        </div>

        <!-- Stats de Pagamento Secundários -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="nn-card">
                    <div class="nn-card-body text-center">
                        <i class="fas fa-money-bill-wave text-success mb-2" style="font-size: 2rem;"></i>
                        <div class="nn-stat-value"><?php echo htmlspecialchars($dinheiro); ?></div>
                        <div class="nn-stat-label">Dinheiro</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="nn-card">
                    <div class="nn-card-body text-center">
                        <i class="fas fa-credit-card text-primary mb-2" style="font-size: 2rem;"></i>
                        <div class="nn-stat-value"><?php echo htmlspecialchars($cartaoCredito); ?></div>
                        <div class="nn-stat-label">Cartão de Crédito</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="nn-card">
                    <div class="nn-card-body text-center">
                        <i class="fas fa-credit-card text-info mb-2" style="font-size: 2rem;"></i>
                        <div class="nn-stat-value"><?php echo htmlspecialchars($cartaoDebito); ?></div>
                        <div class="nn-stat-label">Cartão de Débito</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="nn-card">
                    <div class="nn-card-body text-center">
                        <i class="fas fa-barcode text-warning mb-2" style="font-size: 2rem;"></i>
                        <div class="nn-stat-value"><?php echo htmlspecialchars($boleto); ?></div>
                        <div class="nn-stat-label">Boleto</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Técnicos -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-users-cog"></i>
                    Lista de Técnicos
                </h2>
                <div class="nn-d-flex nn-gap-1">
                    <button class="nn-btn nn-btn-secondary nn-btn-sm" onclick="location.href='apresenta_tecnicos.php'">
                        <i class="fas fa-list"></i>
                        Ver Todos
                    </button>
                    <button class="nn-btn nn-btn-primary nn-btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync"></i>
                        Atualizar
                    </button>
                </div>
            </div>

            <div class="nn-card-body">
                <div class="nn-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                                <th>Matrícula</th>
                                <th>Veículo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, nome, carro_do_dia, email, created_at, matricula, status_tecnico FROM tecnicos ORDER BY created_at DESC";
                            $result = $conn->query($sql);

                            if ($result === false) {
                                echo "<tr><td colspan='8' class='text-center'>Erro na consulta: " . htmlspecialchars($conn->error) . "</td></tr>";
                            } elseif ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $id = (int)$row['id'];
                                    $status = $row['status_tecnico'];
                                    $badgeClass = ($status === 'Active') ? 'nn-badge-success' : 'nn-badge-secondary';

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($id) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td><span class='nn-badge $badgeClass'>" . htmlspecialchars($status) . "</span></td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>";
                                    echo "<td><span class='nn-badge nn-badge-info'>" . htmlspecialchars($row['matricula']) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['carro_do_dia']) . "</td>";
                                    echo "<td>";
                                    echo "<a href='editar_tecnico.php?id=$id' class='nn-btn nn-btn-primary nn-btn-sm'>";
                                    echo "<i class='fas fa-edit'></i> Editar</a> ";
                                    echo "<a href='relatorio_tecnico.php?id=$id' class='nn-btn nn-btn-success nn-btn-sm'>";
                                    echo "<i class='fas fa-file-alt'></i> Relatório</a> ";
                                    echo "<a href='excluir_tecnico.php?id=$id' class='nn-btn nn-btn-danger nn-btn-sm' ";
                                    echo "onclick=\"return confirm('Deseja realmente excluir este técnico?');\">";
                                    echo "<i class='fas fa-trash'></i> Excluir</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>Nenhum técnico encontrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal para Adicionar Técnico -->
<div class="modal fade" id="addTecnicoModal" tabindex="-1" aria-labelledby="addTecnicoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title" id="addTecnicoModalLabel">
                    <i class="fas fa-user-plus"></i>
                    Adicionar Novo Técnico
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="processa_adicionar_tecnico.php" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-user"></i>
                                    Nome Completo
                                </label>
                                <input type="text" class="nn-form-control" name="nome" required placeholder="Digite o nome completo">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </label>
                                <input type="email" class="nn-form-control" name="email" required placeholder="email@exemplo.com">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-id-card"></i>
                                    Matrícula
                                </label>
                                <input type="text" class="nn-form-control" name="registration" required
                                       placeholder="<?php echo htmlspecialchars($newMatricula); ?>"
                                       value="<?php echo htmlspecialchars($newMatricula); ?>">
                                <small class="text-muted">Próxima matrícula disponível</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-car"></i>
                                    Veículo
                                </label>
                                <input type="text" class="nn-form-control" name="vehicle_of_the_day" required
                                       placeholder="Ex: Fiat Uno - ABC-1234">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-toggle-on"></i>
                                    Status
                                </label>
                                <select class="nn-form-control" name="status_technician" required>
                                    <option value="Active">Ativo</option>
                                    <option value="Inactive">Inativo</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-lock"></i>
                                    Senha
                                </label>
                                <input type="password" class="nn-form-control" name="password" required
                                       placeholder="Digite a senha" minlength="6">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="nn-btn nn-btn-primary">
                            <i class="fas fa-save"></i>
                            Salvar Técnico
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
require_once '../includes/footer.php';
?>
