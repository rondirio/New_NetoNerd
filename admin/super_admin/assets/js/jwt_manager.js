/**
 * JWT Manager - Frontend Integration
 * Gerenciador de tokens JWT para Super Admin NetoNerd
 * 
 * @author NetoNerd Development Team
 * @version 1.0.0
 */

class JWTManager {
    constructor(apiBaseUrl) {
        this.apiBaseUrl = apiBaseUrl || '/api/jwt';
        this.tokensCache = [];
        this.statsCache = null;
    }

    /**
     * Gera um novo token JWT
     * @param {Object} tenantData - Dados do tenant
     * @returns {Promise<Object>}
     */
    async generateToken(tenantData) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(tenantData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Erro ao gerar token');
            }

            // Atualizar cache
            this.tokensCache.unshift(data.data);
            
            return data;
        } catch (error) {
            console.error('Erro ao gerar token:', error);
            throw error;
        }
    }

    /**
     * Valida um token JWT
     * @param {string} token - Token JWT
     * @returns {Promise<Object>}
     */
    async validateToken(token) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token })
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Erro ao validar token:', error);
            throw error;
        }
    }

    /**
     * Revoga um token
     * @param {string} token - Token JWT
     * @param {string} motivo - Motivo da revogação
     * @returns {Promise<Object>}
     */
    async revokeToken(token, motivo = 'Revogado manualmente') {
        try {
            const response = await fetch(`${this.apiBaseUrl}/revoke`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token, motivo })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Erro ao revogar token');
            }

            return data;
        } catch (error) {
            console.error('Erro ao revogar token:', error);
            throw error;
        }
    }

    /**
     * Lista tokens de um tenant
     * @param {string} tenantId - ID do tenant
     * @returns {Promise<Array>}
     */
    async listTokens(tenantId) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/list?tenant_id=${tenantId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Erro ao listar tokens');
            }

            return data.tokens;
        } catch (error) {
            console.error('Erro ao listar tokens:', error);
            throw error;
        }
    }

    /**
     * Obtém estatísticas dos tokens
     * @returns {Promise<Object>}
     */
    async getStatistics() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/stats`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Erro ao obter estatísticas');
            }

            this.statsCache = data.statistics;
            return data.statistics;
        } catch (error) {
            console.error('Erro ao obter estatísticas:', error);
            throw error;
        }
    }

    /**
     * Decodifica um token JWT (sem validar assinatura)
     * @param {string} token - Token JWT
     * @returns {Object|null}
     */
    decodeToken(token) {
        try {
            const parts = token.split('.');
            if (parts.length !== 3) {
                return null;
            }

            const payload = parts[1];
            const decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
            return JSON.parse(decoded);
        } catch (error) {
            console.error('Erro ao decodificar token:', error);
            return null;
        }
    }

    /**
     * Verifica se um token está expirado (localmente, sem validar assinatura)
     * @param {string} token - Token JWT
     * @returns {boolean}
     */
    isTokenExpired(token) {
        const payload = this.decodeToken(token);
        if (!payload || !payload.exp) {
            return true;
        }

        const now = Math.floor(Date.now() / 1000);
        return payload.exp < now;
    }

    /**
     * Formata data timestamp Unix para string legível
     * @param {number} timestamp - Timestamp Unix
     * @returns {string}
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp * 1000);
        return date.toLocaleString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Calcula dias restantes até expiração
     * @param {number} expirationTimestamp - Timestamp de expiração
     * @returns {number}
     */
    daysUntilExpiration(expirationTimestamp) {
        const now = Date.now() / 1000;
        const diff = expirationTimestamp - now;
        return Math.ceil(diff / (60 * 60 * 24));
    }
}

// ========================================
// UI Manager - Gerencia a interface
// ========================================

class JWTUIManager {
    constructor(jwtManager) {
        this.jwtManager = jwtManager;
        this.currentTenantId = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadStatistics();
    }

    setupEventListeners() {
        // Formulário de geração
        const form = document.getElementById('tokenForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleGenerateToken(e));
        }
    }

    async handleGenerateToken(event) {
        event.preventDefault();

        const formData = {
            nome_empresa: document.getElementById('nomeEmpresa').value,
            tipo_projeto: document.getElementById('tipoProjeto').value,
            email_owner: document.getElementById('emailOwner').value,
            telefone: document.getElementById('telefone').value || null,
            plano: document.getElementById('plano').value
        };

        try {
            // Mostrar loading
            this.showLoading('Gerando token...');

            // Gerar token via API
            const result = await this.jwtManager.generateToken(formData);

            // Exibir token gerado
            this.displayGeneratedToken(result.data);

            // Atualizar estatísticas
            await this.loadStatistics();

            // Mostrar mensagem de sucesso
            this.showAlert('Token JWT gerado com sucesso!', 'success');

            // Limpar formulário
            event.target.reset();

        } catch (error) {
            this.showAlert(`Erro: ${error.message}`, 'error');
        } finally {
            this.hideLoading();
        }
    }

    displayGeneratedToken(tokenData) {
        document.getElementById('jwtToken').textContent = tokenData.token;
        document.getElementById('tenantId').textContent = tokenData.tenant_id;
        document.getElementById('projectType').textContent = this.getProjectName(tokenData.projeto);
        document.getElementById('issuedAt').textContent = tokenData.issued_at;
        document.getElementById('expiresAt').textContent = tokenData.expires_at;
        
        // Mostrar display
        document.getElementById('tokenDisplay').classList.add('active');

        // Adicionar à lista
        this.addTokenToList(tokenData);
    }

    addTokenToList(tokenData) {
        const container = document.getElementById('tokensList');
        
        // Remover mensagem vazia se existir
        if (container.querySelector('p')) {
            container.innerHTML = '';
        }

        const tokenItem = document.createElement('div');
        tokenItem.className = 'token-item active';
        tokenItem.innerHTML = `
            <div class="token-header">
                <strong>${tokenData.empresa}</strong>
                <span class="badge active">Ativo</span>
            </div>
            <div style="font-size: 12px; color: #666;">
                <div><strong>Tenant:</strong> ${tokenData.tenant_id}</div>
                <div><strong>Projeto:</strong> ${this.getProjectName(tokenData.projeto)}</div>
                <div><strong>Plano:</strong> ${tokenData.plano}</div>
                <div><strong>Expira:</strong> ${tokenData.expires_at}</div>
            </div>
        `;

        container.insertBefore(tokenItem, container.firstChild);
    }

    async loadStatistics() {
        try {
            const stats = await this.jwtManager.getStatistics();
            
            document.getElementById('totalTokens').textContent = stats.total_tokens || 0;
            document.getElementById('activeTokens').textContent = stats.tokens_ativos || 0;
            document.getElementById('revokedTokens').textContent = stats.tokens_revogados || 0;
            document.getElementById('expiredTokens').textContent = stats.tokens_expirados || 0;
        } catch (error) {
            console.error('Erro ao carregar estatísticas:', error);
        }
    }

    showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        alertContainer.innerHTML = `
            <div class="alert ${type}">
                ${message}
            </div>
        `;
        
        setTimeout(() => {
            alertContainer.innerHTML = '';
        }, 5000);
    }

    showLoading(message) {
        // Implementar overlay de loading se necessário
        console.log('Loading:', message);
    }

    hideLoading() {
        // Remover overlay de loading
        console.log('Loading finished');
    }

    getProjectName(tipo) {
        const names = {
            'myhealth': '🏥 MyHealth',
            'barbershop': '💈 BarberShop',
            'suporte_ti': '🖥️ Suporte TI'
        };
        return names[tipo] || tipo;
    }
}

// ========================================
// Funções Globais (para uso direto no HTML)
// ========================================

function copyToken() {
    const tokenText = document.getElementById('jwtToken').textContent;
    
    navigator.clipboard.writeText(tokenText).then(() => {
        // Criar feedback visual temporário
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = '✅ Copiado!';
        btn.style.background = '#28a745';
        
        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = '';
        }, 2000);
    }).catch(err => {
        alert('Erro ao copiar token. Tente selecionar e copiar manualmente.');
    });
}

// ========================================
// Inicialização quando DOM carregar
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Configurar URL base da API
    const API_BASE_URL = 'http://localhost/Super_admin_NetoNerd/api/jwt';

    // if it is in production ambient, include this later code:
    // const API_BASE_URL = 'https://admin.netonerd.com/api/jwt';
    
    // Inicializar managers
    const jwtManager = new JWTManager(API_BASE_URL);
    const uiManager = new JWTUIManager(jwtManager);
    
    // Expor para uso global (útil para debug e funções inline)
    window.jwtManager = jwtManager;
    window.uiManager = uiManager;
    
    console.log('JWT Manager inicializado com sucesso!');
});

// ========================================
// Utilitários Adicionais
// ========================================

/**
 * Validador de formulário personalizado
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function formatPhoneNumber(phone) {
    // Remove caracteres não numéricos
    const cleaned = phone.replace(/\D/g, '');
    
    // Formata (XX) XXXXX-XXXX
    if (cleaned.length === 11) {
        return `(${cleaned.substring(0, 2)}) ${cleaned.substring(2, 7)}-${cleaned.substring(7)}`;
    }
    
    return phone;
}

/**
 * Export para CSV (útil para exportar lista de tokens)
 */
function exportTokensToCSV(tokens) {
    const headers = ['Tenant ID', 'Empresa', 'Projeto', 'Plano', 'Status', 'Emitido em', 'Expira em'];
    const rows = tokens.map(token => [
        token.tenant_id,
        token.empresa,
        token.projeto,
        token.plano,
        token.status,
        token.issued_at,
        token.expires_at
    ]);
    
    let csvContent = headers.join(',') + '\n';
    rows.forEach(row => {
        csvContent += row.map(cell => `"${cell}"`).join(',') + '\n';
    });
    
    // Download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `tokens_jwt_${Date.now()}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Busca e filtragem de tokens
 */
function filterTokens(searchTerm, tokens) {
    const term = searchTerm.toLowerCase();
    return tokens.filter(token => 
        token.empresa.toLowerCase().includes(term) ||
        token.tenant_id.toLowerCase().includes(term) ||
        token.projeto.toLowerCase().includes(term)
    );
}