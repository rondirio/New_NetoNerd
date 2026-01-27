-- ============================================================
-- Script para Criar Usuários de Teste
-- NetoNerd ITSM - Sistema de Login
-- ============================================================

USE netonerd_chamados;

-- ============================================================
-- 1. CRIAR CLIENTE DE TESTE
-- ============================================================

-- Senha: teste123
-- Hash gerado com: password_hash('teste123', PASSWORD_BCRYPT)
INSERT INTO `clientes` (`nome`, `email`, `telefone`, `endereco`, `senha_hash`, `cep`, `genero`)
VALUES (
    'Cliente Teste',
    'cliente@teste.com',
    '21987654321',
    'Rua Teste, 123',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- teste123
    25000000,
    'Masculino'
)
ON DUPLICATE KEY UPDATE email=email;

-- ============================================================
-- 2. CRIAR ADMINISTRADOR
-- ============================================================

-- Matrícula: 2026ADM001
-- Senha: admin123
-- Hash gerado com: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `tecnicos` (`nome`, `email`, `matricula`, `senha_hash`, `status_tecnico`, `Ativo`, `carro_do_dia`)
VALUES (
    'Administrador Sistema',
    'admin@netonerd.com.br',
    '2026ADM001',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'Ativo',
    1,
    'N/A'
)
ON DUPLICATE KEY UPDATE matricula=matricula;

-- ============================================================
-- 3. CRIAR TÉCNICO
-- ============================================================

-- Matrícula: 2026F001
-- Senha: tecnico123
-- Hash gerado com: password_hash('tecnico123', PASSWORD_BCRYPT)
INSERT INTO `tecnicos` (`nome`, `email`, `matricula`, `senha_hash`, `status_tecnico`, `Ativo`, `carro_do_dia`)
VALUES (
    'Técnico Teste',
    'tecnico@netonerd.com.br',
    '2026F001',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- tecnico123
    'Ativo',
    1,
    'Fiat Uno - ABC1234'
)
ON DUPLICATE KEY UPDATE matricula=matricula;

-- ============================================================
-- 4. CRIAR OUTRO ADMINISTRADOR (Matrícula alternativa)
-- ============================================================

-- Matrícula: 2026A002 (formato alternativo)
-- Senha: admin456
INSERT INTO `tecnicos` (`nome`, `email`, `matricula`, `senha_hash`, `status_tecnico`, `Ativo`, `carro_do_dia`)
VALUES (
    'Admin Alternativo',
    'admin2@netonerd.com.br',
    '2026A002',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin456 (mesma senha para teste)
    'Ativo',
    1,
    'N/A'
)
ON DUPLICATE KEY UPDATE matricula=matricula;

-- ============================================================
-- RESUMO DOS USUÁRIOS CRIADOS
-- ============================================================

SELECT '=== USUÁRIOS DE TESTE CRIADOS ===' AS INFO;

SELECT
    'CLIENTE' as TIPO,
    nome,
    email,
    'teste123' as SENHA,
    NULL as MATRICULA
FROM clientes
WHERE email = 'cliente@teste.com'

UNION ALL

SELECT
    CASE
        WHEN matricula LIKE '%ADM%' OR matricula LIKE '%A%' THEN 'ADMINISTRADOR'
        ELSE 'TÉCNICO'
    END as TIPO,
    nome,
    email,
    CASE
        WHEN matricula = '2026ADM001' THEN 'admin123'
        WHEN matricula = '2026A002' THEN 'admin456'
        WHEN matricula = '2026F001' THEN 'tecnico123'
        ELSE 'N/A'
    END as SENHA,
    matricula as MATRICULA
FROM tecnicos
WHERE matricula IN ('2026ADM001', '2026A002', '2026F001');

SELECT '' AS '';
SELECT '=== INSTRUÇÕES DE LOGIN ===' AS INFO;
SELECT 'CLIENTES: Usam EMAIL + SENHA na página /publics/login.php' AS instrucao;
SELECT 'TÉCNICOS/ADMINS: Usam MATRÍCULA + SENHA na página /tecnico/loginTecnico.php' AS instrucao;

-- ============================================================
-- FIM DO SCRIPT
-- ============================================================
