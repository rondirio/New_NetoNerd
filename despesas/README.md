# 💰 Sistema de Gerenciamento de Despesas

Sistema profissional e moderno para controle de despesas pessoais ou empresariais, desenvolvido em PHP com MySQLi/PDO.

## 🎯 Funcionalidades

### Principais
- ✅ Cadastro completo de despesas (nome, valor, vencimento, forma de pagamento)
- ✅ **Despesas Recorrentes Automáticas** (contas fixas mensais)
- ✅ Controle de débito automático
- ✅ Categorização de despesas
- ✅ Dashboard com estatísticas em tempo real
- ✅ Relatórios detalhados por período
- ✅ Envio de relatórios por email (PHPMailer)
- ✅ API REST para consulta de boletos pendentes
- ✅ Atualização automática de status de despesas vencidas
- ✅ Interface responsiva e moderna
- ✅ Filtros por mês, ano, status e categoria

### Recursos Avançados
- 📊 Gráficos de resumo financeiro
- 🔄 Sistema de débito automático
- 🔁 **Geração automática de despesas recorrentes**
- 📧 Envio automático de relatórios por email
- 🔍 API para integração com outros sistemas
- 📱 Design responsivo (mobile-first)
- 🎨 Interface moderna com animações suaves
- ⚡ Performance otimizada com índices no banco
- 🤖 Cron jobs para automação

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor Apache/Nginx
- Composer (para PHPMailer)
- Extensões PHP:
  - PDO
  - mysqli
  - mbstring
  - json

## 🚀 Instalação

### 1. Clonar/Baixar o Projeto
```bash
# Coloque os arquivos na pasta do seu servidor web
# Exemplo: /var/www/html/despesas ou C:/xampp/htdocs/despesas
```

### 2. Instalar PHPMailer via Composer
```bash
cd despesas
composer require phpmailer/phpmailer
```

### 3. Configurar Banco de Dados

**Opção A: Importar SQL**
```bash
mysql -u root -p < database.sql
```

**Opção B: Executar manualmente**
```sql
mysql -u root -p
source database.sql;
```

### 4. Configurar Credenciais

**Editar `config/database.php`:**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'despesas_db');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

**Editar `config/email.php`:**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'seu-email@gmail.com');
define('SMTP_PASSWORD', 'sua-senha-de-app'); // Usar senha de app do Gmail
define('EMAIL_FROM', 'seu-email@gmail.com');
```

> **⚠️ IMPORTANTE**: Para Gmail, você precisa criar uma senha de app:
> 1. Acesse: https://myaccount.google.com/security
> 2. Ative a verificação em duas etapas
> 3. Vá em "Senhas de app" e gere uma senha
> 4. Use essa senha no `SMTP_PASSWORD`

### 5. Configurar Permissões (Linux)
```bash
chmod -R 755 despesas/
chown -R www-data:www-data despesas/
```

### 6. Acessar o Sistema
```
http://localhost/despesas
ou
http://seu-dominio.com/despesas
```

**Estrutura de Acesso:**
- `/` ou `/landing.php` - Landing page profissional (apresentação do projeto)
- `/index.php` - Login do sistema
- `/registro.php` - Cadastro de novos usuários
- `/despesas.php` - Dashboard (após login)

## 📁 Estrutura do Projeto

```
despesas/
├── config/
│   ├── database.php          # Configurações do banco
│   └── email.php             # Configurações de email
├── classes/
│   ├── Database.php          # Classe de conexão
│   ├── Auth.php              # Sistema de autenticação
│   ├── Despesa.php          # Classe principal (CRUD + Recorrentes)
│   └── EmailService.php     # Serviço de email
├── api/
│   └── boletos.php          # API REST de boletos
├── assets/
│   ├── css/
│   │   └── style.css        # Estilos do sistema
│   └── js/
│       └── script.js        # JavaScript
├── includes/
│   ├── header.php           # Cabeçalho comum
│   └── footer.php           # Rodapé comum
├── logs/                    # Logs do cron (criado automaticamente)
├── index.php                # Página de LOGIN (entrada do sistema)
├── despesas.php             # Dashboard principal (antiga index.php)
├── registro.php             # Cadastro de usuários
├── login.php                # Redirect para index.php
├── logout.php               # Encerramento de sessão
├── adicionar.php            # Adicionar despesa
├── editar.php              # Editar despesa
├── recorrentes.php         # Gerenciar recorrentes
├── relatorio.php           # Visualizar relatório
├── enviar_relatorio.php    # Processar envio de email
├── cron_gerar_recorrentes.php  # Script cron
├── web_cron.php            # Web cron (alternativa)
├── database.sql            # Script SQL de criação
└── MIGRACAO.md             # Guia de migração
```

## 🔌 API de Boletos

### Endpoint
```
GET /despesas/api/boletos.php
```

### Resposta
```json
{
  "success": true,
  "total": 2,
  "data": [
    {
      "id": 1,
      "nome_conta": "Conta de Luz",
      "valor": 250.00,
      "valor_formatado": "R$ 250,00",
      "data_vencimento": "2026-02-15",
      "data_vencimento_formatada": "15/02/2026",
      "dias_para_vencimento": 20,
      "status": "Pendente",
      "vencido": false,
      "debito_automatico": true
    }
  ],
  "timestamp": "2026-01-26 10:30:00"
}
```

### Exemplo de Uso
```javascript
fetch('api/boletos.php')
  .then(response => response.json())
  .then(data => {
    console.log('Boletos pendentes:', data.data);
  });
```

## 💡 Uso do Sistema

### 1. Dashboard
- Visualize estatísticas do mês atual
- Veja total de contas, valores pagos e pendentes
- Acompanhe boletos pendentes em tempo real

### 2. Adicionar Despesa
- Preencha nome da conta, valor e data de vencimento
- Selecione forma de pagamento
- Marque se é débito automático
- **Marque "Despesa Recorrente" para contas fixas mensais**
- Adicione categoria e observações

### 3. Gerenciar Despesas Recorrentes
- Acesse "Recorrentes" no menu principal
- Visualize todas as despesas que se repetem mensalmente
- Veja o custo mensal total de contas fixas
- Gere manualmente as despesas do próximo mês
- Remova recorrência de despesas quando necessário

### 4. Gerenciar Despesas
- Edite despesas existentes
- Marque como pago com um clique
- Delete despesas desnecessárias
- Filtre por mês, ano, status e categoria

### 5. Relatórios
- Visualize relatório completo do período
- Imprima diretamente do navegador
- Envie por email com resumo detalhado

## 🔁 Sistema de Despesas Recorrentes

### Como Funciona
O sistema permite marcar despesas como **recorrentes** (contas fixas como luz, internet, aluguel, etc.). Essas despesas são automaticamente criadas todo mês no dia de vencimento especificado.

### Configuração Automática

**Opção 1: Cron Job (Recomendado - Linux/macOS)**
```bash
# Editar crontab
crontab -e

# Adicionar linha para executar todo dia 1º às 01:00
0 1 1 * * /usr/bin/php /caminho/completo/para/despesas/cron_gerar_recorrentes.php
```

**Opção 2: Event Scheduler do MySQL (Automático)**
O sistema já cria um evento MySQL que executa automaticamente. Certifique-se de que o event_scheduler está habilitado:
```sql
SET GLOBAL event_scheduler = ON;
```

**Opção 3: Web Cron (Para servidores sem acesso ao cron)**
1. Edite `web_cron.php` e defina uma senha forte
2. Configure um serviço externo de cron (como cron-job.org ou easycron.com)
3. Configure para acessar: `http://seu-site.com/despesas/web_cron.php?senha=SUA_SENHA`

**Opção 4: Manual**
Acesse "Recorrentes" no sistema e clique em "⚡ Gerar Próximo Mês" sempre que necessário.

### Exemplo de Uso
1. Adicione "Conta de Luz" com vencimento no dia 15
2. Marque "🔁 Despesa Recorrente"
3. Todo mês, automaticamente será criada uma nova "Conta de Luz" para o dia 15

### Logs
Os logs de execução ficam em: `/logs/cron_recorrentes.log`

## 🎨 Personalização

### Cores do Tema
Edite as variáveis CSS em `assets/css/style.css`:

```css
:root {
    --primary-color: #3498db;    /* Cor principal */
    --secondary-color: #2c3e50;  /* Cor secundária */
    --success-color: #27ae60;    /* Cor de sucesso */
    --danger-color: #e74c3c;     /* Cor de erro */
}
```

### Adicionar Categorias Padrão
Edite o arquivo `adicionar.php` na seção de datalist:

```html
<datalist id="categorias">
    <option value="Sua Nova Categoria">
</datalist>
```

## 🔒 Segurança

### Implementado
- ✅ Prepared Statements (PDO) - Proteção contra SQL Injection
- ✅ htmlspecialchars() - Proteção contra XSS
- ✅ Validação de dados no servidor
- ✅ Senhas de app para email (não armazena senha real)

### Recomendações Adicionais
- [ ] Implementar autenticação de usuários
- [ ] Adicionar HTTPS
- [ ] Implementar CSRF tokens
- [ ] Adicionar rate limiting na API
- [ ] Implementar backup automático

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
```
Erro: Could not connect to database
```
**Solução**: Verifique as credenciais em `config/database.php`

### Email não Envia
```
Erro: SMTP Error
```
**Solução**: 
1. Verifique se usou senha de app (não a senha normal)
2. Habilite "Acesso a app menos seguro" (se necessário)
3. Verifique firewall bloqueando porta 587

### Despesas Vencidas não Atualizam
**Solução**: O sistema atualiza automaticamente ao carregar o index. Para automação total, configure um cron job:

```bash
# Executar todo dia às 00:00
0 0 * * * php /caminho/para/despesas/atualizar_vencidas.php
```

## 📊 Banco de Dados

### Tabelas Principais

**despesas**
- Armazena todas as despesas
- Índices em: data_vencimento, status, categoria
- Auto-incremento no ID

**v_resumo_mensal** (View)
- Resumo automático por mês/ano
- Facilita consultas de relatório

### Procedures
- `sp_atualizar_vencidas()` - Atualiza status de vencidas

### Events
- `evt_atualizar_vencidas` - Executa diariamente às 00:00

## 🔄 Atualizações Futuras

Planejado para próximas versões:
- [ ] Autenticação multi-usuário
- [ ] Gráficos interativos (Chart.js)
- [ ] Exportação para PDF
- [ ] Importação de OFX/CSV
- [ ] Previsão de gastos com IA
- [ ] Notificações por WhatsApp
- [ ] App mobile (React Native)

## 📝 Licença

Este projeto é de código aberto. Sinta-se livre para usar e modificar.

## 👨‍💻 Suporte

Para dúvidas ou sugestões:
- Abra uma issue no GitHub
- Entre em contato via email

---

**Desenvolvido com ❤️ em PHP**
