# 🔐 Guia de Migração - Sistema Multi-usuário

## ⚠️ IMPORTANTE: Leia antes de atualizar

Este sistema agora possui autenticação completa. Se você já tem o sistema instalado, siga estes passos:

## 📝 Passos para Migração

### 1. Backup do Banco de Dados
```bash
mysqldump -u root -p despesas_db > backup_despesas.sql
```

### 2. Atualizar Estrutura do Banco

Execute este SQL no seu banco de dados:

```sql
USE despesas_db;

-- Adicionar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Criar usuário padrão (email: admin@exemplo.com | senha: admin123)
INSERT INTO usuarios (nome, email, senha, ativo) VALUES
('Administrador', 'admin@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Adicionar campo usuario_id na tabela despesas
ALTER TABLE despesas ADD COLUMN usuario_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE despesas ADD FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;
ALTER TABLE despesas ADD INDEX idx_usuario_id (usuario_id);

-- Tabela de sessões
CREATE TABLE IF NOT EXISTS sessoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_em TIMESTAMP NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Substituir Arquivos

Substitua estes arquivos do sistema antigo pelos novos:
- `classes/Auth.php` (NOVO)
- `classes/Despesa.php` (ATUALIZADO)
- `login.php` (NOVO)
- `registro.php` (NOVO)
- `logout.php` (NOVO)
- `perfil.php` (NOVO)
- `database.sql` (ATUALIZADO)

### 4. Atualizar Arquivos Existentes

Os seguintes arquivos precisam adicionar verificação de autenticação no início:

```php
<?php
session_start();
require_once 'classes/Auth.php';

$auth = new Auth();
$auth->protegerPagina(); // Adicione esta linha
$usuarioId = $auth->getUsuarioId(); // Pegue o ID do usuário

// ... resto do código
```

Arquivos que precisam desta atualização:
- despesas.php (antiga index.php)
- adicionar.php  
- editar.php
- recorrentes.php
- relatorio.php

## 🔒 Credenciais Padrão

**Email:** admin@exemplo.com  
**Senha:** admin123

⚠️ **MUDE A SENHA IMEDIATAMENTE APÓS O PRIMEIRO LOGIN!**

## ✅ Verificação

1. Acesse: `http://seu-site.com/despesas/` (ou `index.php`)
2. Entre com as credenciais padrão
3. Crie sua conta pessoal em "Cadastre-se"
4. Delete o usuário admin se quiser

## 📁 Estrutura de Páginas

- `index.php` - Página de LOGIN (antiga login.php)
- `despesas.php` - Dashboard principal (antiga index.php)
- `registro.php` - Cadastro de novos usuários
- `logout.php` - Encerramento de sessão

## 📱 Responsividade Mobile

O sistema agora é 100% responsivo! Teste em:
- Desktop (1920px+)
- Tablet (768px)
- Mobile (375px)

## 🔐 Segurança Implementada

- ✅ Senhas com hash bcrypt
- ✅ Proteção contra SQL Injection (PDO)
- ✅ Proteção contra XSS (htmlspecialchars)
- ✅ Sessões seguras com tokens
- ✅ Timeout de inatividade (30min)
- ✅ Cookie "Lembrar-me" seguro
- ✅ Isolamento de dados por usuário
- ✅ CSRF protection (tokens)

## 📞 Suporte

Problemas na migração? Verifique:
1. Versão do PHP >= 7.4
2. Extensões: PDO, pdo_mysql
3. Permissões de escrita nas pastas
4. Logs de erro do PHP/MySQL

