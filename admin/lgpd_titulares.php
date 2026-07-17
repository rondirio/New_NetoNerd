<?php
/**
 * LGPD — Direitos do Titular (M2/M3 do plano de correção)
 * Exportação (portabilidade) e anonimização (eliminação) de dados de cliente.
 */
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

$mensagem = '';
$tipo_mensagem = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'anonimizado':
            $mensagem = 'Dados do cliente anonimizados com sucesso.';
            $tipo_mensagem = 'success';
            break;
        case 'erro':
            $mensagem = 'Ocorreu um erro ao processar a solicitação.';
            $tipo_mensagem = 'danger';
            break;
        case 'nao_encontrado':
            $mensagem = 'Cliente não encontrado.';
            $tipo_mensagem = 'danger';
            break;
    }
}

$page_title = "LGPD - Direitos do Titular - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <?php if ($mensagem): ?>
            <div class="nn-alert nn-alert-<?= $tipo_mensagem ?> nn-animate-fade">
                <i class="fas fa-<?= $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-user-shield"></i>
                    LGPD — Direitos do Titular
                </h1>
            </div>
            <div class="nn-card-body">
                <p class="mb-2">
                    Use quando um cliente solicitar acesso, portabilidade ou eliminação dos próprios dados
                    (art. 18 da LGPD). Busque o cliente pelo nome para exportar os dados em JSON ou anonimizar
                    o cadastro.
                </p>
                <div class="nn-alert nn-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Anonimizar</strong> não apaga os chamados/ordens de serviço — mantém o histórico
                    para fins estatísticos e fiscais, mas remove nome, e-mail, telefone, endereço e CPF do
                    cliente nesses registros. Ação irreversível.
                </div>

                <div class="nn-form-group" style="max-width: 500px;">
                    <label class="nn-form-label" for="busca_cliente">Buscar Cliente</label>
                    <input type="text" id="busca_cliente" class="nn-form-control" placeholder="Digite ao menos 3 letras do nome...">
                </div>

                <div id="resultado_busca"></div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Confirmar Anonimização -->
<div class="modal fade" id="confirmarAnonimizarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar Anonimização</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="processar_lgpd.php" method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="acao" value="anonimizar">
                <input type="hidden" name="cliente_id" id="modal_cliente_id">
                <div class="modal-body">
                    <p>Tem certeza que deseja anonimizar os dados de <strong id="modal_cliente_nome"></strong>?</p>
                    <p class="text-danger">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="nn-btn nn-btn-danger">
                        <i class="fas fa-user-slash"></i> Confirmar Anonimização
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let debounceTimer;
document.getElementById('busca_cliente').addEventListener('input', function () {
    clearTimeout(debounceTimer);
    const termo = this.value;
    const resultado = document.getElementById('resultado_busca');

    if (termo.length < 3) {
        resultado.innerHTML = '';
        return;
    }

    debounceTimer = setTimeout(() => {
        fetch('buscar_clientes.php?termo=' + encodeURIComponent(termo))
            .then(r => r.json())
            .then(clientes => {
                if (clientes.length === 0) {
                    resultado.innerHTML = '<p class="text-muted mt-3">Nenhum cliente encontrado.</p>';
                    return;
                }
                let html = '<div class="nn-table mt-3"><table><thead><tr><th>Nome</th><th>Email</th><th>Telefone</th><th>Ações</th></tr></thead><tbody>';
                clientes.forEach(c => {
                    const nomeEsc = c.nome.replace(/"/g, '&quot;');
                    html += `<tr>
                        <td data-label="Nome">${c.nome}</td>
                        <td data-label="Email">${c.email}</td>
                        <td data-label="Telefone">${c.telefone ?? '-'}</td>
                        <td data-label="Ações">
                            <a href="exportar_dados_cliente.php?cliente_id=${c.id}" class="nn-btn nn-btn-sm nn-btn-info" title="Exportar dados (JSON)">
                                <i class="fas fa-file-export"></i>
                            </a>
                            <button type="button" class="nn-btn nn-btn-sm nn-btn-danger" title="Anonimizar"
                                onclick="abrirModalAnonimizar(${c.id}, '${nomeEsc}')">
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                resultado.innerHTML = html;
            });
    }, 300);
});

function abrirModalAnonimizar(id, nome) {
    document.getElementById('modal_cliente_id').value = id;
    document.getElementById('modal_cliente_nome').textContent = nome;
    new bootstrap.Modal(document.getElementById('confirmarAnonimizarModal')).show();
}
</script>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
