# 🔐 Sistema de Login - NetoNerd ITSM

## 📋 Visão Geral

O sistema possui **3 tipos de usuários** com logins e permissões diferentes:

| Tipo | Login | Página de Login | Permissões |
|------|-------|----------------|------------|
| **Cliente** | Email + Senha | `/publics/login.php` | Área do cliente (abrir/ver chamados) |
| **Técnico** | Matrícula + Senha | `/tecnico/loginTecnico.php` | Atender chamados, ver filas |
| **Administrador** | Matrícula + Senha | `/tecnico/loginTecnico.php` | Acesso total ao sistema |

---

## 🎯 Identificação de Tipo de Usuário

### Clientes
- **Login:** Email
- **Cadastro:** Tabela `clientes`
- **Sessão:** `$_SESSION['tipo'] = 'cliente'`
- **Acesso:** `/cliente/*`

### Técnicos
- **Login:** Matrícula (ex: `2026F001`)
- **Formato da Matrícula:** `[ANO]F[NÚMERO]`
  - Exemplo: `2025F001`, `2026F045`
  - O **"F"** identifica que é técnico
- **Cadastro:** Tabela `tecnicos`
- **Sessão:** `$_SESSION['tipo'] = 'tecnico'`
- **Acesso:** `/tecnico/*`

### Administradores
- **Login:** Matrícula (ex: `2026ADM001` ou `2026A001`)
- **Formato da Matrícula:**
  - `[ANO]ADM[NÚMERO]` (ex: `2025ADM001`, `2026ADM999`)
  - `[ANO]A[NÚMERO]` (ex: `2025A001`, `2026A005`)
- **Cadastro:** Tabela `tecnicos`
- **Sessão:** `$_SESSION['tipo'] = 'admin'`
- **Acesso:** `/admin/*` + `/tecnico/*`

---

## 🔑 Lógica de Identificação

```php
// Em: controller/valida_loginTecnico.php

function isAdmin($matricula) {
    // Verifica se matrícula contém "ADM" ou formato "AAAAA###"
    return (
        stripos($matricula, 'ADM') !== false ||  // Ex: 2026ADM001
        preg_match('/\d{4}A\d{3}/', $matricula) === 1  // Ex: 2026A001
    );
}

// Ao fazer login:
$tipo_usuario = isAdmin($matricula) ? 'admin' : 'tecnico';
```

---

## 🛡️ Sistema de Proteção

### Middleware de Autenticação

**Arquivo:** `controller/auth_middleware.php`

Funções disponíveis:

```php
// Verificar tipo de usuário
isAuthenticated()  // Qualquer usuário autenticado
isAdmin()          // Apenas administrador
isTecnico()        // Técnico OU administrador
isCliente()        // Apenas cliente

// Proteger rotas (redireciona se não autorizado)
requireAuth()      // Qualquer usuário autenticado
requireAdmin()     // APENAS administradores
requireTecnico()   // Técnicos ou admins
requireCliente()   // APENAS clientes

// Utilitários
getUserType()      // Retorna: 'admin', 'tecnico' ou 'cliente'
getUserId()        // ID do usuário
getUserName()      // Nome do usuário
```

### Exemplo de Uso

```php
<?php
// No topo de qualquer página administrativa
session_start();
require_once '../controller/auth_middleware.php';

// Bloqueia técnicos e clientes
requireAdmin();

// Resto do código...
?>
```

### Páginas Protegidas

Todas as páginas abaixo **só podem ser acessadas por ADMINISTRADORES**:

```
✅ /admin/licencas.php           - Gerar licenças
✅ /admin/processar_licenca.php  - Processar licenças
✅ /admin/categorias.php         - Gerenciar categorias
✅ /admin/processar_categoria.php
✅ /admin/apresenta_tecnicos.php - Listar técnicos
✅ /admin/chamados_ativos.php    - Ver todos os chamados
✅ /admin/relatorios.php         - Relatórios
✅ /admin/configura.php          - Configurações
```

---

## 🧪 Usuários de Teste

Execute o script SQL para criar usuários de teste:

```bash
mysql -u root -p netonerd_chamados < config/bandoDeDados/criar_usuarios_teste.sql
```

### Credenciais Criadas

| Tipo | Login | Senha | Onde Fazer Login |
|------|-------|-------|------------------|
| **Cliente** | `cliente@teste.com` | `teste123` | `/publics/login.php` |
| **Técnico** | `2026F001` | `tecnico123` | `/tecnico/loginTecnico.php` |
| **Admin** | `2026ADM001` | `admin123` | `/tecnico/loginTecnico.php` |
| **Admin (alt)** | `2026A002` | `admin456` | `/tecnico/loginTecnico.php` |

---

## 🔄 Fluxo de Login

### 1. Cliente

```
1. Acessa: /publics/login.php
2. Insere: email + senha
3. Validação: controller/valida_login.php
4. Sucesso → Redireciona para: /cliente/home.php
5. Sessão: $_SESSION['tipo'] = 'cliente'
```

### 2. Técnico

```
1. Acessa: /tecnico/loginTecnico.php
2. Insere: matrícula (ex: 2026F001) + senha
3. Validação: controller/valida_loginTecnico.php
4. Sistema detecta: NÃO é admin (não tem "ADM" ou "A")
5. Sucesso → Redireciona para: /tecnico/paineltecnico.php
6. Sessão: $_SESSION['tipo'] = 'tecnico'
```

### 3. Administrador

```
1. Acessa: /tecnico/loginTecnico.php
2. Insere: matrícula (ex: 2026ADM001) + senha
3. Validação: controller/valida_loginTecnico.php
4. Sistema detecta: É ADMIN (tem "ADM" na matrícula)
5. Sucesso → Redireciona para: /admin/dashboard.php
6. Sessão: $_SESSION['tipo'] = 'admin'
```

---

## 🚨 Mensagens de Erro

| Erro | Significado |
|------|-------------|
| `credenciais_invalidas` | Email/matrícula ou senha incorretos |
| `campos_vazios` | Login ou senha não preenchidos |
| `conta_inativa` | Técnico/admin com status inativo |
| `bloqueado` | Muitas tentativas de login (5 falhas = bloqueio por 15 min) |
| `acesso_negado` | Tentou acessar área sem permissão |
| `nao_autenticado` | Não está logado |

---

## 🔒 Segurança Implementada

### 1. Proteção contra SQL Injection
- ✅ Prepared statements em todos os queries
- ✅ Sanitização de entrada (`trim()`, `filter_var()`)

### 2. Proteção de Senha
- ✅ Senhas hasheadas com `password_hash()` (BCRYPT)
- ✅ Validação com `password_verify()`
- ✅ Migração automática de senhas texto plano para hash

### 3. Bloqueio por Tentativas
- ✅ Máximo: 5 tentativas
- ✅ Bloqueio: 15 minutos
- ✅ Por matrícula/email (não por IP)

### 4. Sessão Segura
- ✅ Regeneração de ID da sessão após login
- ✅ Token CSRF gerado
- ✅ Registro de IP e User Agent
- ✅ Timestamp de última atividade

### 5. Logs de Auditoria
- ✅ Todas as tentativas de login registradas
- ✅ Acessos não autorizados logados
- ✅ Tabela `logs_sistema` com histórico completo

---

## 📝 Como Criar Novos Usuários

### Criar Cliente

```sql
-- Gerar hash da senha (use em PHP):
-- password_hash('SenhaDoCliente123', PASSWORD_BCRYPT)

INSERT INTO clientes (nome, email, telefone, senha_hash)
VALUES (
    'Nome do Cliente',
    'email@cliente.com',
    '21987654321',
    '$2y$10$...' -- Hash da senha
);
```

### Criar Técnico

```sql
-- Matrícula deve ter "F": 2026F002, 2026F003...

INSERT INTO tecnicos (nome, email, matricula, senha_hash, status_tecnico, Ativo)
VALUES (
    'Nome do Técnico',
    'tecnico@netonerd.com.br',
    '2026F002',  -- Formato: ANOF###
    '$2y$10$...',  -- Hash da senha
    'Ativo',
    1
);
```

### Criar Administrador

```sql
-- Matrícula deve ter "ADM" ou "A": 2026ADM002, 2026A003...

INSERT INTO tecnicos (nome, email, matricula, senha_hash, status_tecnico, Ativo)
VALUES (
    'Nome do Admin',
    'admin@netonerd.com.br',
    '2026ADM002',  -- Formato: ANOADM### ou ANOA###
    '$2y$10$...',  -- Hash da senha
    'Ativo',
    1
);
```

---

## 🧩 Gerar Hash de Senha

Use este código PHP para gerar hashes:

```php
<?php
$senha = 'MinhaSenh@ Forte123';
$hash = password_hash($senha, PASSWORD_BCRYPT);
echo "Hash: " . $hash;
?>
```

Ou crie um arquivo temporário `gerar_hash.php`:

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = $_POST['senha'];
    $hash = password_hash($senha, PASSWORD_BCRYPT);
    echo "<pre>";
    echo "Senha: " . htmlspecialchars($senha) . "\n";
    echo "Hash: " . $hash . "\n";
    echo "</pre>";
} else {
    echo '<form method="POST">';
    echo '<input type="text" name="senha" placeholder="Digite a senha">';
    echo '<button type="submit">Gerar Hash</button>';
    echo '</form>';
}
?>
```

---

## ✅ Checklist de Implementação

- [x] Sistema de login de clientes corrigido
- [x] Sistema de login de técnicos corrigido
- [x] Detecção automática de administradores por matrícula
- [x] Middleware de proteção de rotas
- [x] Todas as páginas admin protegidas
- [x] Usuários de teste criados
- [x] Documentação completa

---

## 🆘 Problemas Comuns

### "Acesso negado" mesmo sendo admin
- ✅ Verifique se a matrícula tem "ADM" ou "A"
- ✅ Exemplos válidos: `2026ADM001`, `2026A001`
- ✅ Exemplos inválidos: `2026F001` (técnico)

### Não consigo logar com nenhum usuário
- ✅ Execute o script: `criar_usuarios_teste.sql`
- ✅ Verifique se o banco está acessível
- ✅ Verifique os logs: `tail -f /var/log/apache2/error.log`

### Senha não funciona
- ✅ Certifique-se que está usando o hash correto
- ✅ O sistema migra senhas texto plano automaticamente
- ✅ Use `password_hash()` para gerar novos hashes

---

**© 2026 NetoNerd Soluções Digitais LTDA**
