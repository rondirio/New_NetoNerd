# 📝 Histórico de Alterações

## Versão 2.0 (Atual) - Sistema Multi-usuário

### 🔄 Mudanças na Estrutura de Arquivos

**IMPORTANTE:** A estrutura de URLs mudou!

| Antes (v1.0) | Agora (v2.0) | Descrição |
|--------------|--------------|-----------|
| `index.php` | `despesas.php` | Dashboard principal |
| (não existia) | `index.php` | Página de login |
| (não existia) | `registro.php` | Cadastro de usuários |
| (não existia) | `logout.php` | Sair do sistema |

### ✨ Novidades

**Autenticação e Segurança:**
- ✅ Sistema completo de login/registro
- ✅ Hash de senhas com bcrypt
- ✅ Sessões seguras com tokens
- ✅ Cookie "Lembrar-me" (30 dias)
- ✅ Timeout de inatividade (30 minutos)
- ✅ Proteção contra SQL Injection
- ✅ Proteção contra XSS
- ✅ CSRF protection

**Multi-usuário:**
- ✅ Cada usuário vê apenas suas despesas
- ✅ Isolamento completo de dados
- ✅ Suporte ilimitado de usuários
- ✅ Gestão de sessões por usuário

**Responsividade:**
- ✅ Design 100% responsivo
- ✅ Mobile-first approach
- ✅ Breakpoints: 375px, 480px, 768px, 1024px, 1920px
- ✅ Touch-friendly em dispositivos móveis

**Banco de Dados:**
- ✅ Nova tabela: `usuarios`
- ✅ Nova tabela: `sessoes`
- ✅ Campo `usuario_id` em `despesas`
- ✅ Foreign keys com cascade delete
- ✅ Índices otimizados

### 🔧 Arquivos Modificados

**Novos Arquivos:**
- `classes/Auth.php`
- `index.php` (login)
- `registro.php`
- `logout.php`
- `despesas.php` (renomeado de index.php)
- `MIGRACAO.md`
- `CHANGELOG.md`

**Arquivos Atualizados:**
- `database.sql` - Novas tabelas e procedures
- `classes/Despesa.php` - Filtros por usuário
- `.htaccess` - DirectoryIndex atualizado
- `README.md` - Documentação atualizada
- Todos os arquivos principais - Proteção de autenticação

### 🚀 Como Atualizar de v1.0 para v2.0

Consulte o arquivo `MIGRACAO.md` para instruções detalhadas.

### ⚠️ Breaking Changes

1. **URL de Acesso Mudou:**
   - Antes: `http://seu-site.com/despesas/index.php` → Dashboard
   - Agora: `http://seu-site.com/despesas/` → Login
   - Dashboard: `http://seu-site.com/despesas/despesas.php`

2. **Autenticação Obrigatória:**
   - Todas as páginas agora exigem login
   - Sessões expiram após 30 minutos de inatividade

3. **Banco de Dados:**
   - Novas tabelas obrigatórias
   - Campo `usuario_id` obrigatório em despesas

---

## Versão 1.0 - Sistema Básico

### ✨ Funcionalidades Iniciais

**Gestão de Despesas:**
- ✅ CRUD completo de despesas
- ✅ Categorização
- ✅ Filtros por mês/ano/status
- ✅ Débito automático

**Despesas Recorrentes:**
- ✅ Marcação de despesas recorrentes
- ✅ Geração automática mensal
- ✅ Cron jobs

**Relatórios:**
- ✅ Dashboard com estatísticas
- ✅ Relatórios mensais
- ✅ Envio por email (PHPMailer)

**API:**
- ✅ API REST para boletos pendentes
- ✅ Resposta em JSON

**Interface:**
- ✅ Design moderno
- ✅ Animações suaves
- ✅ Responsivo básico

---

## 🔮 Roadmap Futuro

### Versão 2.1 (Planejado)
- [ ] Recuperação de senha por email
- [ ] Verificação de email em 2 etapas
- [ ] Avatar/foto de perfil
- [ ] Temas claro/escuro
- [ ] Configurações de notificações

### Versão 2.2 (Planejado)
- [ ] Gráficos interativos (Chart.js)
- [ ] Exportação para PDF
- [ ] Importação de OFX/CSV
- [ ] Compartilhamento de despesas (famílias)

### Versão 3.0 (Futuro)
- [ ] App mobile nativo
- [ ] IA para previsão de gastos
- [ ] Integração bancária
- [ ] Múltiplas moedas

---

**Data da última atualização:** Janeiro 2026  
**Desenvolvido com ❤️ em PHP**
