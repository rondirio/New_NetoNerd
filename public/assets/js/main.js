/**
 * NetoNerd - JavaScript Global
 * Funcionalidades compartilhadas em toda aplicação
 */

(function($) {
    'use strict';

    // ========================================================================
    // CONFIGURAÇÕES GLOBAIS
    // ========================================================================
    
    const NetoNerd = {
        config: {
            animationDuration: 300,
            scrollOffset: 80,
            ajaxTimeout: 30000
        },
        
        // ====================================================================
        // INICIALIZAÇÃO
        // ====================================================================
        
        init: function() {
            this.setupEventListeners();
            this.initializeComponents();
            this.setupAjaxDefaults();
            this.handlePageLoading();
        },
        
        // ====================================================================
        // EVENT LISTENERS
        // ====================================================================
        
        setupEventListeners: function() {
            // Previne submit duplo
            $('form').on('submit', this.preventDoubleSubmit);
            
            // Confirmação antes de deletar
            $('.btn-delete, .delete-action').on('click', this.confirmDelete);
            
            // Auto-dismiss de alertas
            setTimeout(() => {
                $('.alert').fadeOut(this.config.animationDuration);
            }, 5000);
            
            // Máscaras de input
            this.setupInputMasks();
            
            // Validação de formulários
            this.setupFormValidation();
        },
        
        // ====================================================================
        // COMPONENTES
        // ====================================================================
        
        initializeComponents: function() {
            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Popovers
            $('[data-toggle="popover"]').popover();
            
            // Select2 (se disponível)
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    language: 'pt-BR'
                });
            }
            
            // DataTables (se disponível)
            if ($.fn.DataTable) {
                $('.datatable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
                    },
                    responsive: true,
                    pageLength: 25
                });
            }
        },
        
        // ====================================================================
        // AJAX
        // ====================================================================
        
        setupAjaxDefaults: function() {
            $.ajaxSetup({
                timeout: this.config.ajaxTimeout,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Loading global para requisições AJAX
            $(document).ajaxStart(function() {
                NetoNerd.showLoading();
            }).ajaxStop(function() {
                NetoNerd.hideLoading();
            });
            
            // Tratamento de erros global
            $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
                if (jqxhr.status === 401) {
                    NetoNerd.showAlert('Sessão expirada. Faça login novamente.', 'warning');
                    setTimeout(() => {
                        window.location.href = 'index.php?login=erro2';
                    }, 2000);
                } else if (jqxhr.status === 500) {
                    NetoNerd.showAlert('Erro no servidor. Tente novamente mais tarde.', 'danger');
                }
            });
        },
        
        // ====================================================================
        // LOADING
        // ====================================================================
        
        showLoading: function(message = 'Carregando...') {
            if ($('#globalLoader').length === 0) {
                $('body').append(`
                    <div id="globalLoader" class="loading-overlay">
                        <div class="text-center">
                            <div class="spinner-border text-light" role="status">
                                <span class="sr-only">Carregando...</span>
                            </div>
                            <p class="text-light mt-3">${message}</p>
                        </div>
                    </div>
                `);
            }
            $('#globalLoader').fadeIn(200);
        },
        
        hideLoading: function() {
            $('#globalLoader').fadeOut(200);
        },
        
        handlePageLoading: function() {
            $(window).on('load', function() {
                $('.page-loader').fadeOut(300);
            });
        },
        
        // ====================================================================
        // ALERTAS
        // ====================================================================
        
        showAlert: function(message, type = 'info', duration = 5000) {
            const alertId = 'alert-' + Date.now();
            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" 
                     style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            $('body').append(alertHtml);
            
            if (duration > 0) {
                setTimeout(() => {
                    $(`#${alertId}`).fadeOut(300, function() {
                        $(this).remove();
                    });
                }, duration);
            }
        },
        
        // ====================================================================
        // CONFIRMAÇÕES
        // ====================================================================
        
        confirmDelete: function(e) {
            const confirmed = confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
            return true;
        },
        
        confirmAction: function(message, callback) {
            if (confirm(message)) {
                if (typeof callback === 'function') {
                    callback();
                }
                return true;
            }
            return false;
        },
        
        // ====================================================================
        // VALIDAÇÃO DE FORMULÁRIOS
        // ====================================================================
        
        setupFormValidation: function() {
            // Validação customizada do Bootstrap
            $('form.needs-validation').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
            
            // Validação de email
            $('input[type="email"]').on('blur', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<div class="invalid-feedback">Email inválido</div>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Validação de senha forte
            $('input[name="senha"][data-strong]').on('keyup', function() {
                const senha = $(this).val();
                const strength = NetoNerd.checkPasswordStrength(senha);
                NetoNerd.showPasswordStrength($(this), strength);
            });
        },
        
        checkPasswordStrength: function(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;
            
            return strength;
        },
        
        showPasswordStrength: function($input, strength) {
            let message = '';
            let className = '';
            
            switch(strength) {
                case 0:
                case 1:
                    message = 'Senha muito fraca';
                    className = 'text-danger';
                    break;
                case 2:
                    message = 'Senha fraca';
                    className = 'text-warning';
                    break;
                case 3:
                    message = 'Senha média';
                    className = 'text-info';
                    break;
                case 4:
                case 5:
                    message = 'Senha forte';
                    className = 'text-success';
                    break;
            }
            
            if (!$input.next('.password-strength').length) {
                $input.after('<small class="password-strength"></small>');
            }
            
            $input.next('.password-strength')
                .attr('class', 'password-strength ' + className)
                .text(message);
        },
        
        // ====================================================================
        // MÁSCARAS DE INPUT
        // ====================================================================
        
        setupInputMasks: function() {
            // CPF
            $('input[data-mask="cpf"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                $(this).val(value);
            });
            
            // CNPJ
            $('input[data-mask="cnpj"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                $(this).val(value);
            });
            
            // Telefone
            $('input[data-mask="phone"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
                } else {
                    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
                }
                $(this).val(value);
            });
            
            // CEP
            $('input[data-mask="cep"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                $(this).val(value);
            });
            
            // Moeda
            $('input[data-mask="currency"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                value = (value / 100).toFixed(2) + '';
                value = value.replace('.', ',');
                value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
                $(this).val('R$ ' + value);
            });
        },
        
        // ====================================================================
        // BUSCA CEP
        // ====================================================================
        
        searchCEP: function(cep, callback) {
            cep = cep.replace(/\D/g, '');
            
            if (cep.length !== 8) {
                NetoNerd.showAlert('CEP inválido', 'warning');
                return;
            }
            
            NetoNerd.showLoading('Buscando CEP...');
            
            $.ajax({
                url: `https://viacep.com.br/ws/${cep}/json/`,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    NetoNerd.hideLoading();
                    
                    if (data.erro) {
                        NetoNerd.showAlert('CEP não encontrado', 'warning');
                        return;
                    }
                    
                    if (typeof callback === 'function') {
                        callback(data);
                    }
                },
                error: function() {
                    NetoNerd.hideLoading();
                    NetoNerd.showAlert('Erro ao buscar CEP', 'danger');
                }
            });
        },
        
        // ====================================================================
        // UPLOAD DE ARQUIVOS
        // ====================================================================
        
        setupFileUpload: function(inputSelector, options = {}) {
            const defaults = {
                maxSize: 5 * 1024 * 1024, // 5MB
                allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
                onSuccess: null,
                onError: null
            };
            
            const settings = $.extend({}, defaults, options);
            
            $(inputSelector).on('change', function() {
                const file = this.files[0];
                
                if (!file) return;
                
                // Valida tamanho
                if (file.size > settings.maxSize) {
                    NetoNerd.showAlert('Arquivo muito grande. Máximo: ' + 
                        (settings.maxSize / 1024 / 1024) + 'MB', 'warning');
                    $(this).val('');
                    return;
                }
                
                // Valida tipo
                if (!settings.allowedTypes.includes(file.type)) {
                    NetoNerd.showAlert('Tipo de arquivo não permitido', 'warning');
                    $(this).val('');
                    return;
                }
                
                // Preview de imagem
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = $(inputSelector).data('preview');
                        if (preview) {
                            $(preview).attr('src', e.target.result).show();
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        },
        
        // ====================================================================
        // UTILITÁRIOS
        // ====================================================================
        
        preventDoubleSubmit: function(e) {
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            
            if ($form.data('submitting') === true) {
                e.preventDefault();
                return false;
            }
            
            $form.data('submitting', true);
            $btn.prop('disabled', true);
            
            const originalText = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm mr-2"></span>Processando...');
            
            // Restaura após 5 segundos (caso não haja redirect)
            setTimeout(() => {
                $form.data('submitting', false);
                $btn.prop('disabled', false).html(originalText);
            }, 5000);
        },
        
        formatCurrency: function(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        },
        
        formatDate: function(date) {
            return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
        },
        
        copyToClipboard: function(text) {
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            NetoNerd.showAlert('Copiado para área de transferência!', 'success', 2000);
        },
        
        // ====================================================================
        // SCROLL SUAVE
        // ====================================================================
        
        smoothScroll: function(target, offset = 80) {
            $('html, body').animate({
                scrollTop: $(target).offset().top - offset
            }, 600);
        },
        
        // ====================================================================
        // DEBOUNCE
        // ====================================================================
        
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };
    
    // ========================================================================
    // INICIALIZAÇÃO AUTOMÁTICA
    // ========================================================================
    
    $(document).ready(function() {
        NetoNerd.init();
    });
    
    // Expõe globalmente
    window.NetoNerd = NetoNerd;
    
})(jQuery);

// ============================================================================
// FUNÇÕES GLOBAIS EXTRAS
// ============================================================================

// Busca CEP automaticamente
$(document).on('blur', 'input[data-cep]', function() {
    const cep = $(this).val();
    const form = $(this).closest('form');
    
    NetoNerd.searchCEP(cep, function(data) {
        form.find('input[name="endereco"]').val(data.logradouro);
        form.find('input[name="bairro"]').val(data.bairro);
        form.find('input[name="cidade"]').val(data.localidade);
        form.find('input[name="estado"]').val(data.uf);
        form.find('input[name="numero"]').focus();
    });
});

// Confirmação de exclusão com data-confirm
$(document).on('click', '[data-confirm]', function(e) {
    const message = $(this).data('confirm') || 'Tem certeza que deseja continuar?';
    if (!confirm(message)) {
        e.preventDefault();
        return false;
    }
});

// Copy to clipboard com data-copy
$(document).on('click', '[data-copy]', function() {
    const text = $(this).data('copy');
    NetoNerd.copyToClipboard(text);
});

// Print com data-print
$(document).on('click', '[data-print]', function() {
    window.print();
});

// Console log bonito para debug
console.log('%cNetoNerd System', 'color: #007bff; font-size: 20px; font-weight: bold;');
console.log('%cVersão 2.0 - Sistema carregado com sucesso!', 'color: #28a745; font-size: 12px;');