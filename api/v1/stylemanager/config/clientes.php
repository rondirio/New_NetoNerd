<?php
/**
 * StyleManager API - Configuração de Clientes
 *
 * Este arquivo mapeia cada API Key para as credenciais do banco de dados
 * do cliente correspondente.
 *
 * ⚠️  IMPORTANTE: Este arquivo contém credenciais sensíveis!
 *     - Não commitc no Git em produção
 *     - Use permissões 600 (chmod 600 clientes.php)
 *     - Em produção, considere usar variáveis de ambiente
 */

return [
    /**
     * Configurações globais
     */
    'config' => [
        // Ambiente: 'development' ou 'production'
        'ambiente' => 'development',

        // Chave secreta para JWT (mude em produção!)
        'jwt_secret' => 'stylemanager_dev_secret_2026_CHANGE_IN_PRODUCTION',

        // Tempo de expiração do token (em segundos)
        'token_expiration' => 86400 * 30, // 30 dias
        'refresh_expiration' => 86400 * 90, // 90 dias
    ],

    /**
     * Mapeamento de API Keys para bancos de dados
     *
     * Formato:
     * 'API_KEY' => [
     *     'db_host' => 'host do banco',
     *     'db_name' => 'nome do banco',
     *     'db_user' => 'usuário do banco',
     *     'db_pass' => 'senha do banco',
     *     'db_port' => 3306,
     *     'estabelecimento' => [
     *         'id' => ID único,
     *         'nome' => 'Nome do Estabelecimento',
     *         'logo' => 'URL do logo (opcional)'
     *     ]
     * ]
     */
    'clientes' => [

        // ============================================================
        // DESENVOLVIMENTO / LOCALHOST
        // ============================================================

        'dev_local_123' => [
            'db_host' => 'localhost',
            'db_name' => 'stylemanager_dev',
            'db_user' => 'root',
            'db_pass' => '',
            'db_port' => 3306,
            'estabelecimento' => [
                'id' => 1,
                'nome' => 'Salão Desenvolvimento',
                'logo' => null
            ]
        ],

        // ============================================================
        // PRODUÇÃO - Adicione seus clientes abaixo
        // ============================================================

        // Exemplo de cliente em produção:
        // 'stylemanager_live_ABC123_salao1' => [
        //     'db_host' => 'seu_host.hostinger.com',
        //     'db_name' => 'u123456789_salao1',
        //     'db_user' => 'u123456789_salao1',
        //     'db_pass' => 'senha_segura_aqui',
        //     'db_port' => 3306,
        //     'estabelecimento' => [
        //         'id' => 2,
        //         'nome' => 'Salão Beleza Total',
        //         'logo' => 'https://salao1.com.br/logo.png'
        //     ]
        // ],

    ]
];
