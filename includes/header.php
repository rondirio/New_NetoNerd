<?php
/**
 * Header/Sidebar Global do Sistema NetoNerd ITSM
 * Drawer lateral colapsável, adaptado por tipo de usuário
 * Versão: 2.1
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

// Base absoluta do projeto (funciona em qualquer subpasta, dev e produção)
// mesmo padrão usado em routes/footer.php
$_headerDocRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$_headerProjDir = str_replace('\\', '/', dirname(__DIR__));
$_headerBase    = str_replace($_headerDocRoot, '', $_headerProjDir);

$role_labels = [
    'admin'   => 'Administrador',
    'tecnico' => 'Técnico',
    'cliente' => 'Cliente',
];
$role_label = $role_labels[$tipo_usuario] ?? ucfirst($tipo_usuario);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0B3D91">
    <title><?= $page_title ?? 'NetoNerd ITSM' ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%230B3D91'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='central' text-anchor='middle' font-family='Arial Black,Arial,sans-serif' font-weight='900' font-size='14' fill='white'%3ENN%3C/text%3E%3C/svg%3E">

    <!-- CSS Global -->
    <link rel="stylesheet" href="../assets/css/netonerd-global.css">

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

<!-- Sidebar -->
<div class="nn-sidebar" id="nnSidebar">
    <div class="nn-sidebar-header">
        <div class="nn-sidebar-logo">
            <i class="fas fa-tools"></i>
        </div>
        <span class="nn-sidebar-brand">NetoNerd ITSM</span>
        <button class="nn-sidebar-toggle d-none d-lg-flex" id="nnSidebarToggle" title="Recolher menu">
            <i class="fas fa-chevron-left" id="nnToggleIcon"></i>
        </button>
    </div>

    <div class="nn-sidebar-user">
        <div class="nn-sidebar-user-avatar">
            <?= $iniciais ?: '<i class="fas fa-user"></i>' ?>
        </div>
        <div class="nn-sidebar-user-info">
            <div class="nn-sidebar-user-name"><?= htmlspecialchars($nome_usuario) ?></div>
            <div class="nn-sidebar-user-role"><?= htmlspecialchars($role_label) ?></div>
        </div>
    </div>

    <nav class="nn-sidebar-nav">
        <?php if ($tipo_usuario === 'admin'): ?>
            <div class="nn-sidebar-section-title">Principal</div>
            <div class="nn-sidebar-item" data-label="Dashboard">
                <a href="dashboard.php" class="nn-sidebar-link <?= $pagina_atual === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="Ordem de Serviço">
                <a href="gerar_ordem_servico.php" class="nn-sidebar-link <?= $pagina_atual === 'gerar_ordem_servico.php' ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span>OS</span>
                </a>
            </div>

            <div class="nn-sidebar-section-title">Chamados</div>
            <div class="nn-sidebar-item" data-label="Novo Chamado">
                <a href="abrir_chamado_admin.php" class="nn-sidebar-link <?= $pagina_atual === 'abrir_chamado_admin.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Novo Chamado</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="Atribuir Chamados">
                <a href="atribuir_chamados.php" class="nn-sidebar-link <?= $pagina_atual === 'atribuir_chamados.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-plus"></i>
                    <span>Atribuir Chamados</span>
                    <?php
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
            </div>
            <div class="nn-sidebar-item" data-label="Todos Chamados">
                <a href="chamados_ativos.php" class="nn-sidebar-link <?= $pagina_atual === 'chamados_ativos.php' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    <span>Todos Chamados</span>
                </a>
            </div>

            <div class="nn-sidebar-section-title">Gestão</div>
            <div class="nn-sidebar-item" data-label="Técnicos">
                <a href="apresenta_tecnicos.php" class="nn-sidebar-link <?= $pagina_atual === 'apresenta_tecnicos.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Técnicos</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="Relatórios">
                <a href="relatorios.php" class="nn-sidebar-link <?= $pagina_atual === 'relatorios.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Relatórios</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="Licenças">
                <a href="api_keys.php" class="nn-sidebar-link <?= $pagina_atual === 'api_keys.php' ? 'active' : '' ?>">
                    <i class="fas fa-key"></i>
                    <span>Licenças</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="LGPD">
                <a href="lgpd_titulares.php" class="nn-sidebar-link <?= $pagina_atual === 'lgpd_titulares.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-shield"></i>
                    <span>LGPD</span>
                </a>
            </div>

        <?php elseif ($tipo_usuario === 'tecnico'): ?>
            <div class="nn-sidebar-section-title">Principal</div>
            <div class="nn-sidebar-item" data-label="Dashboard">
                <a href="paineltecnico.php" class="nn-sidebar-link <?= $pagina_atual === 'paineltecnico.php' ? 'active' : '' ?>">
                    <i class="fas fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="Meus Chamados">
                <a href="meus_chamados.php" class="nn-sidebar-link <?= $pagina_atual === 'meus_chamados.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Meus Chamados</span>
                    <?php
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
            </div>

        <?php else: ?>
            <div class="nn-sidebar-section-title">Principal</div>
            <div class="nn-sidebar-item" data-label="Home">
                <a href="home.php" class="nn-sidebar-link <?= $pagina_atual === 'home.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="Meus Chamados">
                <a href="meus_chamados.php" class="nn-sidebar-link <?= $pagina_atual === 'meus_chamados.php' ? 'active' : '' ?>">
                    <i class="fas fa-ticket"></i>
                    <span>Meus Chamados</span>
                </a>
            </div>
            <div class="nn-sidebar-item" data-label="Novo Chamado">
                <a href="abrir_chamado.php" class="nn-sidebar-link <?= $pagina_atual === 'abrir_chamado.php' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Novo Chamado</span>
                </a>
            </div>
        <?php endif; ?>
    </nav>

    <div class="nn-sidebar-footer">
        <a href="../controller/logout.php" class="nn-sidebar-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sair</span>
        </a>
    </div>
</div>

<!-- Overlay mobile -->
<div class="nn-sidebar-overlay" id="nnSidebarOverlay"></div>

<!-- Topbar -->
<header class="nn-topbar">
    <div class="nn-d-flex nn-align-center nn-gap-2">
        <button class="nn-mobile-toggle" id="nnMobileToggle">
            <i class="fas fa-bars"></i>
        </button>
        <button class="nn-desktop-toggle d-none d-lg-flex" id="nnDesktopToggle" title="Recolher menu">
            <i class="fas fa-bars"></i>
        </button>
        <span class="nn-topbar-title"><?= htmlspecialchars($page_title ?? 'NetoNerd ITSM') ?></span>
    </div>
</header>

<?php if ($tipo_usuario === 'admin'): ?>
<!-- Modal de revelar CPF (dado sensível protegido por senha) -->
<div class="nn-modal-overlay" id="cpfRevealOverlay" role="dialog" aria-modal="true" aria-labelledby="cpfModalTitle">
    <div class="nn-modal-box">
        <h3 id="cpfModalTitle">
            <i class="fas fa-shield-alt"></i>
            Confirmação de Identidade
        </h3>
        <p>Para visualizar o CPF completo, confirme sua senha de acesso.</p>
        <form id="cpfRevealForm" data-action="<?= $_headerBase ?>/admin/confirmar_senha_cpf.php">
            <?= csrfField() ?>
            <div class="nn-form-group">
                <label class="nn-form-label" for="cpfRevealPassword">Sua senha</label>
                <input type="password" id="cpfRevealPassword" class="nn-form-control" placeholder="Digite sua senha atual" autocomplete="current-password">
                <div id="cpfRevealError" class="nn-text-danger" role="alert" aria-live="assertive" style="margin-top: 5px; font-size: 0.85rem;"></div>
            </div>
            <div class="nn-d-flex nn-gap-2 nn-mt-2">
                <button type="submit" class="nn-btn nn-btn-primary" style="flex:1;">
                    <i class="fas fa-unlock"></i>
                    Confirmar
                </button>
                <button type="button" id="cpfModalClose" class="nn-btn nn-btn-secondary">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- JavaScript do Sidebar -->
<script>
(function () {
    var sidebar     = document.getElementById('nnSidebar');
    var toggleBtn   = document.getElementById('nnSidebarToggle');
    var desktopBtn  = document.getElementById('nnDesktopToggle');
    var toggleIcon  = document.getElementById('nnToggleIcon');
    var mobileBtn   = document.getElementById('nnMobileToggle');
    var overlay     = document.getElementById('nnSidebarOverlay');
    var COLLAPSED_KEY = 'nn_sidebar_collapsed';

    function applyCollapsed(collapsed) {
        document.body.classList.toggle('nn-sidebar-collapsed', collapsed);
        if (sidebar) sidebar.classList.toggle('collapsed', collapsed);
        if (toggleIcon) {
            toggleIcon.classList.toggle('fa-chevron-left', !collapsed);
            toggleIcon.classList.toggle('fa-chevron-right', collapsed);
        }
        localStorage.setItem(COLLAPSED_KEY, collapsed ? '1' : '0');
    }

    function toggleSidebar() {
        applyCollapsed(!sidebar.classList.contains('collapsed'));
    }

    if (window.innerWidth > 992 && localStorage.getItem(COLLAPSED_KEY) === '1') {
        applyCollapsed(true);
    }

    if (toggleBtn)  toggleBtn.addEventListener('click', toggleSidebar);
    if (desktopBtn) desktopBtn.addEventListener('click', toggleSidebar);

    if (mobileBtn) {
        mobileBtn.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    // Fechar drawer mobile ao navegar
    document.querySelectorAll('.nn-sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            }
        });
    });
})();

<?php if ($tipo_usuario === 'admin'): ?>
// CPF protegido — revelar com confirmação de senha
(function () {
    var overlay  = document.getElementById('cpfRevealOverlay');
    var form     = document.getElementById('cpfRevealForm');
    var closeBtn = document.getElementById('cpfModalClose');
    var targetEl = null;

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.nn-cpf-reveal-btn');
        if (btn) {
            e.preventDefault();
            var targetId = btn.getAttribute('data-target-id');
            targetEl = targetId ? document.getElementById(targetId) : null;
            if (overlay) overlay.classList.add('open');
            setTimeout(function () {
                var pwdInput = document.getElementById('cpfRevealPassword');
                if (pwdInput) pwdInput.focus();
            }, 150);
        }
    });

    function closeModal() {
        if (overlay) overlay.classList.remove('open');
        var pwdInput = document.getElementById('cpfRevealPassword');
        if (pwdInput) pwdInput.value = '';
        var errEl = document.getElementById('cpfRevealError');
        if (errEl) errEl.textContent = '';
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay && overlay.classList.contains('open')) closeModal();
    });

    if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();

        var password = document.getElementById('cpfRevealPassword').value;
        var errEl    = document.getElementById('cpfRevealError');
        var btnEl    = form.querySelector('button[type="submit"]');
        var csrfInput = form.querySelector('input[name="csrf_token"]');

        if (!password) {
            errEl.textContent = 'Digite sua senha.';
            return;
        }

        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

        fetch(form.dataset.action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'password=' + encodeURIComponent(password) + '&csrf_token=' + encodeURIComponent(csrfInput.value)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                if (targetEl) {
                    var cpfSpan = targetEl.querySelector('.nn-cpf-value');
                    var realCpf = targetEl.getAttribute('data-cpf');
                    if (cpfSpan) cpfSpan.textContent = realCpf;
                    var revealBtn = targetEl.querySelector('.nn-cpf-reveal-btn');
                    if (revealBtn) revealBtn.style.display = 'none';
                }
                closeModal();
            } else {
                errEl.textContent = data.message || 'Senha incorreta.';
            }
        })
        .catch(function () {
            errEl.textContent = 'Erro de rede. Tente novamente.';
        })
        .finally(function () {
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="fas fa-unlock"></i> Confirmar';
        });
    });
})();
<?php endif; ?>

// Máscaras de input (CPF, CNPJ, telefone, CEP) — formata enquanto o usuário digita
(function () {
    function aplicarMascara(input, formatador) {
        input.addEventListener('input', function () {
            var posicaoOriginal = this.selectionStart;
            var tamanhoAntes = this.value.length;
            this.value = formatador(this.value);
            var diferenca = this.value.length - tamanhoAntes;
            var novaPosicao = Math.max(0, posicaoOriginal + diferenca);
            this.setSelectionRange(novaPosicao, novaPosicao);
        });
    }

    function mascaraCpf(valor) {
        valor = valor.replace(/\D/g, '').slice(0, 11);
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        return valor;
    }

    function mascaraCnpj(valor) {
        valor = valor.replace(/\D/g, '').slice(0, 14);
        valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
        valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
        return valor;
    }

    function mascaraTelefone(valor) {
        valor = valor.replace(/\D/g, '').slice(0, 11);
        valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{4,5})(\d{4})$/, '$1-$2');
        return valor;
    }

    function mascaraCep(valor) {
        valor = valor.replace(/\D/g, '').slice(0, 8);
        valor = valor.replace(/^(\d{5})(\d)/, '$1-$2');
        return valor;
    }

    function registrarMascaras() {
        document.querySelectorAll('[data-mask="cpf"]').forEach(function (el) { aplicarMascara(el, mascaraCpf); });
        document.querySelectorAll('[data-mask="cnpj"]').forEach(function (el) { aplicarMascara(el, mascaraCnpj); });
        document.querySelectorAll('[data-mask="phone"]').forEach(function (el) { aplicarMascara(el, mascaraTelefone); });
        document.querySelectorAll('[data-mask="cep"]').forEach(function (el) { aplicarMascara(el, mascaraCep); });
    }

    // header.php é incluído no topo da página, antes do <form> da página ser
    // impresso — os inputs com data-mask ainda não existem no DOM neste ponto.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', registrarMascaras);
    } else {
        registrarMascaras();
    }
})();
</script>
