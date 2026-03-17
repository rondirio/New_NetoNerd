// Funções de Utilidade
const utils = {
    formatarMoeda: (valor) => {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    },
    
    formatarData: (data) => {
        return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
    },
    
    exibirAlerta: (mensagem, tipo = 'success') => {
        const alertaDiv = document.createElement('div');
        alertaDiv.className = `alert alert-${tipo} fade-in`;
        alertaDiv.innerHTML = `
            <span>${mensagem}</span>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:1.2em;margin-left:auto;">×</button>
        `;
        alertaDiv.style.display = 'flex';
        alertaDiv.style.alignItems = 'center';
        
        const container = document.querySelector('.container');
        container.insertBefore(alertaDiv, container.firstChild);
        
        setTimeout(() => {
            alertaDiv.remove();
        }, 5000);
    }
};

// Gerenciador de Modal
class Modal {
    constructor(id) {
        this.modal = document.getElementById(id);
        this.setupCloseEvents();
    }
    
    setupCloseEvents() {
        if (!this.modal) return;
        
        // Fechar ao clicar no X
        const closeBtn = this.modal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.fechar());
        }
        
        // Fechar ao clicar fora
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.fechar();
            }
        });
        
        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.fechar();
            }
        });
    }
    
    abrir() {
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    fechar() {
        this.modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Validação de Formulários
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (this.form) {
            this.setupValidation();
        }
    }
    
    setupValidation() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validar()) {
                e.preventDefault();
                utils.exibirAlerta('Por favor, preencha todos os campos obrigatórios.', 'error');
            }
        });
    }
    
    validar() {
        const campos = this.form.querySelectorAll('[required]');
        let valido = true;
        
        campos.forEach(campo => {
            if (!campo.value.trim()) {
                campo.style.borderColor = 'var(--danger-color)';
                valido = false;
            } else {
                campo.style.borderColor = '#ddd';
            }
        });
        
        return valido;
    }
}

// Gerenciador de Boletos (API)
class GerenciadorBoletos {
    constructor() {
        this.apiUrl = 'api/boletos.php';
        this.containerBoletos = document.getElementById('listaBoletos');
    }
    
    async carregar() {
        try {
            this.exibirLoading();
            
            const response = await fetch(this.apiUrl);
            const data = await response.json();
            
            if (data.success) {
                this.renderizar(data.data);
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            console.error('Erro ao carregar boletos:', error);
            utils.exibirAlerta('Erro ao carregar boletos pendentes.', 'error');
            this.exibirErro();
        }
    }
    
    exibirLoading() {
        if (!this.containerBoletos) return;
        
        this.containerBoletos.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
            </div>
        `;
    }
    
    exibirErro() {
        if (!this.containerBoletos) return;
        
        this.containerBoletos.innerHTML = `
            <div class="alert alert-error">
                Não foi possível carregar os boletos. Tente novamente mais tarde.
            </div>
        `;
    }
    
    renderizar(boletos) {
        if (!this.containerBoletos) return;
        
        if (boletos.length === 0) {
            this.containerBoletos.innerHTML = `
                <div class="alert alert-success">
                    ✓ Nenhum boleto pendente no momento!
                </div>
            `;
            return;
        }
        
        let html = '<div class="table-container"><table><thead><tr>';
        html += '<th>Conta</th>';
        html += '<th>Vencimento</th>';
        html += '<th>Valor</th>';
        html += '<th>Status</th>';
        html += '<th>Ações</th>';
        html += '</tr></thead><tbody>';
        
        boletos.forEach(boleto => {
            const statusClass = boleto.vencido ? 'badge-vencido' : 'badge-pendente';
            const statusTexto = boleto.vencido ? 'Vencido' : 
                               (boleto.dias_para_vencimento === 0 ? 'Vence Hoje' : 
                                `${boleto.dias_para_vencimento} dias`);
            
            html += `<tr>
                <td>
                    <strong>${boleto.nome_conta}</strong>
                    ${boleto.debito_automatico ? '<br><small>🔄 Débito Automático</small>' : ''}
                </td>
                <td>${boleto.data_vencimento_formatada}</td>
                <td><strong>${boleto.valor_formatado}</strong></td>
                <td><span class="badge ${statusClass}">${statusTexto}</span></td>
                <td>
                    <button class="btn btn-success btn-small" onclick="marcarPago(${boleto.id})">
                        ✓ Pagar
                    </button>
                </td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        this.containerBoletos.innerHTML = html;
    }
}

// Confirmação de Ações
function confirmarAcao(mensagem, callback) {
    if (confirm(mensagem)) {
        callback();
    }
}

// Marcar despesa como paga
function marcarPago(id) {
    confirmarAcao('Confirmar pagamento desta despesa?', () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'despesas.php';
        
        const inputAcao = document.createElement('input');
        inputAcao.type = 'hidden';
        inputAcao.name = 'acao';
        inputAcao.value = 'marcar_pago';
        
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;
        
        form.appendChild(inputAcao);
        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    });
}

// Deletar despesa
function deletarDespesa(id) {
    confirmarAcao('Tem certeza que deseja deletar esta despesa?', () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'despesas.php';
        
        const inputAcao = document.createElement('input');
        inputAcao.type = 'hidden';
        inputAcao.name = 'acao';
        inputAcao.value = 'deletar';
        
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;
        
        form.appendChild(inputAcao);
        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    });
}

// Filtrar tabela em tempo real
function filtrarTabela(inputId, tabelaId) {
    const input = document.getElementById(inputId);
    const tabela = document.getElementById(tabelaId);
    
    if (!input || !tabela) return;
    
    input.addEventListener('keyup', function() {
        const filtro = this.value.toLowerCase();
        const linhas = tabela.getElementsByTagName('tr');
        
        for (let i = 1; i < linhas.length; i++) {
            const linha = linhas[i];
            const texto = linha.textContent.toLowerCase();
            
            if (texto.includes(filtro)) {
                linha.style.display = '';
            } else {
                linha.style.display = 'none';
            }
        }
    });
}

// Máscara de Moeda
function mascaraMoeda(input) {
    let valor = input.value;
    valor = valor.replace(/\D/g, '');
    valor = (parseInt(valor) / 100).toFixed(2);
    valor = valor.replace('.', ',');
    valor = valor.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    input.value = valor;
}

// Aplicar máscara em campos de moeda
document.addEventListener('DOMContentLoaded', () => {
    const camposMoeda = document.querySelectorAll('input[data-tipo="moeda"]');
    camposMoeda.forEach(campo => {
        campo.addEventListener('input', function() {
            mascaraMoeda(this);
        });
    });
});

// Auto-atualização de data de vencimento para próximo mês
function proximoVencimento(dia) {
    const hoje = new Date();
    const mes = hoje.getMonth() + 1;
    const ano = hoje.getFullYear();
    
    let proximoMes = mes + 1;
    let proximoAno = ano;
    
    if (proximoMes > 12) {
        proximoMes = 1;
        proximoAno++;
    }
    
    const dataFormatada = `${proximoAno}-${String(proximoMes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    return dataFormatada;
}

// Calcular resumo em tempo real
function atualizarResumo() {
    const linhas = document.querySelectorAll('tbody tr');
    let totalPago = 0;
    let totalPendente = 0;
    let totalVencido = 0;
    
    linhas.forEach(linha => {
        if (linha.style.display === 'none') return;
        
        const valorTexto = linha.cells[2]?.textContent || '0';
        const valor = parseFloat(valorTexto.replace('R$', '').replace(/\./g, '').replace(',', '.'));
        const status = linha.cells[4]?.textContent.trim() || '';
        
        if (status.includes('Pago')) {
            totalPago += valor;
        } else if (status.includes('Vencido')) {
            totalVencido += valor;
        } else {
            totalPendente += valor;
        }
    });
    
    console.log('Resumo:', { totalPago, totalPendente, totalVencido });
}

// Exportar para Excel (simples)
function exportarExcel(tabelaId, nomeArquivo = 'despesas') {
    const tabela = document.getElementById(tabelaId);
    if (!tabela) return;
    
    let html = tabela.outerHTML;
    const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `${nomeArquivo}_${new Date().getTime()}.xls`;
    link.click();
    
    URL.revokeObjectURL(url);
}

// Imprimir relatório
function imprimirRelatorio() {
    window.print();
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    console.log('Sistema de Despesas carregado!');
    
    // Inicializar validação de formulários
    new FormValidator('formDespesa');
    
    // Carregar boletos se houver container
    if (document.getElementById('listaBoletos')) {
        const gerenciador = new GerenciadorBoletos();
        gerenciador.carregar();
        
        // Atualizar a cada 5 minutos
        setInterval(() => gerenciador.carregar(), 300000);
    }
});
