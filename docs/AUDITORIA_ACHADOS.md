# NetoNerd ITSM — Auditoria de Segurança e Estrutura

Documento consolidado dos achados levantados em auditoria do código de produção real (pasta baixada do servidor, incluindo `.env` e dump `u478690921_netonerd.sql` que corresponde ao banco de produção). Cada item foi verificado lendo o código/dado real — não presumido.

Status de cada item: `[ ]` pendente, `[x]` corrigido nesta pasta de trabalho, `[~]` corrigido parcialmente / precisa de ação fora do código (rotação de credencial, teste ao vivo, etc).

---

## CRÍTICO

### C10. Escalação de privilégio: técnico vira admin automaticamente pela matrícula
`controller/valida_loginTecnico.php:20-25` (função `isAdmin()`, replicada em `admin/processar_atribuicao.php:54` e `config/config_systens/auth_system.php:238`):
```php
function isAdmin($matricula) {
    return (stripos($matricula, 'ADM') !== false || preg_match('/\d{4}A\d{3}/', $matricula) === 1);
}
```
Testado contra dados reais do dump: as matrículas `2026A002` (Thaina Quintanilha Ramos) e `2026A001` (Rondineli da Silva Oliveira Moreira) — ambas cadastradas como técnicos comuns na tabela `tecnicos` — batem no regex `\d{4}A\d{3}` e são promovidas a admin no login, ganhando acesso total ao `admin/dashboard.php` e a toda rota protegida por `requireAdmin()`. A convenção "A" = admin / "F" = funcionário na matrícula é intencional (confirmado pelo usuário — essas 2 contas sempre foram admins de propósito), mas usá-la como **única** fonte de verdade permitia que qualquer matrícula nesse padrão virasse admin, mesmo sem essa intenção. Consequência colateral: o filtro espelhado `matricula LIKE '%F%'` em `admin/atribuir_chamados.php:110,127` escondia esses técnicos da lista de atribuição — "técnicos fantasma".
**Status: [x]** Corrigido — decisão do usuário: em vez de coluna de role em `tecnicos`, os admins foram separados fisicamente em tabela `admins` própria (`config/bandoDeDados/migracao_fase4_tabela_admins.sql`). Os 2 registros que já eram admin migraram preservando o `id` original (dados reais em `chamados.criado_por_admin`/`chamado_atribuicoes.admin_id`/`ordens_servico.created_by` já apontavam para esses IDs). A tabela `usuarios` (vestígio morto, só lida em 1 lugar para checar FK antes de logar, não usada como fonte de dado ativo) foi removida junto. `valida_loginTecnico.php` e `auth_system.php` agora tentam `admins` primeiro, depois `tecnicos`; nenhum dos dois decide o cargo por regex. Toda query que fazia `JOIN tecnicos`/`INNER JOIN tecnicos` esperando encontrar quem hoje é admin (relatórios, listagem de OS, detalhe de chamado) foi ajustada para `LEFT JOIN tecnicos ... LEFT JOIN admins ... COALESCE(...)`, já que `tecnico_id`/`admin_id`/`created_by` não têm FK formal e podem apontar para qualquer uma das duas tabelas em dados históricos. FK real que existia em `ordens_servico.tecnico_id`/`created_by` → `tecnicos.id` foi removida na migration (nunca refletiu a regra real: dados mostravam admin nesses campos). Testado via curl local: login com matrícula forjada no padrão antigo (não cadastrada) cai em `credenciais_invalidas`, não vira admin.

### C1. `.env` de produção sem proteção HTTP na raiz
`APP_URL=http://netonerd.com.br/public_html` sugere que este diretório é o document root público. Não havia `.htaccess` na raiz bloqueando dotfiles — `.env`, `$BACKUP_DIR/`, `unicle_execution/`, os `.sql` em `bandoDeDados/` ficavam potencialmente acessíveis via URL direta.
**Status: [x]** `.htaccess` criado na raiz, bloqueando dotfiles, extensões sensíveis (`.sql`, `.env`, `.hd`, `.log`, `.bak`, `.ini`, `.conf`) e as pastas `$BACKUP_DIR`, `backup_20251008_122106`, `unicle_execution`, `bandoDeDados`, `config/bandoDeDados`, `vendor`, `storage/logs`.
**Pendente:** testar em produção que `netonerd.com.br/.env` retorna 403/404 após o deploy.

### C2. API key do Google exposta em texto puro
`$BACKUP_DIR/keyAPINetoNerd` continha uma chave `AIzaSyAn8jv...` em texto puro.
**Status: [~]** Pasta bloqueada via `.htaccess` (C1). **Pendente ação fora do código:** revogar/regenerar a chave no Google Cloud Console.

### C3. Script de reset de senha sem autenticação, publicamente acessível
`unicle_execution/change_passwords.php` reescrevia `senha_hash` de todos os técnicos ativos, sem `session_start()`, sem checagem de login, sem restrição de IP.
**Status: [x]** Copiado para `_REMOVER_DO_SERVIDOR/` e bloqueado via `.htaccess` (C1).
**Pendente:** apagar `unicle_execution/` do servidor de produção via FTP/painel (ação manual, fora do código).

### C4. CSRF token gerado mas nunca validado
`controller/valida_login.php:123` gera `$_SESSION['csrf_token']`, mas nenhum handler de POST no fluxo principal (chamados, cliente, admin, técnico) o valida. Toda ação de escrita do sistema principal fica exposta a CSRF.
**Status: [x]** Corrigido em 2026-07-13 — `generateCsrfToken()`/`csrfField()`/`requireCsrfToken()` adicionadas em `controller/auth_middleware.php` (token de sessão, `hash_equals()`, gerado de forma idempotente — funciona tanto para sessão autenticada quanto anônima, cobrindo os forms públicos de contato/cadastro). Aplicado nos 27 forms POST reais do fluxo principal (`cliente/`, `admin/`, `tecnico/`, `publics/contato.php`, `publics/cadastro.php`) e nos ~19 handlers correspondentes. Testado via `curl` local: POST sem token → 403; POST com token forjado → 403; POST com token real extraído da página → sucesso. Ver `docs/resolucoesAuditoria.md` (Fase 4) para a lista completa arquivo por arquivo. Fora do escopo: cópias de vitrine mortas (`app/`, `assets/cliente/`, `apresenta_escritorius/assets/cliente/`) e StyleManager (S25, item próprio da Fase 4).

### C5. Exclusão de chamado via GET, sem CSRF
`cliente/excluir_chamado.php` (e as 2 cópias) executa `DELETE FROM chamados` disparado por requisição GET simples, sem token.
**Status: [x]** Corrigido em 2026-07-13, junto com C4 — `cliente/excluir_chamado.php` agora exige POST + `requireCsrfToken()` (arquivo confirmado órfão, sem link algum no fluxo ativo hoje, mas continuava acessível por URL direta). Mesma correção aplicada por extensão a 2 outras ações destrutivas que tinham o mesmo padrão de GET simples, achadas durante o levantamento: `cliente/fechar_chamado.php` (o JS de "Confirmar Resolução" em `visualizar_chamado.php` foi trocado de `window.location.href` para um form POST com token) e `admin/excluir_tecnico.php` (o link de exclusão em `admin/dashboard.php` virou form POST com token). As 2 cópias mortas de `cliente/excluir_chamado.php` (`assets/cliente/`, `apresenta_escritorius/assets/cliente/`) não foram tocadas — decisão já registrada (M1/BE11): destino delas é da Fase 6.

### C6. `validador_acesso.php` sem `exit()` — controle de acesso quebrado
Redirect sem `exit()`/`die()` — a página protegida continuava sendo renderizada mesmo sem sessão válida. Afetava 21 arquivos, incluindo o núcleo do portal do cliente.
**Status: [x]** Corrigido — `exit();` adicionado após o `header('Location: ...')`.

### C7. Upload de anexo no admin confia no Content-Type do navegador
`admin/abrir_chamado_admin.php:138` valida usando `$_FILES['anexos']['type'][$i]` (declarado pelo cliente HTTP, falsificável), não magic bytes. Combinado com ausência de `.htaccess` em `uploads/` bloqueando execução de PHP, é um caminho realista para upload de webshell.
**Status: [x]** Corrigido — `admin/abrir_chamado_admin.php` agora valida via `finfo`/magic bytes (mesmo padrão de `cliente/registra_chamado.php`/`tecnico/processar_resolucao.php`), e `uploads/.htaccess` criado desabilitando execução de PHP na pasta inteira (cobre `uploads/anexos/` e `uploads/chamados/`).

### C8. Endpoint que devolve senha de banco de dados em texto reversível, CORS aberto
`api/validar_chave.php` retorna `database.password` via `base64_decode()` (não é criptografia real, apesar do nome `$db_senha_encrypted` em `admin/processar_api_key.php`). `Access-Control-Allow-Origin: *` libera qualquer origem. `ip_permitido` é opcional, não obrigatório.
**Status: [ ]** Tabela `api_keys` está vazia hoje — não vazou dado real ainda, mas é funcionalidade ativa. Pendente: remover CORS aberto, tornar IP obrigatório, substituir base64 por criptografia real ou por token de conexão temporário em vez da credencial bruta.

### C9. Senha de funcionária em texto plano no banco de produção
Tabela `tecnicos`, coluna `senha` (redundante — o campo correto é `senha_hash`). Registro real: `id=2`, senha em texto puro.
**Status: [x]** Coluna `senha` removida na Fase 5 (ver BE26). **Pré-requisito ainda pendente antes de rodar a migração em produção**: confirmar que `senha_hash` está populada e devidamente hasheada (não texto puro) para todos os técnicos, e forçar troca de senha da pessoa afetada (id=2 em produção) — a remoção da coluna localmente não apaga esse passo em produção, só o torna irreversível depois de feito.

---

## ALTO

### A1. Status gravado pelo código não existe no ENUM real do banco
Schema real: `status enum('aberto','em andamento','pendente','resolvido','cancelado')`. As 3 cópias de `fechar_chamado.php` tentam gravar `status = 'fechado'` (inexistente) e `data_ultima_atualizacao` (coluna inexistente — a real é `data_atualizacao`). O UPDATE falha com erro em modo estrito — o botão "fechar chamado" do cliente está quebrado em produção agora, não é risco latente.
**Status: [ ]** Pendente — decidir entre adicionar `'fechado'` ao ENUM (em `chamados` e `historico_chamados`) ou reaproveitar um status existente, e corrigir o nome da coluna.

### A2. Conexão hardcoded usada por 106 arquivos, divergente da conexão real de produção
`bandoDeDados/conexao.php` (hardcoded: `root@localhost`, senha vazia, banco `netonerd`) é usada por 106 arquivos, incluindo todo `admin/`. `config/bandoDeDados/conexao.php` (lê do `.env`, credenciais reais Hostinger) é usada por 68. O CHANGELOG afirma ter corrigido isso ("Removidas credenciais hardcoded"), mas a correção nunca foi propagada.
**Status: [x]** Já estava resolvido antes da Fase 5, sem ter sido registrado no plano em algum momento anterior: `bandoDeDados/conexao.php` hoje é só `require_once __DIR__ . '/../config/bandoDeDados/conexao.php'` — delega para a versão real, não duplica credenciais. Confirmado também que `admin/super_admin/config/database.php` e `app/Database/conexao.php` já leem do `.env` via `Config::get()`.

### A6. Cinco ações do cliente com fatal error por `require` relativo quebrado
`cliente/editar_chamado.php:3`, `excluir_chamado.php:3`, `salvar_edicao.php:3`, `fechar_chamado.php:3`, `adicionar_resposta.php:3` usam `require 'bandoDeDados/conexao.php'` (sem `../`). Confirmado via execução direta com PHP CLI a partir de `cliente/`: `Fatal error: Uncaught Error: Failed opening required 'bandoDeDados/conexao.php'`. Não existe `cliente/bandoDeDados/`.
**Sintoma real:** clicar em "Editar Chamado", "Excluir", "Salvar Edição", ou "Confirmar Resolução" (que chama `fechar_chamado.php`) resulta em tela em branco/erro fatal para qualquer cliente. Estas são ações centrais do fluxo do cliente, **totalmente quebradas hoje**.
**Status: [ ]** Pendente — trocar para `require '../bandoDeDados/conexao.php'` ou, melhor, aproveitar a correção e apontar direto para `../config/bandoDeDados/conexao.php` (a versão que lê `.env`), resolvendo esse bug e parte de A2 ao mesmo tempo nesses 5 arquivos.

### A7. `adicionar_resposta.php` também grava em coluna inexistente
`cliente/adicionar_resposta.php:84-88` — mesmo padrão de A1: `UPDATE chamados SET data_ultima_atualizacao = ...` (coluna correta é `data_atualizacao`, que já tem `ON UPDATE current_timestamp()` automático — o UPDATE é redundante mesmo depois de corrigido o nome). Hoje esse bug fica mascarado porque A6 já derruba o script antes de chegar aqui.
**Status: [ ]** Pendente — corrigir junto com A1.

### A8. Exclusão de técnico deixa chamados órfãos, sem realocação
`admin/excluir_tecnico.php:38` — `DELETE FROM tecnicos WHERE id = ?` sem tratar chamados vinculados. Confirmado no schema: não existe FK entre `chamados.tecnico_id` e `tecnicos.id`. Chamados de um técnico excluído ficam com `tecnico_id` apontando para um ID inexistente — somem da fila de não-atribuídos (`tecnico_id IS NULL` não bate) e da fila do técnico (que não existe mais). Ficam invisíveis, sem aviso.
**Status: [ ]** Pendente — antes de excluir, reatribuir ou marcar chamados do técnico como não-atribuídos explicitamente.

### A9. Chamado aberto pelo admin sempre grava autoria fixa (id=1)
`admin/abrir_chamado_admin.php:90` — `$admin_id = $_SESSION['id'] ?? 1;`. O arquivo não tem `session_start()` nem `requireAdmin()`/checagem de autenticação em nenhum lugar. Sem sessão iniciada, `$_SESSION['id']` nunca existe, então todo chamado aberto por essa tela grava `criado_por_admin = 1`, independente de qual admin (ou se algum admin) estava logado.
**Status: [ ]** Pendente — adicionar `session_start()` + `requireAdmin()` (ausência de auth aqui é também um problema de controle de acesso, não só de dado incorreto).

### A3. Tabela de backup viva dentro do banco de produção
`chamados_backup_20260121` existe como tabela real, com 1 registro duplicando o chamado id 1.
**Status: [x]** Corrigido na Fase 5 — `DROP TABLE` na migração, após confirmar por busca que nenhum código a referenciava.

### A4. Duas tabelas de histórico com propósitos sobrepostos
`historico_chamados` (alimentada por trigger automático) e `chamado_atualizacoes` (eventos livres) não são sincronizadas; código tenta gravar manualmente em `historico_chamados` mesmo com o trigger já fazendo isso — e falha no caso de A1.
**Status: [x]** Corrigido na Fase 5. `chamado_atualizacoes` mantida como está (feed de eventos livres, propósito distinto, sem conflito). `historico_chamados` deixou de depender de trigger: o trigger `registrar_historico_status` (que sempre gravava `usuario_id = tecnico_id do chamado`, incorreto quando quem mudava o status era o cliente ou um admin) foi removido; `registrarHistoricoStatus()` (`controller/historico_chamados.php`) agora é chamada explicitamente em todo ponto que muda `chamados.status`, com o `usuario_id` de quem está de fato logado. Achado extra descoberto ao testar a migração: `historico_chamados.id` não tinha `PRIMARY KEY`/`AUTO_INCREMENT` no banco local — corrigido na mesma migração (ver `docs/PLANO_DE_CORRECAO.md`, Fase 5, para a ressalva sobre produção).

### A5. `display_errors` ligado em produção em múltiplos arquivos
`login_handler.php`, `verificar_senha.php`, `handle_adicionar_usuario.php` (superadmin), `configurar_log.php`, `processa_contato.php` — todos ligam `display_errors=1` direto no código, ignorando `APP_DEBUG=false` do `.env`. Vaza mensagens de erro do MySQL, caminhos absolutos, stack traces — inclusive na tela de login do super admin.
**Status: [~]** `.htaccess` (C1) força `php_flag display_errors off` a nível de diretório como rede de segurança, mas os `ini_set()` no código continuam lá e podem sobrescrever dependendo da configuração do PHP. Pendente: remover os `ini_set('display_errors', 1)` do código.

---

## BAIXO / FUNCIONAL (bugs de UX, links e JS quebrados)

### B1. Links "Sair" e "Suporte" quebrados em `cliente/visualizar_chamado.php`
Linha 439: `href="logoff.php"` — não existe `cliente/logoff.php` (só `tecnico/logoff.php`); o padrão correto usado no resto do sistema é `../controller/logout.php`. Linha 438: `href="suporte.php"` — não existe; o arquivo real é `cliente/contato.php`.
**Status: [ ]** Pendente — dois `href` para corrigir.

### B2. Botão "Atribuir Técnico" não pré-seleciona o chamado
`admin/chamados_ativos.php:368` e `admin/visualizar_chamado.php:91,296` geram `href="atribuir_chamados.php?chamado=<id>"`, mas `admin/atribuir_chamados.php` nunca lê `$_GET['chamado']`. Comparar com `admin/gerar_ordem_servico.php:15`, que implementa corretamente o mesmo padrão — confirma que é omissão, não convenção do projeto.
**Sintoma:** admin espera cair direto no chamado ao clicar "Atribuir", mas cai na tela genérica e precisa procurar de novo.
**Status: [x]** Corrigido — `admin/atribuir_chamados.php` agora lê `$_GET['chamado']` e abre automaticamente o modal "Atribuir Técnico" pré-selecionado com esse chamado ao carregar a página (usa a função `abrirModalAtribuir()` já existente). **Limitação conhecida:** se o chamado não estiver na lista renderizada pelo filtro padrão (`status=nao_atribuido`) — por exemplo, se já foi atribuído por outra pessoa entre o clique e o carregamento — o modal abre com título vazio; título e técnico continuam corretos porque o `chamado_id` é sempre passado corretamente ao formulário de atribuição.

### B3. `tecnico/loginTecnico.php` — JavaScript quebrado por ID inexistente
Linhas 473 e 509 fazem `document.getElementById('email')`, mas o campo real do form é `id="matricula"` (linha 386). O listener de `submit` (linha 472) lança `TypeError` ao tentar submeter, anulando toda validação client-side (regex de matrícula, mínimo de senha); o autofoco no `load` (linha 508) também quebra. Falha silenciosa — só aparece no console do navegador.
**Status: [x]** Corrigido — `getElementById('email')` trocado por `getElementById('matricula')` nas duas linhas, `label for="email"` corrigido para `for="matricula"` (achado adicional, ver FE1 em `docs/AUDITORIA_FRONTEND.md`). **Achado extra descoberto ao reativar a validação:** o regex client-side de matrícula (`/^(\d{4}[A-Z]\d{4}|...)$/`) só aceitava 4 dígitos finais, rejeitando matrículas reais de 3 dígitos como `2026A001`/`2026A002` — corrigido para `\d{3,4}`, senão a correção deste bug teria bloqueado logins válidos que hoje passam só porque a validação estava quebrada.

### B4. Telas mortas/protótipos não-funcionais, acessíveis por URL direta
- `tecnico/dashboard.php` — `$tecnicoId = 1` hardcoded (não existe técnico com esse ID no dump atual); modal de status usa valores capitalizados (`'Concluído'`) incompatíveis com o ENUM real. Não linkada por nenhuma tela ativa.
- `tecnico/painelTecnicoCliente.php` — tabela inteira com dados mockados em HTML fixo ("João Silva" repetido); botões "Aceitar"/"Enviar Atualização" sem handler algum. Não linkada por nenhuma tela ativa.
**Status: [ ]** Pendente — decidir entre completar ou remover; não são alcançadas pelo fluxo normal, então não é urgente, mas ficam vulneráveis/confusas se acessadas direto por URL.

---

## MÉDIO

### M1. Três cópias do portal do cliente
`cliente/`, `assets/cliente/`, `apresenta_escritorius/assets/cliente/` — idênticas hoje. Apenas `cliente/` é alcançada pelo login real (`controller/valida_login.php:139`). As outras duas são código morto do ponto de vista do fluxo, mas continuam acessíveis por URL direta, herdando todas as vulnerabilidades acima como superfície extra.
**Status: [ ]** Pendente — apagar as duas cópias não usadas depois de confirmar via log de acesso do servidor que nada aponta para elas.

### M2. Nenhum mecanismo real de exclusão/portabilidade de dados do titular
A política de privacidade (`publics/privacidade.php`, seção 9) promete os direitos da LGPD (acesso, correção, eliminação, oposição, portabilidade). Não existe nenhuma funcionalidade correspondente no código.
**Status: [ ]** Pendente — mínimo viável: processo manual documentado; ideal: funcionalidade real.

### M3. Retenção de dados sem expurgo automático
Política promete eliminação seletiva após 5 anos. Não há rotina em `cron/` cobrindo isso.
**Status: [ ]** Pendente.

### M4. Dado pessoal duplicado em `chamados`
`cliente_nome`, `cliente_email`, `cliente_telefone` são colunas próprias em `chamados`, redundantes com `clientes` (via `cliente_id`), ampliando a superfície de exposição.
**Status: [ ]** Pendente — avaliar se a duplicação é necessária (histórico caso o cliente mude os dados) ou se pode ser removida.

### M5. Log de auditoria insuficiente
`logs_sistema.acao` é string livre, sem IP, sem indicar qual registro foi acessado. Insuficiente para reconstruir escopo de um incidente (exigido pela ANPD em notificação, art. 48 LGPD).
**Status: [ ]** Pendente — adicionar IP, tipo de recurso e ID do recurso acessado.

### M6. Raiz de produção mistura projetos não relacionados
`StyleManager/` (agora majoritariamente em VPS própria, mas resquício de código ainda presente), `apresenta_myhealth/` (não lançado), `apresenta_escritorius/` (não hospedado aqui de fato), `despesas/` (ativo, mesma hospedagem), `portifolio/` (pessoal), `testeApp.cs` (C#, não utilizado) — tudo na mesma raiz.
**Status: [ ]** Pendente — fora do escopo imediato; StyleManager já migrou para VPS. Avaliar isolar `despesas/` futuramente.

### M7. Política de privacidade declara controles que o código não cumpre
Ver C9, C6 e A5 — a política promete bcrypt universal, controle de acesso por perfil, logs de auditoria robustos. Nenhuma dessas é 100% verdadeira hoje.
**Status: [ ]** Pendente — revisar política junto com jurídico à medida que os itens técnicos forem corrigidos, para manter a promessa e a realidade alinhadas.

---

## Itens investigados e descartados (não são vulnerabilidade ativa)

- **SQL injection via `{$tenantId}`** em `admin/super_admin/api/api_jwt.php:178` e `functions.php:51` — a variável vem de `$stmt->insert_id` (inteiro gerado pelo MySQL), não de input externo. Má prática, não exploração possível como está.
- **SMTP Gmail em `.env` local do StyleManager** — confirmado pelo usuário: é credencial de ambiente de teste, ativa deliberadamente para esse fim. Não faz parte do escopo de rotação de credencial de produção.

---

## Observação sobre a senha SMTP de produção (`contato@netonerd.com.br`)

Confirmado que esta credencial é usada por pelo menos dois sistemas de produção reais: NetoNerd ITSM (Hostinger) e StyleManager (VPS). Trocar a senha exige coordenação prévia — ver planejamento de correção para a sequência recomendada. Não tratado como item desta auditoria porque depende de levantamento fora do código (acesso à VPS, Zapier/automações externas).
