<?php
/**
 * Header Global do Sistema NetoNerd ITSM
 * Navegação responsiva adaptada por tipo de usuário
 * Versão: 2.0
 */

// Verificar se sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obter informações do usuário
$tipo_usuario = $_SESSION['tipo'] ?? $_SESSION['tipo_usuario'] ?? 'visitante';
$nome_usuario = $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? 'Usuário';
$email_usuario = $_SESSION['email'] ?? $_SESSION['usuario_email'] ?? '';

// Iniciais do usuário para avatar
$iniciais = '';
if ($nome_usuario && $nome_usuario !== 'Usuário') {
    $partes_nome = explode(' ', $nome_usuario);
    $iniciais = strtoupper(substr($partes_nome[0], 0, 1));
    if (count($partes_nome) > 1) {
        $iniciais .= strtoupper(substr($partes_nome[count($partes_nome) - 1], 0, 1));
    }
}

// Determinar página ativa
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#007bff">
    <title><?= $page_title ?? 'NetoNerd ITSM' ?></title>

    <!-- CSS Global -->
    <link rel="stylesheet" href="/assets/css/netonerd-global.css">

    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Adicional da Página -->
    <?php if (isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>
</head>
<body>

<!-- Header -->
<header class="nn-header">
    <div class="nn-header-container">
        <!-- Logo e Nome -->
        <a href="<?= $tipo_usuario === 'admin' ? '/admin/dashboard.php' : ($tipo_usuario === 'tecnico' ? '/tecnico/paineltecnico.php' : '/cliente/home.php') ?>" class="nn-header-brand">
            <div class="nn-header-logo">
                <i class="fas fa-tools"></i>
            </div>
            <span>NetoNerd ITSM</span>
        </a>

        <!-- Toggle Menu Mobile -->
        <button class="nn-mobile-toggle" id="mobileToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navegação -->
        <nav class="nn-header-nav" id="headerNav">
            <?php if ($tipo_usuario === 'admin'): ?>
                <!-- Menu Admin -->
                <a href="/admin/dashboard.php" class="<?= $pagina_atual === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-dashboard"></i>
                    <span class="nn-hidden-mobile">Dashboard</span>
                </a>
                <a href="/admin/atribuir_chamados.php" class="<?= $pagina_atual === 'atribuir_chamados.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-plus"></i>
                    <span>Atribuir Chamados</span>
                    <?php
                    // Contar chamados não atribuídos (se conexão disponível)
                    if (function_exists('getConnection')) {
                        $conn = getConnection();
                        $result = $conn->query("SELECT COUNT(*) as total FROM chamados WHERE tecnico_id IS NULL AND status != 'cancelado'");
                        if ($result) {
                            $count = $result->fetch_assoc()['total'];
                            if ($count > 0) {
                                echo '<span class="nn-sidebar-badge">' . $count . '</span>';
                            }
                        }
                    }
                    ?>
                </a>
                <a href="/admin/chamados_ativos.php" class="<?= $pagina_atual === 'chamados_ativos.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>Todos Chamados</span>
                </a>
                <a href="/admin/apresenta_tecnicos.php" class="<?= $pagina_atual === 'apresenta_tecnicos.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Técnicos</span>
                </a>
                <a href="/admin/relatorios.php" class="<?= $pagina_atual === 'relatorios.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Relatórios</span>
                </a>
                <a href="/admin/licencas.php" class="<?= $pagina_atual === 'licencas.php' ? 'active' : '' ?>">
                    <i class="fas fa-key"></i>
                    <span class="nn-hidden-mobile">Licenças</span>
                </a>

            <?php elseif ($tipo_usuario === 'tecnico'): ?>
                <!-- Menu Técnico -->
                <a href="/tecnico/paineltecnico.php" class="<?= $pagina_atual === 'paineltecnico.php' ? 'active' : '' ?>">
                    <i class="fas fa-dashboard"></i>
                    <span class="nn-hidden-mobile">Dashboard</span>
                </a>
                <a href="/tecnico/meus_chamados.php" class="<?= $pagina_atual === 'meus_chamados.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Meus Chamados</span>
                    <?php
                    // Contar chamados ativos do técnico
                    if (function_exists('getConnection') && isset($_SESSION['usuario_id'])) {
                        $conn = getConnection();
                        $tecnico_id = $_SESSION['usuario_id'];
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE tecnico_id = ? AND status IN ('aberto', 'em andamento')");
                        $stmt->bind_param("i", $tecnico_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result) {
                            $count = $result->fetch_assoc()['total'];
                            if ($count > 0) {
                                echo '<span class="nn-sidebar-badge">' . $count . '</span>';
                            }
                        }
                        $stmt->close();
                    }
                    ?>
                </a>

            <?php else: ?>
                <!-- Menu Cliente -->
                <a href="/cliente/home.php" class="<?= $pagina_atual === 'home.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span class="nn-hidden-mobile">Home</span>
                </a>
                <a href="/cliente/meus_chamados.php" class="<?= $pagina_atual === 'meus_chamados.php' ? 'active' : '' ?>">
                    <i class="fas fa-ticket"></i>
                    <span>Meus Chamados</span>
                </a>
                <a href="/cliente/abrir_chamado.php" class="<?= $pagina_atual === 'abrir_chamado.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Novo Chamado</span>
                </a>
            <?php endif; ?>

            <!-- Informações do Usuário (mobile) -->
            <div class="nn-header-user d-md-none">
                <div class="nn-header-user-avatar">
                    <?= $iniciais ?: '<i class="fas fa-user"></i>' ?>
                </div>
                <div>
                    <div><strong><?= htmlspecialchars($nome_usuario) ?></strong></div>
                    <small><?= ucfirst($tipo_usuario) ?></small>
                </div>
            </div>

            <!-- Botão Sair (mobile) -->
            <a href="/controller/logout.php" class="nn-header-logout d-md-none">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </nav>

        <!-- Informações do Usuário (desktop) -->
        <div class="nn-header-user d-none d-md-flex">
            <div class="nn-header-user-avatar">
                <?= $iniciais ?: '<i class="fas fa-user"></i>' ?>
            </div>
            <div>
                <div><strong><?= htmlspecialchars($nome_usuario) ?></strong></div>
                <small><?= ucfirst($tipo_usuario) ?></small>
            </div>
        </div>

        <!-- Botão Sair (desktop) -->
        <a href="/controller/logout.php" class="nn-header-logout d-none d-md-flex">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sair</span>
        </a>
    </div>
</header>

<!-- JavaScript do Header -->
<script>
// Toggle menu mobile
document.getElementById('mobileToggle')?.addEventListener('click', function() {
    const nav = document.getElementById('headerNav');
    nav.classList.toggle('active');

    // Mudar ícone
    const icon = this.querySelector('i');
    if (nav.classList.contains('active')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

// Fechar menu ao clicar em link (mobile)
document.querySelectorAll('.nn-header-nav a').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 992) {
            const nav = document.getElementById('headerNav');
            const toggle = document.getElementById('mobileToggle');
            nav.classList.remove('active');

            const icon = toggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });
});

// Fechar menu ao clicar fora (mobile)
document.addEventListener('click', function(event) {
    const nav = document.getElementById('headerNav');
    const toggle = document.getElementById('mobileToggle');

    if (window.innerWidth <= 992 && nav.classList.contains('active')) {
        if (!nav.contains(event.target) && !toggle.contains(event.target)) {
            nav.classList.remove('active');

            const icon = toggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }
});
</script>
