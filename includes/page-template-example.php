<?php
/**
 * EXEMPLO DE TEMPLATE DE PÁGINA
 * NetoNerd ITSM v2.0
 * Use este arquivo como base para criar/atualizar páginas
 */

// Configurações da página
$page_title = "Título da Página - NetoNerd ITSM";

// Incluir header
require_once '../includes/header.php';
?>

<!-- Conteúdo Principal -->
<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho da Página -->
        <div class="nn-card">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-dashboard"></i>
                    Título da Página
                </h1>
                <div>
                    <button class="nn-btn nn-btn-primary">
                        <i class="fas fa-plus"></i>
                        Ação Principal
                    </button>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="nn-alert nn-alert-success nn-animate-fade">
                <i class="fas fa-check-circle"></i>
                Operação realizada com sucesso!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="nn-alert nn-alert-danger nn-animate-fade">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_GET['erro']) ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Stats -->
        <div class="nn-stats-grid nn-animate-slide">
            <div class="nn-stat-card">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="nn-stat-value">25</div>
                <div class="nn-stat-label">Abertos</div>
            </div>

            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value">150</div>
                <div class="nn-stat-label">Resolvidos</div>
            </div>

            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="nn-stat-value">8</div>
                <div class="nn-stat-label">Pendentes</div>
            </div>

            <div class="nn-stat-card info">
                <div class="nn-stat-icon danger">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="nn-stat-value">12</div>
                <div class="nn-stat-label">Em Andamento</div>
            </div>
        </div>

        <!-- Card de Conteúdo -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-list"></i>
                    Lista de Itens
                </h2>
                <div class="nn-d-flex nn-gap-1">
                    <button class="nn-btn nn-btn-secondary nn-btn-sm">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>
                    <button class="nn-btn nn-btn-primary nn-btn-sm">
                        <i class="fas fa-sync"></i>
                        Atualizar
                    </button>
                </div>
            </div>

            <div class="nn-card-body">
                <!-- Tabela Exemplo -->
                <div class="nn-table">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Status</th>
                                <th>Prioridade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#001</td>
                                <td>Exemplo de Chamado</td>
                                <td>
                                    <span class="nn-badge nn-badge-primary">
                                        <i class="fas fa-folder-open"></i>
                                        Aberto
                                    </span>
                                </td>
                                <td>
                                    <span class="nn-badge nn-badge-high">
                                        Alta
                                    </span>
                                </td>
                                <td>
                                    <button class="nn-btn nn-btn-primary nn-btn-sm">
                                        <i class="fas fa-eye"></i>
                                        Ver
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>#002</td>
                                <td>Outro Chamado</td>
                                <td>
                                    <span class="nn-badge nn-badge-success">
                                        <i class="fas fa-check"></i>
                                        Resolvido
                                    </span>
                                </td>
                                <td>
                                    <span class="nn-badge nn-badge-medium">
                                        Média
                                    </span>
                                </td>
                                <td>
                                    <button class="nn-btn nn-btn-secondary nn-btn-sm">
                                        <i class="fas fa-eye"></i>
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Exemplo de Formulário -->
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-edit"></i>
                    Formulário Exemplo
                </h2>
            </div>

            <div class="nn-card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    Campo de Texto
                                </label>
                                <input type="text" class="nn-form-control" placeholder="Digite algo...">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    Select
                                </label>
                                <select class="nn-form-control">
                                    <option>Opção 1</option>
                                    <option>Opção 2</option>
                                    <option>Opção 3</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    Textarea
                                </label>
                                <textarea class="nn-form-control" rows="4" placeholder="Escreva algo..."></textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="nn-btn nn-btn-primary nn-btn-lg">
                                <i class="fas fa-save"></i>
                                Salvar
                            </button>
                            <button type="button" class="nn-btn nn-btn-secondary nn-btn-lg">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </button>
                        </div>
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
