# Guia de Migração para Design v2.0
**NetoNerd ITSM - Sistema de Design Padronizado**

---

## 📋 Visão Geral

Este guia explica como migrar páginas existentes para o novo sistema de design v2.0 com:
- ✅ Paleta de cores padronizada (da landing page)
- ✅ Header responsivo global
- ✅ Componentes reutilizáveis
- ✅ 100% responsivo mobile
- ✅ Performance otimizada

---

## 🎨 Paleta de Cores

### Cores Principais
```css
--primary-blue: #007bff          /* Azul principal */
--primary-blue-dark: #0056b3     /* Azul escuro */
--primary-blue-light: #3395ff    /* Azul claro */
```

### Gradientes
```css
--gradient-primary: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
--gradient-light: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
```

### Cores de Fundo
```css
--bg-white: #ffffff
--bg-light: #f8f9fa
--bg-lighter: #e9ecef
--bg-dark: #2c3e50
```

### Cores de Status
```css
--success: #28a745        /* Verde - sucesso */
--warning: #ffc107        /* Amarelo - aviso */
--danger: #dc3545         /* Vermelho - perigo */
--info: #17a2b8           /* Azul claro - informação */
```

---

## 🔄 Como Migrar uma Página

### **Passo 1: Substituir Header**

#### Antes:
```php
<!DOCTYPE html>
<html>
<head>
    <title>Página</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos inline ou separados -->
</head>
<body>
    <!-- Navbar customizada -->
    <nav>...</nav>
```

#### Depois:
```php
<?php
$page_title = "Título da Página - NetoNerd ITSM";
require_once '../includes/header.php';
?>
```

**Benefícios:**
- ✅ Header responsivo automático
- ✅ Menu adaptado ao tipo de usuário (admin/técnico/cliente)
- ✅ Badges de notificação automáticas
- ✅ Avatar com iniciais do usuário
- ✅ Botão de logout padronizado

---

### **Passo 2: Usar Estrutura de Layout**

#### Antes:
```html
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2">
            <!-- Sidebar customizada -->
        </div>
        <div class="col-md-10">
            <!-- Conteúdo -->
        </div>
    </div>
</div>
```

#### Depois:
```html
<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">
        <!-- Conteúdo -->
    </div>
</div>
```

**Benefícios:**
- ✅ Padding automático considerando header
- ✅ Responsivo automaticamente
- ✅ Sem necessidade de sidebar customizada

---

### **Passo 3: Usar Cards Padronizados**

#### Antes:
```html
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5>Título</h5>
    </div>
    <div class="card-body">
        Conteúdo
    </div>
</div>
```

#### Depois:
```html
<div class="nn-card">
    <div class="nn-card-header">
        <h2 class="nn-card-title">
            <i class="fas fa-icon"></i>
            Título
        </h2>
        <div>
            <!-- Botões de ação -->
        </div>
    </div>
    <div class="nn-card-body">
        Conteúdo
    </div>
</div>
```

---

### **Passo 4: Usar Componentes de Dashboard**

#### Stats Cards

```html
<div class="nn-stats-grid">
    <div class="nn-stat-card">
        <div class="nn-stat-icon primary">
            <i class="fas fa-folder-open"></i>
        </div>
        <div class="nn-stat-value">25</div>
        <div class="nn-stat-label">Chamados Abertos</div>
    </div>

    <div class="nn-stat-card success">
        <div class="nn-stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="nn-stat-value">150</div>
        <div class="nn-stat-label">Resolvidos</div>
    </div>

    <!-- Mais cards... -->
</div>
```

**Tipos de stat-card:**
- `primary` (azul)
- `success` (verde)
- `warning` (amarelo)
- `danger` (vermelho)
- `info` (azul claro)

---

### **Passo 5: Usar Badges Padronizados**

#### Antes:
```html
<span class="badge bg-primary">Aberto</span>
<span class="badge bg-success">Resolvido</span>
```

#### Depois:
```html
<span class="nn-badge nn-badge-primary">
    <i class="fas fa-folder-open"></i>
    Aberto
</span>

<span class="nn-badge nn-badge-success">
    <i class="fas fa-check-circle"></i>
    Resolvido
</span>
```

**Tipos de badges:**
- `nn-badge-primary` - Azul
- `nn-badge-success` - Verde
- `nn-badge-warning` - Amarelo
- `nn-badge-danger` - Vermelho
- `nn-badge-info` - Azul claro

**Badges de Prioridade:**
- `nn-badge-critical` - Crítica (vermelho)
- `nn-badge-high` - Alta (laranja)
- `nn-badge-medium` - Média (amarelo)
- `nn-badge-low` - Baixa (verde)

---

### **Passo 6: Usar Botões Padronizados**

#### Antes:
```html
<button class="btn btn-primary">
    <i class="fas fa-save"></i> Salvar
</button>
```

#### Depois:
```html
<button class="nn-btn nn-btn-primary">
    <i class="fas fa-save"></i>
    Salvar
</button>
```

**Tipos de botões:**
- `nn-btn-primary` - Azul gradiente
- `nn-btn-success` - Verde
- `nn-btn-warning` - Amarelo
- `nn-btn-danger` - Vermelho
- `nn-btn-secondary` - Cinza
- `nn-btn-outline-primary` - Borda azul

**Tamanhos:**
- `nn-btn-sm` - Pequeno
- `nn-btn` - Normal
- `nn-btn-lg` - Grande

---

### **Passo 7: Usar Tabelas Padronizadas**

#### Antes:
```html
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th>Coluna 1</th>
            <th>Coluna 2</th>
        </tr>
    </thead>
    <tbody>
        <!-- Linhas -->
    </tbody>
</table>
```

#### Depois:
```html
<div class="nn-table">
    <table>
        <thead>
            <tr>
                <th>Coluna 1</th>
                <th>Coluna 2</th>
            </tr>
        </thead>
        <tbody>
            <!-- Linhas -->
        </tbody>
    </table>
</div>
```

**Benefícios:**
- ✅ Header com gradiente azul
- ✅ Hover automático
- ✅ Scroll horizontal em mobile
- ✅ Bordas arredondadas

---

### **Passo 8: Usar Formulários Padronizados**

#### Antes:
```html
<div class="mb-3">
    <label class="form-label">Campo</label>
    <input type="text" class="form-control">
</div>
```

#### Depois:
```html
<div class="nn-form-group">
    <label class="nn-form-label">Campo</label>
    <input type="text" class="nn-form-control">
</div>
```

**Benefícios:**
- ✅ Focus com borda azul
- ✅ Sombra suave no focus
- ✅ Espaçamento consistente
- ✅ Estados disabled padronizados

---

### **Passo 9: Usar Alertas Padronizados**

#### Antes:
```html
<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
```

#### Depois:
```html
<?php if (isset($_GET['sucesso'])): ?>
    <div class="nn-alert nn-alert-success nn-animate-fade">
        <i class="fas fa-check-circle"></i>
        Operação realizada com sucesso!
    </div>
<?php endif; ?>
```

**Tipos de alertas:**
- `nn-alert-success` - Verde
- `nn-alert-warning` - Amarelo
- `nn-alert-danger` - Vermelho
- `nn-alert-info` - Azul claro

---

### **Passo 10: Substituir Footer**

#### Antes:
```html
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scripts customizados
    </script>
</body>
</html>
```

#### Depois:
```php
<?php
// Scripts customizados (opcional)
$extra_js = '<script>
    // Seu código aqui
</script>';

require_once '../includes/footer.php';
?>
```

---

## 📱 Responsividade Mobile

### Breakpoints

```css
/* Tablets */
@media (max-width: 992px) { }

/* Smartphones */
@media (max-width: 768px) { }

/* Smartphones pequenos */
@media (max-width: 480px) { }
```

### Classes Utilitárias

**Ocultar em mobile:**
```html
<span class="nn-hidden-mobile">Texto oculto em mobile</span>
```

**Grid responsivo:**
```html
<div class="nn-stats-grid">
    <!-- Auto-ajusta para mobile -->
</div>
```

**Botões full-width em mobile:**
```html
<button class="nn-btn nn-btn-primary">
    <!-- Automaticamente 100% em mobile -->
</button>
```

---

## 🎬 Animações

### Fade In
```html
<div class="nn-animate-fade">
    Conteúdo com fade in
</div>
```

### Slide Up
```html
<div class="nn-animate-slide">
    Conteúdo deslizando para cima
</div>
```

### Pulse (animação contínua)
```html
<div class="nn-animate-pulse">
    Conteúdo pulsando
</div>
```

---

## 🔧 Classes Utilitárias

### Espaçamento
```css
.nn-mt-1  /* margin-top pequeno */
.nn-mt-2  /* margin-top médio */
.nn-mt-3  /* margin-top grande */

.nn-mb-1  /* margin-bottom pequeno */
.nn-mb-2  /* margin-bottom médio */
.nn-mb-3  /* margin-bottom grande */
```

### Flexbox
```css
.nn-d-flex           /* display: flex */
.nn-align-center     /* align-items: center */
.nn-justify-between  /* justify-content: space-between */

.nn-gap-1  /* gap pequeno */
.nn-gap-2  /* gap médio */
.nn-gap-3  /* gap grande */
```

### Texto
```css
.nn-text-center  /* text-align: center */
.nn-text-right   /* text-align: right */
```

---

## 📁 Exemplo Completo de Migração

### Antes (página antiga):
```php
<!DOCTYPE html>
<html>
<head>
    <title>Chamados</title>
    <link href="bootstrap.css" rel="stylesheet">
    <style>
        /* Estilos customizados */
        .custom-card { background: #fff; }
        .custom-header { background: #007bff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <a href="/">NetoNerd</a>
        <div>
            <a href="/chamados">Chamados</a>
            <a href="/logout">Sair</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success">Sucesso!</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Chamados</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Chamado Teste</td>
                            <td><span class="badge bg-success">Resolvido</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="bootstrap.bundle.js"></script>
</body>
</html>
```

### Depois (página nova):
```php
<?php
$page_title = "Chamados - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="nn-alert nn-alert-success nn-animate-fade">
                <i class="fas fa-check-circle"></i>
                Operação realizada com sucesso!
            </div>
        <?php endif; ?>

        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-list"></i>
                    Chamados
                </h2>
            </div>

            <div class="nn-card-body">
                <div class="nn-table">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Chamado Teste</td>
                                <td>
                                    <span class="nn-badge nn-badge-success">
                                        <i class="fas fa-check"></i>
                                        Resolvido
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
```

**Resultado:**
- ✅ 80% menos código HTML
- ✅ 100% responsivo
- ✅ Paleta padronizada
- ✅ Animações suaves
- ✅ Header global
- ✅ Manutenção simplificada

---

## ✅ Checklist de Migração

### Para cada página:

- [ ] Substituir header customizado por `require_once '../includes/header.php'`
- [ ] Substituir footer customizado por `require_once '../includes/footer.php'`
- [ ] Envolver conteúdo em `<div class="nn-main-wrapper"><div class="nn-content nn-content-full">`
- [ ] Trocar classes `card` por `nn-card`
- [ ] Trocar classes `btn btn-primary` por `nn-btn nn-btn-primary`
- [ ] Trocar classes `badge` por `nn-badge`
- [ ] Trocar classes `alert` por `nn-alert`
- [ ] Trocar `<table class="table">` por `<div class="nn-table"><table>`
- [ ] Trocar `form-control` por `nn-form-control`
- [ ] Trocar `form-label` por `nn-form-label`
- [ ] Adicionar ícones Font Awesome nos títulos
- [ ] Adicionar animações (`nn-animate-fade`, `nn-animate-slide`)
- [ ] Testar em mobile (< 768px)
- [ ] Verificar todas as cores seguem paleta

---

## 🎯 Prioridade de Migração

### Alta Prioridade:
1. ✅ admin/atribuir_chamados.php
2. ✅ admin/chamados_ativos.php
3. ✅ tecnico/meus_chamados.php
4. ✅ tecnico/resolver_chamado.php
5. ✅ cliente/home.php

### Média Prioridade:
- admin/apresenta_tecnicos.php
- admin/relatorios.php
- admin/licencas.php
- tecnico/paineltecnico.php
- cliente/meus_chamados.php

### Baixa Prioridade:
- Páginas de configuração
- Páginas de relatórios
- Páginas administrativas secundárias

---

## 🚀 Deploy

### Passo 1: Verificar arquivos criados
```bash
ls -la assets/css/netonerd-global.css
ls -la includes/header.php
ls -la includes/footer.php
```

### Passo 2: Limpar cache do navegador
```
Ctrl + Shift + R (ou Cmd + Shift + R no Mac)
```

### Passo 3: Testar em diferentes dispositivos
- Desktop (> 1200px)
- Tablet (768px - 992px)
- Smartphone (< 768px)

### Passo 4: Validar cores
- Abrir landing page (publics/index.php)
- Comparar cores com dashboard
- Garantir consistência visual

---

## 📞 Suporte

Para dúvidas sobre a migração, consulte:
- `includes/page-template-example.php` - Template completo
- `assets/css/netonerd-global.css` - Todos os componentes CSS
- `docs/SISTEMA_GERENCIAMENTO_CHAMADOS.md` - Documentação técnica

---

**Última atualização:** 2026-01-21
**Versão:** 2.0
