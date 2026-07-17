# NetoNerd ITSM — Resoluções da Auditoria

Registro do que foi **efetivamente corrigido no código**, por fase do `docs/PLANO_DE_CORRECAO.md`. Os documentos `docs/AUDITORIA_ACHADOS.md`, `docs/AUDITORIA_SEGURANCA.md`, `docs/AUDITORIA_BACKEND.md` e `docs/AUDITORIA_FRONTEND.md` continuam sendo a fonte de verdade dos achados (com status `[x]`/`[ ]`/`[~]` atualizado); este documento é um resumo de leitura rápida do que mudou, para quem não quer vasculhar os 4 documentos de achados.

Nenhuma correção registrada aqui exigiu downtime — tudo foi feito no ambiente de trabalho local, sem tocar produção diretamente.

---

# Fase 1 (2026-07-13)

---

## 1. Segurança crítica

### Backdoor de login removido
`app/Views/superadmin/auth/login_handler.php` tinha um bypass hardcoded (`matricula === 'teste' && password === 'senha'` → sessão de super admin com cargo CEO). Removido. No mesmo arquivo, removido também um fallback perigoso que aceitava login se a senha em texto puro do banco batesse literalmente com a senha digitada (`$user['senha_hash'] === $password`) — agora só `password_verify()` é aceito.

### Escalação de privilégio sem credencial prévia (cadeia S2 → C10)
`admin/processa_adicionar_tecnico.php` não tinha nenhuma verificação de sessão — qualquer pessoa podia criar um técnico com a matrícula que quisesse e, combinando com o bug de regex já documentado em C10 (matrícula no padrão `\d{4}A\d{3}` vira admin automaticamente), obter acesso administrativo total sem nunca ter tido credencial alguma. Agora exige `requireAdmin()`. **Nota:** C10 (o bug de regex em si) continua pendente — está reservado para a Fase 4, junto com a unificação das 3 implementações incompatíveis de `isAdmin()`.

### Ações destrutivas sem autenticação
- `admin/excluir_tecnico.php` tinha a checagem de admin comentada — restaurada via `requireAdmin()`.
- `cliente/atualizarChamado.php` (e as 2 cópias mortas em `assets/cliente/` e `apresenta_escritorius/assets/cliente/`) permitia que qualquer visitante alterasse status/descrição de qualquer chamado de qualquer cliente. Agora exige `requireCliente()` e filtra `AND cliente_id = ?` no UPDATE.
- `admin/limpar_tecnicos_id_zero.php` executava DELETE/UPDATE em massa via GET simples, sem autenticação. Isolado (ver seção "Arquivos removidos" abaixo).

### API de JWT do Super Admin sem autenticação
`admin/super_admin/api/bootstrap.php` e `api_jwt.php` (os dois pontos de entrada da API, cobrindo geração/revogação/listagem de tokens de tenant) não tinham nenhuma verificação. Agora exigem header `X-Api-Key` batendo com `SUPERADMIN_API_MASTER_KEY` (variável nova no `.env`), verificado com `hash_equals()`. Feito **antes** de tocar no bug separado de nome de classe (`JWTHandler` → `JWTHandlerV2`), para não reabrir a vulnerabilidade ao corrigir esse bug depois.

### SQL Injection + XSS em relatórios
`admin/relatorios.php` interpolava `$_GET['data_inicio']`/`data_fim` direto em duas queries SQL e no HTML sem escape. Agora usa `bind_param()` nas duas queries, valida o formato da data (`checkdate()`) antes de qualquer uso, e escapa o output com `htmlspecialchars()`. `admin/relatorio_tecnico.php` recebeu o mesmo tratamento de validação/escape (as queries já usavam prepared statements — o risco de SQLi ali era menor do que o documento original supunha).

### Instalador de despesas exposto
`despesas/config/.installed` não existia neste ambiente, deixando `despesas/install.php` e `processar_instalacao.php` publicamente acessíveis e vulneráveis a SQL injection (`$_POST['db_name']` interpolado em `CREATE DATABASE`). Arquivo `.installed` criado; os dois scripts movidos para `_REMOVER_DO_SERVIDOR/despesas/`.

### Upload de arquivos sem validação real
- `admin/abrir_chamado_admin.php` validava pelo `Content-Type` declarado pelo navegador (falsificável). Agora usa `finfo`/magic bytes, igual ao padrão já correto em `cliente/registra_chamado.php`.
- `uploads/.htaccess` criado, desabilitando execução de PHP em toda a árvore de uploads (`uploads/anexos/`, `uploads/chamados/`).
- As 4 instâncias do StyleManager (`apresenta_stylemenager`, `barbeariatheclub`, `barbeariaviana`, `Excellence-Barbear-House`) tinham o mesmo problema em `handle_upload_foto.php` (upload de foto de profissional) e `admin/configuracoes.php` (upload de logo do salão) — corrigidas com lista branca de extensão + `finfo`, e `.htaccess` criado em `assets/img/` de cada uma.

---

## 2. Credenciais hardcoded eliminadas

Pedido explícito do usuário no meio da sessão: **nenhuma senha, email de credencial ou secret pode ficar hardcoded no código — tudo vai para `.env`**. Levantamento e correção:

| Arquivo | O que tinha hardcoded | Correção |
|---|---|---|
| `bandoDeDados/conexao.php` | `root@localhost`, sem senha | Passou a delegar para `config/bandoDeDados/conexao.php` (já lia do `.env`) |
| `despesas/config/database.php` | Senha real de produção (`u478690921_despesas`) | Lê `DESPESAS_DB_*` do `.env` |
| `admin/super_admin/config/database.php` | `root` sem senha | Lê `SUPERADMIN_API_DB_*` do `.env` |
| `app/Views/superadmin/data_base/conection.php` | `root` sem senha | Lê `SUPERADMIN_DB_*` do `.env` |
| `app/Database/conexao.php` | `root` sem senha | Lê `APP_LEGACY_DB_*` do `.env` |
| `app/Controllers/ChamadoController_atualizar.php` | `root` sem senha + nome de banco placeholder | Código morto (nenhuma referência no projeto) — movido para `_REMOVER_DO_SERVIDOR/` |
| `config/config.php` | Senha SMTP real de produção como valor default | Default trocado para vazio; valor real só no `.env` |
| `api/v1/stylemanager/config/clientes.php` | Secret JWT hardcoded | Lê `STYLEMANAGER_JWT_SECRET` do `.env`, sem fallback |
| `api/v1/stylemanager/config/jwt.php` | Fallback de secret hardcoded | Lança exceção se `STYLEMANAGER_JWT_SECRET` não estiver definido |
| `social/api/helpers/JWT.php` | Fallback `'change_me_in_production'` | Lança exceção se `JWT_SECRET` não estiver definido |
| `StyleManager/suporte/app/Services/{GeradorPacoteService,ProvisionamentoService,SincronizacaoPlanoService}.php` + `ProvisionamentoController.php` (5 ocorrências) | Fallback `'stylemanager_key'` para a chave que cifra senhas de banco de clientes (AES-256) | Lança exceção se `APP_KEY` não estiver definido — antes operava inseguro e silencioso |

Também corrigidos, fora do escopo original de credenciais mas achados no caminho:
- **Senha de app do Gmail pessoal** em comentário morto de `app/Views/superadmin/pages/handle_adicionar_usuario.php` — arquivo mantido (é código morto comentado), mas o `ini_set('display_errors')` do mesmo arquivo foi removido; recomenda-se revogar essa senha de app no Google Account.
- **Script de debug com hash bcrypt real** (`app/Views/superadmin/auth/verificar_senha.php`), que confirmava publicamente a senha da conta `teste`/`senha` do backdoor removido — isolado em `_REMOVER_DO_SERVIDOR/`.

`.env` e `.env.example` foram atualizados com todas as novas variáveis, documentadas por seção. Uma chave forte (`SUPERADMIN_API_MASTER_KEY`) foi gerada e já está no `.env` local.

**Pendência que não é de código:** os valores migrados para o `.env` local (senha do banco de `despesas`, secret do StyleManager) são os mesmos que já estavam expostos no código-fonte — precisam ser rotacionados em produção, não só movidos de lugar.

---

## 3. Destruição de sessão redundante (pedido extra do usuário)

Nova função `destruirSessaoComRedundancia()` em `controller/auth_middleware.php`, com 3 camadas: limpar `$_SESSION` + `session_destroy()`; invalidar explicitamente o cookie de sessão no navegador (o `session_destroy()` sozinho não faz isso); checagem final para garantir que nada ficou para trás. Aplicada nos 5 pontos de logout ativos do sistema principal: `controller/logout.php`, `tecnico/logoff.php`, `app/Views/superadmin/auth/logout.php`, `assets/cliente/logoff.php`, `apresenta_escritorius/assets/cliente/logoff.php`.

---

## 4. Bugs de frontend corrigidos

- **B1** — links "Sair" e "Suporte" quebrados em `cliente/visualizar_chamado.php`, corrigidos para `../controller/logout.php` e `contato.php`.
- **B3 / FE1** — `tecnico/loginTecnico.php`: `getElementById('email')` (2 ocorrências) e `<label for="email">` corrigidos para `matricula`, que é o `id` real do campo. **Achado extra ao destravar a validação:** o regex de matrícula só aceitava 4 dígitos finais, rejeitando matrículas reais de 3 dígitos (incluindo a do próprio usuário, `2026A001`) — corrigido para aceitar `\d{3,4}`.
- **B2** — `admin/atribuir_chamados.php` agora lê `$_GET['chamado']` e abre automaticamente o modal de atribuição pré-selecionado.
- **FE3** — link morto "Esqueceu sua senha?" (apontava para arquivo inexistente) removido de `tecnico/loginTecnico.php` e `publics/login.php`.
- **FE5** — caminho relativo de `css/main.css` corrigido para `../css/main.css` em `cliente/contato.php`, `cliente/visualizar_chamado.php`, `tecnico/loginTecnico.php`, `tecnico/painelTecnicoCliente.php`.
- **FE6** — ícone de menu mobile invisível (branco sobre fundo branco) em `routes/header.php` corrigido para usar a classe `navbar-toggler-icon` — beneficia todas as páginas públicas que usam esse header, não só `atendimento.php`.
- **FE10** — `routes/header_tecnico.php` (órfão, nome quase idêntico ao arquivo real `headerTecnico.php`) movido para `_REMOVER_DO_SERVIDOR/routes/`.

---

## 5. Arquivos removidos do código ativo (movidos para `_REMOVER_DO_SERVIDOR/`)

Seguindo o padrão já estabelecido no projeto (mesmo usado para `unicle_execution/`): nada foi apagado do disco, tudo foi copiado para `_REMOVER_DO_SERVIDOR/` mantendo a estrutura de pastas, e removido do caminho ativo. **Ação pendente do usuário:** apagar essa pasta do servidor de produção via FTP/painel.

- `admin/limpar_tecnicos_id_zero.php`
- `despesas/fix_multiuser.php`
- `admin/super_admin/tests/test_connection.php`
- `admin/super_admin/config/redits.php`
- `config/bandoDeDados/criar_usuarios_teste.sql` (criava contas de admin com senha fraca conhecida, incluindo a matrícula `2026A002` que colidia com o bug de escalação de privilégio)
- `despesas/install.php` e `despesas/processar_instalacao.php`
- `app/Controllers/ChamadoController_atualizar.php` (código morto com credencial hardcoded inútil)
- `app/Views/superadmin/auth/verificar_senha.php` (script de debug com hash real)
- `routes/header_tecnico.php` (órfão)

Também gerado (não executado): `_REMOVER_DO_SERVIDOR/remover_conta_teste_2026A002.sql` — a matrícula `2026A002` era dado de teste do próprio desenvolvedor (não uma funcionária real), com o mesmo hash de senha fraco do script acima. O `.sql` traz as consultas de verificação (chamados vinculados) antes do `DELETE`, que está comentado de propósito — precisa ser rodado manualmente pelo usuário.

---

## 6. Verificação

Todos os arquivos PHP editados (cerca de 45, incluindo os 4 tenants do StyleManager) passaram em `php -l` sem erro de sintaxe antes de considerar a fase concluída. Não houve teste end-to-end em navegador nesta rodada — recomenda-se testar pelo menos o fluxo de login (cliente, técnico, admin, super admin) e logout antes de promover essas mudanças para produção, já que vários pontos de autenticação/sessão foram tocados.

---

## 7. O que NÃO foi feito nesta fase (fica para as próximas)

- **Fase 2** (ações fora do código) — **redefinida em 2026-07-13**: o lançamento em produção será feito por inteiro ao fim da atualização, não incremental. Por isso apagar pastas, trocar senha de banco e revogar a API key (de teste) em produção *agora* foi descartado como desnecessário/arriscado — essas ações ou perdem sentido com o lançamento completo, ou devem esperar até ele acontecer. Os itens do StyleManager (S10/S24/S26/S29) foram descartados por serem de um projeto separado. O único item mantido para execução imediata foi rodar `_REMOVER_DO_SERVIDOR/remover_conta_teste_2026A002.sql` contra o banco real. Ver `docs/PLANO_DE_CORRECAO.md` (Fase 2) para o detalhamento.
- **Fase 4**: corrigir C10 de verdade (regex de matrícula → coluna de role explícita) e unificar as 3 implementações de `isAdmin()`; implementar CSRF real; corrigir os XSS armazenados/refletidos restantes (S13-S18); rate limiting de login.
- **Fase 5+**: correções de schema (race condition de protocolo, `Ativo`/`status_tecnico` dessincronizados), migração de design (drawer lateral + navegação no padrão StyleManager/Escritorius, mantendo a paleta de cores do próprio NetoNerd), e a migração maior para Laravel + Vue com JWT, planejada para depois da segurança estar resolvida.

---

# Fase 3 (2026-07-13)

Bugs de fluxo quebrado em `cliente/` e `admin/` — ações que hoje resultam em fatal error ou comportamento silenciosamente errado para o usuário final.

## 1. Requires quebrados no portal do cliente (A6)

Os 5 arquivos de ação do cliente (`editar_chamado.php`, `excluir_chamado.php`, `salvar_edicao.php`, `fechar_chamado.php`, `adicionar_resposta.php`) usavam `require 'bandoDeDados/conexao.php'` — caminho relativo inexistente a partir de `cliente/`, causando fatal error ao clicar em "Editar", "Excluir", "Salvar Edição" ou "Confirmar Resolução". Corrigido para `../config/bandoDeDados/conexao.php` (a versão que lê `.env`, resolvendo também parte de A2 nesses arquivos). Ao mesmo tempo, a checagem de sessão manual e inconsistente de cada arquivo (`die()` num, `header()+exit()` noutro, nenhum checando o *tipo* de usuário) foi trocada por `requireCliente()` do middleware — mais forte porque garante tipo `cliente`, não só que existe alguma sessão, consistente com a correção já feita em `cliente/atualizarChamado.php` na Fase 1.

## 2. Status "fechado" inexistente no ENUM (A1, A7, BE10)

`fechar_chamado.php` tentava gravar `status = 'fechado'`, valor que não existe no ENUM real de `chamados.status`. Resolvido com uma migration isolada (`config/bandoDeDados/migracao_fase3_status_fechado.sql`) que adiciona `'fechado'` ao ENUM de `chamados.status` e de `historico_chamados.status_anterior`/`status_novo` — decisão do usuário de manter a distinção semântica entre "resolvido" (técnico terminou) e "fechado" (cliente confirmou), em vez de reaproveitar `'resolvido'` como estado final. **Ação pendente do usuário:** rodar essa migration contra o banco real (testar em cópia local antes, é mudança de schema).

A coluna inexistente `data_ultima_atualizacao` (a real é `data_atualizacao`, com `ON UPDATE current_timestamp()` automático) foi removida de `fechar_chamado.php` (redundante ali). Em `adicionar_resposta.php`, foi trocada por um `UPDATE chamados SET data_atualizacao = CURRENT_TIMESTAMP` explícito em vez de simplesmente removida — decisão do usuário, porque sem esse UPDATE o timestamp do chamado nunca refletiria a resposta do cliente (a resposta é inserida em `respostas_chamado`, tabela separada, então o gatilho automático da coluna não dispararia).

## 3. Transações ausentes (BE1, BE5, BE8)

`fechar_chamado.php` e `adicionar_resposta.php` agora envolvem seus UPDATE/INSERT em `begin_transaction()`/`commit()`/`rollback()`, seguindo o padrão já usado em `tecnico/processar_chamado.php` e `admin/processar_atribuicao.php` (BE5).

A race condition na geração de protocolo (`SELECT MAX(protocolo)` seguido de `INSERT` sem lock, permitindo protocolo duplicado sob concorrência) foi corrigida com transação nas **2 cópias reais**: `cliente/registra_chamado.php` e `admin/abrir_chamado_admin.php` (que também ganhou `requireAdmin()` — antes só tinha `session_start()`, sem checar tipo de usuário, resolvendo A9 e a autoria fixa `criado_por_admin = 1`). As outras 3 cópias (`app/Views/auth/registra_chamado.php`, `assets/cliente/registra_chamado.php`, `apresenta_escritorius/assets/cliente/registra_chamado.php`) **não foram tocadas** — confirmado pelo usuário que são material de demonstração/vitrine para clientes/prospects, não fluxo real (consistente com M1/BE11 já documentados); destino delas fica para a Fase 6. `UNIQUE` em `chamados.protocolo` como rede de segurança adicional **não foi implementado** — é mudança de schema mais ampla, considerar na Fase 5. A função `gerarProtocolo($conn)` centralizada, sugerida no achado original, também não foi extraída — as 2 cópias corrigidas continuam com a lógica duplicada.

## 4. Chamados órfãos ao excluir técnico (A8)

`admin/excluir_tecnico.php` fazia `DELETE FROM tecnicos` sem tratar os chamados vinculados (não existe FK entre `chamados.tecnico_id` e `tecnicos.id`). Corrigido: antes do DELETE, um `UPDATE chamados SET tecnico_id = NULL WHERE tecnico_id = ?` (dentro da mesma transação já existente no arquivo) devolve os chamados do técnico excluído para a fila de não-atribuídos, em vez de ficarem com um ID inexistente, invisíveis em qualquer fila.

## 5. Dashboard sempre mostrando zero chamados abertos (BE4)

`admin/dashboard.php` comparava `status = 'Aberto'` (capitalizado) contra um ENUM todo minúsculo — nunca batia. Corrigido para `status = 'aberto'`.

## 6. categoria_id nunca populado — e mudança de produto que isso disparou (BE9, BE16)

Nenhum ponto de criação de chamado gravava `categoria_id` (só a coluna de texto livre `categoria`), o que anulava a proteção "categoria em uso, não pode excluir" em `admin/processar_categoria.php` e impedia várias telas de exibir cor/ícone de categoria (JOIN com `categorias_chamado` nunca casava).

A causa raiz foi mais profunda do que o achado original supunha: tanto `admin/abrir_chamado_admin.php` quanto `cliente/abrir_chamado.php` usavam uma lista de categorias **hardcoded no PHP** (2 níveis — categoria + subcategoria), com nomes que **não correspondiam** à tabela real `categorias_chamado` (ex: "Impressão" no código vs. "Impressora" no banco; "Segurança"/"Telefonia" no código sem contraparte, e vice-versa). Não havia mapeamento textual viável. Decisão do usuário: trocar a fonte das duas telas para vir diretamente de `categorias_chamado` (8 categorias reais, 1 nível). Isso significou:
- `admin/abrir_chamado_admin.php`: `<select name="categoria_id">` populado pela tabela; o servidor busca o `nome` correspondente ao `id` recebido e grava os dois (`categoria` texto + `categoria_id`) no INSERT, dentro da transação.
- `cliente/abrir_chamado.php`: o wizard de 2 passos (categoria visual + subcategoria em `<select>`) perdeu a etapa de subcategoria — os cards agora representam as 8 categorias reais, e a seleção grava `categoria_id` direto. Resumo da revisão (step 4) e JS de validação/seleção ajustados para não referenciar mais subcategoria.
- `cliente/registra_chamado.php`: recebe `categoria_id` do POST, valida contra a tabela, grava `categoria` (nome) + `categoria_id`, dentro de transação nova (ver item 3).

## 7. Autocomplete de busca de cliente no admin (fora do escopo original, pedido do usuário)

Durante a correção do BE9 em `admin/abrir_chamado_admin.php`, o usuário pediu para substituir o `<select>` que listava todos os clientes de uma vez por busca com autocomplete: campo de texto que, a partir de 3 letras, consulta clientes por nome (LIKE, ordem alfabética) via novo endpoint `admin/buscar_clientes.php` (protegido por `requireAdmin()`), com debounce de 300ms no JS.

## 8. Checagem de retorno de query() (BE15)

Adicionado `if ($result)` antes de `fetch_assoc()`/`fetch_all()`/`num_rows` em `admin/categorias.php`, na geração de protocolo de `admin/abrir_chamado_admin.php`, e em `admin/api_keys.php` — evita fatal error silencioso (tela em branco) se a query falhar.

## 9. Design System v2 aplicado a editar_chamado.php (FE2)

`cliente/editar_chamado.php` usava um CSS órfão (`estilos.css`, arquivo inexistente) e HTML sem framework. Migrado para o Design System v2.0 **vigente** (`nn-*`, `includes/header.php`/`footer.php`) — confirmado antes contra `docs/STATUS_MIGRACAO_DESIGN.md` e `docs/DESIGN_SYSTEM_NOTAS.md` que a paleta nova extraída do logo é a Fase 7, ainda não aplicada em nenhuma página; migrar para ela agora teria sido retrabalho. Um XSS refletido adicional (não listado no achado original) foi corrigido no caminho: `$chamado_id` vindo de `$_GET['id']` agora passa por `htmlspecialchars()` antes de ser ecoado no `<input type="hidden">`.

## 10. Links absolutos quebrados (achado adicional, fora do plano original — pedido do usuário ao ver 404 real)

Depois da correção original da Fase 3, o usuário reportou (com screenshot) que o botão "Entrar" da landing page dava 404 em `localhost/publics/login.php`. Investigação mapeou o padrão completo: o projeto real fica em `/NetoNerd/New_NetoNerd-main/` (via XAMPP), mas várias partes do código usavam paths absolutos hardcoded sem esse prefixo, ou com um prefixo antigo (`/New_NetoNerd/`) que nunca correspondeu a nenhum ambiente real (nem local, nem produção).

- **`publics/index.php`**: essa página é acessível de **duas formas** — via raiz do projeto (`.../New_NetoNerd-main/`, porque `index.php` da raiz faz `include_once('publics/index.php')`) e via `.../New_NetoNerd-main/publics/index.php` direto — e o HTML gerado é o mesmo nos dois casos, então nenhum link relativo fixo (nem `login.php`, nem `publics/login.php`) resolve certo nas duas situações ao mesmo tempo. A primeira correção desta fase usou `login.php` etc. (relativo simples), o que quebrava especificamente no acesso via raiz (reportado pelo usuário com screenshot: `localhost/publics/login.php` 404, porque o navegador resolvia a partir de `localhost/`, não de `localhost/NetoNerd/New_NetoNerd-main/`). Correção definitiva: bloco PHP no topo do arquivo calcula `$_indexBase` dinamicamente a partir de `$_SERVER['DOCUMENT_ROOT']` (mesmo padrão de `routes/footer.php`), e **todos** os 20 `href`/`src` do arquivo que apontavam para dentro do projeto (12 imagens/CSS com `../` quebrado, 8 links de navegação/produtos) passaram a usar `<?= $_indexBase ?>/...` — inclui até o logo da navbar, que estava silenciosamente quebrado (ícone de imagem ausente) desde antes desta fase, visível no mesmo screenshot que o usuário enviou.
- **`includes/header_leading_page.php`**: tinha `href="/assets/css/netonerd-global.css"` quebrado (CSS nunca carregava). Confirmado por busca exaustiva que **nenhum arquivo do projeto inclui esse header** — é código morto, mesmo padrão do `routes/header_tecnico.php` já isolado na Fase 1. Movido para `_REMOVER_DO_SERVIDOR/includes/` em vez de corrigido.
- **`includes/header.php`** (o header real, usado por praticamente toda página logada de admin/cliente/técnico): o link do logo/nome do sistema usava 3 paths absolutos sem prefixo (`/admin/dashboard.php`, `/tecnico/paineltecnico.php`, `/cliente/home.php`) — quebrava em qualquer instalação fora da raiz do domínio. Corrigido calculando a base do projeto dinamicamente a partir de `$_SERVER['DOCUMENT_ROOT']` (mesmo padrão já usado em `routes/footer.php`, que nunca teve esse bug).
- **`controller/auth_middleware.php`**: os 10 `header('Location: ...')` de `requireAuth()`, `requireAdmin()`, `requireTecnico()`, `requireCliente()` e `logout()` usavam `/New_NetoNerd/...` hardcoded — path que não bate nem com produção (`/public_html`) nem com o ambiente local real (`/NetoNerd/New_NetoNerd-main`). Decisão do usuário: em vez de hardcode, criada a função `basePath()` que lê `APP_URL` do `.env` via `Config::get()` e extrai só o path com `parse_url()` — resolve automaticamente em qualquer ambiente sem precisar editar código ao trocar de dev para produção.

**Infraestrutura de ambiente criada para viabilizar isso e testes futuros:** o `.env` único que antes misturava valores de produção (banco, `APP_URL`) com módulos locais foi separado em dois arquivos — `.env` (agora com banco/URL de desenvolvimento local: `netonerd`/`root`/sem senha, `http://localhost/NetoNerd/New_NetoNerd-main`) e `.env.production` (snapshot fiel dos valores reais de produção, para promover no lançamento). `.env.production` adicionado ao `.gitignore` (não estava coberto pelos padrões existentes, que só cobriam `.env`/`.env.local`/`.env.*.local`). `.env.example` também corrigido (tinha o mesmo path antigo `/New_NetoNerd` desatualizado). Banco local `netonerd` criado e populado a partir de `config/bandoDeDados/u478690921_netonerd.sql` (schema + dados de exemplo) para permitir testar de verdade.

## 11. Verificação

Todos os arquivos tocados na fase (17, incluindo os novos `admin/buscar_clientes.php` e os dois `.env`) passaram em `php -l` sem erro de sintaxe. **Testado ao vivo via XAMPP** (`curl` contra `localhost/NetoNerd/New_NetoNerd-main/`), diferente do restante da fase:
- Confirmado que o link antigo (`localhost/publics/login.php`) de fato retorna 404, e que uma página protegida por `requireAdmin()` (`admin/categorias.php`), acessada sem sessão, redireciona corretamente para `/NetoNerd/New_NetoNerd-main/publics/login.php?erro=nao_autenticado` (antes seria `/New_NetoNerd/...`, 404) — cadeia completa validada (redirect → página de destino carrega, 200).
- Todos os 20 `href`/`src` corrigidos em `publics/index.php` testados via `curl` **nos dois modos de acesso** (raiz e `publics/index.php` direto) — HTML gerado idêntico nos dois casos, e todas as 14 URLs finais (CSS, 5 imagens de logo/foto/produtos, 6 páginas de navegação, 2 links de produto externo, 1 link para `despesas/`) retornam 200 (ou 301 esperado para `despesas` sem barra final).
- Migration de status `'fechado'` já rodou contra o banco local nesse processo (banco `netonerd` criado e populado a partir de `config/bandoDeDados/u478690921_netonerd.sql` para viabilizar os testes).
- Confirmado que as outras 8 páginas de `publics/` que usam `../` (login, contato, produtos, etc.) **não têm o mesmo bug** — só `index.php` tem acesso duplo via o `include` do `index.php` da raiz; as demais são sempre acessadas com `publics/` explícito na URL, então `../` resolve sem ambiguidade nelas.

**Ainda não testado manualmente em navegador** (só via `curl`, que confirma status HTTP e header `Location`, mas não renderização/JS): abrir chamado (cliente e admin, com a nova seleção de categoria e autocomplete), editar/excluir/fechar chamado, adicionar resposta, excluir técnico com chamados atribuídos — todos exigem login real (sessão), que o `curl` não simula.

## 12. O que NÃO foi feito nesta fase

- `UNIQUE` em `chamados.protocolo` (rede de segurança adicional para BE1/BE8) — mudança de schema, fica para a Fase 5.
- Função `gerarProtocolo($conn)` centralizada — as 2 cópias corrigidas continuam com lógica duplicada.
- As 3 cópias de vitrine de `registra_chamado.php` (`app/`, `assets/cliente/`, `apresenta_escritorius/assets/cliente/`) não receberam a correção de race condition — destino delas é decisão da Fase 6 (M1/BE11).
- CSRF real, XSS restantes (S13-S18) e rate limiting de login continuam pendentes — Fase 4 (C10 já corrigido, ver seção Fase 4 abaixo).
- **Antes do lançamento em produção:** `.env.production` precisa ser promovido a `.env` (ou o processo de deploy precisa gerar o `.env` de produção a partir dele) — o `.env` local não deve subir para produção, senão o sistema tenta conectar no banco/URL de desenvolvimento.

Ver `docs/PLANO_DE_CORRECAO.md` para o detalhamento fase a fase.

---

# Fase 4 (2026-07-13, em andamento) — C10 + S2 + BE2

Item de prioridade máxima da fase: fechar a escalação de privilégio por regex de matrícula.

## 1. Separação em tabela `admins` (C10 + BE2)

Plano original previa uma coluna/flag de role em `tecnicos`. **Decisão do usuário durante a execução:** separar fisicamente em tabela `admins` própria, distinta de `tecnicos` — a convenção de matrícula (`F`=funcionário/técnico, `A`=admin) é intencional e correta, o problema era ela ser a *única* fonte de verdade, não a convenção em si.

`config/bandoDeDados/migracao_fase4_tabela_admins.sql`:
- Cria `admins` (id, nome, email, matricula, senha_hash, Ativo, created_at).
- Migra os 2 registros que hoje batem no regex antigo (`2026A001`, `2026A002`), **preservando o `id` original** — dados reais em `chamados.criado_por_admin`, `chamado_atribuicoes.admin_id` e `ordens_servico.tecnico_id`/`created_by` já apontavam para esses IDs, e nenhuma dessas colunas tem FK formal (exceto uma, ver abaixo), então preservar o ID evita órfãos silenciosos.
- Remove os mesmos registros de `tecnicos`.
- Dropa a tabela `usuarios` (vestígio do schema antigo — tinha exatamente a forma `tipo ENUM('tecnico','admin')` que o plano original cogitava, mas não era lida em nenhum ponto do fluxo real, só num `SELECT` morto em `valida_loginTecnico.php` para decidir se gravava log com FK).
- Remove 2 FKs reais que existiam em `ordens_servico` (`tecnico_id` e `created_by` → `tecnicos.id`) — descobertas ao rodar a migration (bloquearam o primeiro `DELETE`). Dados reais mostravam admin nesses campos em várias linhas, ou seja, a FK nunca refletiu a regra de negócio real. Viram int solto, como os demais campos análogos.

Testado em banco local (`netonerd`): migration aplicada com sucesso, IDs 2 e 3 em `admins`, `tecnicos` só com técnicos reais (IDs 4 e 5).

## 2. Login não decide mais o cargo por regex

`controller/valida_loginTecnico.php`: função `isAdmin($matricula)` removida. A busca de dados agora tenta `admins` primeiro, depois `tecnicos`; o `$tipo` da sessão vem de qual tabela encontrou o registro, não de um padrão de string. A checagem morta contra `usuarios` (proteção de FK que não existia mais na prática) foi removida junto — `logs_sistema.usuario_id` não tem FK real, então grava direto.

`config/config_systens/auth_system.php` (classe `AuthSystem`, não usada em produção hoje — reservada para o fix de S12/rate limiting): mesmo padrão aplicado em `loginTecnico()` (tenta `admins`, depois `tecnicos`) e em `validarSessao()` (`LEFT JOIN` separado para `tecnicos`/`admins` conforme `tipo_usuario`, em vez de um `JOIN tecnicos` único que cobria os dois papéis por coincidência de ID).

`admin/processar_atribuicao.php`: removida a checagem "não atribuir a admin" via regex — hoje é redundante, `tecnicos` não contém mais admins.

## 3. Filtro que escondia "técnicos fantasma" removido

`admin/atribuir_chamados.php`: as 2 queries que filtravam `matricula LIKE '%F%'` (para esconder os técnicos que na verdade eram admins) voltaram a listar todos os técnicos ativos sem filtro — não é mais necessário, `tecnicos` já não contém admin nenhum.

## 4. Cadastro de admin (novo, exigido pela separação de tabelas)

Como só existe hoje cadastro de técnico (`admin/processa_adicionar_tecnico.php`, já exige `requireAdmin()` desde a Fase 1/S2), foi criado o caminho equivalente para admin: modal "Adicionar Admin" em `admin/dashboard.php` + `admin/processa_adicionar_admin.php` (novo arquivo), com a mesma proteção (`requireAdmin()`, `password_hash()`, `bind_param()`, checagem de email/matrícula duplicados). Confirma o modelo de negócio do usuário: **só admin cadastra técnico ou admin** — não existe cadastro público de nenhum dos dois.

## 5. Correção de leituras que assumiam `tecnico_id`/`created_by`/`admin_id` só em `tecnicos`

Como a separação de tabelas quebra qualquer `JOIN tecnicos` que dependia de admins estarem misturados ali, foi necessário varrer o código por esse padrão. Achados e correções:

- **`INNER JOIN tecnicos`** (o caso mais grave — faria a linha inteira sumir da listagem se `tecnico_id`/`created_by` apontasse pra admin): `admin/visualizar_ordem_servico.php`, `admin/imprimir_ordem_servico.php`. Convertidos para `LEFT JOIN tecnicos ... LEFT JOIN admins ... COALESCE(...)`.
- **`LEFT JOIN tecnicos` sem equivalente para admins** (não some a linha, mas o nome do responsável aparece em branco): `admin/listar_ordens_servico.php`, `admin/visualizar_chamado.php`, `admin/chamados_ativos.php`, `admin/atribuir_chamados.php`, `admin/gerar_ordem_servico.php`, `cliente/visualizar_chamado.php` (2 queries — chamado e respostas), `cliente/detalhe_chamado.php`. Todos ganharam o `LEFT JOIN admins` + `COALESCE` equivalente.
- Confirmado com dado real via `curl` local: OS com `tecnico_id` apontando para o admin (id 3) — que antes da correção sumiria (`INNER JOIN`) ou apareceria com nome em branco (`LEFT JOIN` sem admins) — agora mostra "Rondineli da Silva Oliveira Moreira / Admin" corretamente.
- **Não alterados, por não precisarem**: `admin/relatorios.php` e `admin/relatorio_tecnico.php` (relatório é intencionalmente só de técnicos, `FROM tecnicos` é a fonte certa); `tecnico/detalhes_chamado.php` (filtra por `tecnico_id` da própria sessão do técnico logado, que só existe em `tecnicos`); as cópias de vitrine (`app/`, `assets/cliente/`, `apresenta_escritorius/assets/cliente/`) e código já isolado em `_REMOVER_DO_SERVIDOR/`/`unicle_execution/`.

## 6. Verificação

Todos os arquivos tocados passaram em `php -l` sem erro de sintaxe. Testado via `curl` contra o Apache local (`http://localhost/NetoNerd/New_NetoNerd-main`), com senha de teste temporária local (não afeta produção):
- Login com matrícula de admin real (`2026A001`) → sessão `tipo=admin`, acesso a `admin/dashboard.php` retorna 200.
- Login com matrícula de técnico real (`2026F001`) → sessão `tipo=tecnico`, redireciona para `paineltecnico.php`; tentativa de acessar `admin/dashboard.php` é barrada (302 para `index.php`).
- **Teste decisivo do C10**: login com matrícula forjada no padrão antigo mas não cadastrada em lugar nenhum (`9999A999`) → `credenciais_invalidas`, não vira admin. Confirma que a escalação de privilégio original não é mais possível.
- Cadastro de admin sem sessão → barrado (redirect para login); com sessão de admin → sucesso.
- Fluxo geral do admin (`atribuir_chamados.php`, `visualizar_chamado.php`, `chamados_ativos.php`, `dashboard.php`) retorna 200 sem fatal error após todas as mudanças de JOIN.

## 7. CSRF real em todo o fluxo principal (C4 + C5)

Levantamento completo feito antes de codificar (via agente de busca dedicado): confirmado que `csrf_token` só existia gerado em `valida_login.php`/`valida_loginTecnico.php`, nunca validado em lugar nenhum — 27 forms POST reais no fluxo principal (`cliente/`, `admin/`, `tecnico/`, `controller/`, `publics/`) sem nenhuma proteção, mapeados para ~19 handlers únicos.

### Função central

`controller/auth_middleware.php` ganhou 4 funções novas:
- `generateCsrfToken()` — retorna `$_SESSION['csrf_token']`, gerando com `bin2hex(random_bytes(32))` se ainda não existir. Funciona tanto para sessão autenticada quanto anônima (essencial para os forms públicos de contato/cadastro, que não têm login prévio).
- `csrfField()` — retorna o `<input type="hidden" name="csrf_token" ...>` pronto, escapado com `htmlspecialchars()`.
- `isValidCsrfToken()` — compara `$_POST['csrf_token']`/`$_GET['csrf_token']` contra a sessão via `hash_equals()` (resistente a timing attack).
- `requireCsrfToken()` — chama a validação acima; se falhar, loga a tentativa (usuário/URI/IP) e responde 403 com `die()`.

### Padrão aplicado

Em cada handler POST: `requireCsrfToken()` logo após a checagem `REQUEST_METHOD === 'POST'` (ou logo após `requireAdmin()`/`requireCliente()`/`requireTecnico()`, nos que já centralizavam a checagem de método). Em cada form: `<?php echo csrfField(); ?>` logo após a tag `<form>`.

Dois arquivos legados usavam `controller/validador_acesso.php` (que só faz `session_start()` + checagem de `$_SESSION['autenticado']`, sem incluir `auth_middleware.php`) em vez do padrão novo — precisaram ganhar `require_once '../controller/auth_middleware.php'` adicional para ter `csrfField()` disponível: `cliente/abrir_chamado.php`, `cliente/visualizar_chamado.php`, `cliente/minha_conta.php`. Mesma coisa em `publics/contato.php`, `publics/cadastro.php` e `controller/processa_cadastro.php` (não tinham auth alguma antes, por serem públicos).

### Cobertura por pasta

- **cliente/** (6 forms, 6 handlers): `abrir_chamado.php`→`registra_chamado.php`, `visualizar_chamado.php`→`adicionar_resposta.php` (2 queries), `editar_chamado.php`→`salvar_edicao.php`, `minha_conta.php` (2 forms self-post: dados e senha), `atualizarChamado.php` (self-post, confirmado sem uso no fluxo ativo hoje — só linkado por cópias mortas e por `tecnico/dashboard.php`, também morto — mas protegido por consistência/baixo custo).
- **admin/** (14 forms, 9 handlers): `dashboard.php` (modais de adicionar técnico e adicionar admin), `atribuir_chamados.php`→`processar_atribuicao.php`, `gerar_ordem_servico.php`→`processar_ordem_servico.php`, `visualizar_ordem_servico.php` (2 forms: alterar status e excluir — **achado à parte**: `admin/excluir_ordem_servico.php` não faz `DELETE`, contém a mesma lógica de `UPDATE status` de `atualizar_status_os.php`, bug de nomenclatura pré-existente não corrigido aqui, fora do escopo de C4/C5), `categorias.php` (criar + excluir; achado à parte: o link "editar" da lista é GET puro mas o `case 'editar'` do handler só lê de `$_POST`, então já era inofensivo/quebrado antes desta correção — não mexido), `licencas.php` (gerar + excluir), `api_keys.php` (criar + testar conexão + ativar + desativar + excluir — 5 forms), `configura.php` (self-post), `editar_tecnico.php` (self-post), `abrir_chamado_admin.php` (self-post).
- **tecnico/** (6 forms, 2 handlers): `meus_chamados.php` (4 forms: iniciar, pausar, retomar, atualizar) e `detalhes_chamado.php` (2 forms: iniciar, atualizar) → ambos usam `processar_chamado.php`; `resolver_chamado.php`→`processar_resolucao.php`.
- **publics/controller** (3 forms, 3 handlers, sessão anônima): `contato.php`→`processa_contato.php`, `cadastro.php`→`controller/processa_cadastro.php` (ambos sem login prévio — o token de sessão funciona igual, só precisou incluir `auth_middleware.php` para ter `session_start()` + as funções disponíveis nesses arquivos que antes não tinham auth nenhuma). Login (`valida_login.php`/`valida_loginTecnico.php`) não recebeu CSRF — é o ponto que *gera* o token, não se aplica.

### C5 — GET destrutivo virou POST+CSRF

Além de `cliente/excluir_chamado.php` (achado original de C5, confirmado **órfão** — sem link algum no fluxo ativo hoje, mas continuava acessível por URL direta), o levantamento achou mais 2 ações destrutivas com o mesmo padrão de GET simples sem proteção, corrigidas pela mesma lógica:
- `cliente/fechar_chamado.php`: o botão "Confirmar Resolução" em `visualizar_chamado.php` disparava `window.location.href = 'fechar_chamado.php?id=...'` via JS. Trocado por um `<form method="POST">` com `csrfField()` oculto, com o `confirm()` do JS agora chamando `form.submit()` em vez de navegar.
- `admin/excluir_tecnico.php`: o link "Excluir" em `admin/dashboard.php` virou um mini-`<form>` POST com token, mantendo o `confirm()` via `onsubmit`.

Ambos os handlers passaram a exigir `REQUEST_METHOD === 'POST'` antes de ler o `id` (agora de `$_POST`, não mais `$_GET`) e chamam `requireCsrfToken()`.

### O que ficou fora

- StyleManager (S25 — CSRF via GET em `manage_recommendations.php`, 4 tenants) é item próprio da Fase 4, não tratado nesta rodada.
- As 2 cópias mortas de `cliente/` (`assets/cliente/`, `apresenta_escritorius/assets/cliente/`) não receberam CSRF — mesma decisão já registrada para elas em BE1/BE8 (Fase 3): destino é decisão da Fase 6 (M1).
- `admin/buscar_cliente_ajax.php`/`admin/buscar_clientes.php` (endpoints AJAX chamados via `fetch()`, não formulário HTML) não receberam CSRF — são apenas leitura (`SELECT`), sem efeito destrutivo, risco baixo.

### Verificação

Todos os arquivos tocados passaram em `php -l`. Testado via `curl` contra o Apache local:
- POST sem `csrf_token` em `admin/processar_atribuicao.php` (admin logado) → **403**.
- POST com `csrf_token` forjado (string aleatória) → **403**.
- POST com `csrf_token` real, extraído via `curl` da página que renderiza o form → sucesso (redirect esperado).
- Técnico logado, POST sem token em `tecnico/processar_chamado.php` → **403**.
- `GET cliente/excluir_chamado.php?id=999` (cliente logado) → "Método inválido" (não executa mais o DELETE).
- Form público `publics/contato.php`, sem sessão prévia → token aparece corretamente no HTML renderizado (confirma que sessão anônima funciona).
- Fluxo legítimo completo (`cliente/minha_conta.php`, atualização de dados com token real extraído da página) → sucesso, dado persistido corretamente.

## 8. Rate limiting real de login (S12)

Decisão do usuário: em vez de migrar `valida_login.php`/`valida_loginTecnico.php` para usar a classe `AuthSystem` inteira (reescreveria sessão/hash/redirects — escopo maior que o necessário), foram extraídas 3 funções reutilizáveis para `controller/auth_middleware.php`, reaproveitando a tabela `login_attempts` que a classe já criava mas nunca era chamada:

- `garantirTabelaLoginAttempts($conn)` — `CREATE TABLE IF NOT EXISTS`, idempotente.
- `isLoginBloqueado($conn, $identificador, $tipo_usuario, $max_tentativas = 5, $tempo_bloqueio_segundos = 900)` — conta tentativas malsucedidas por identificador **OU** IP (o que faz o descarte de cookie/sessão não adiantar nada para o atacante, ao contrário do contador antigo em `$_SESSION`).
- `registrarTentativaLogin($conn, $identificador, $tipo_usuario, $sucesso)` — grava cada tentativa.

Aplicadas em `controller/valida_login.php` (cliente — não tinha proteção nenhuma antes) e `controller/valida_loginTecnico.php` (técnico/admin — perdeu `verificarBloqueio()`/`registrarTentativa()` baseadas em `$_SESSION`). O redirect de bloqueio do login de técnico usava `?erro=bloqueado&tempo=X`, mas `tecnico/loginTecnico.php` só lê `$_GET['msg']` — bug de nomenclatura pré-existente que fazia a mensagem de bloqueio nunca aparecer; corrigido para `?msg=bloqueado&tempo=X` (mesmo padrão já usado em `publics/login.php`), já que a correção do rate limiting em si expunha esse bug adjacente.

**Achado durante o teste, não previsto no achado original:** a primeira versão calculava a janela de bloqueio com `date('Y-m-d H:i:s', time() - $tempo_bloqueio_segundos)` no PHP, mas o PHP e o MySQL deste ambiente rodam em fusos horários diferentes (~5h de diferença: PHP adiantado). Isso fazia a janela de tempo calculada ficar no futuro em relação aos timestamps que o MySQL gravava com `CURRENT_TIMESTAMP`, e o bloqueio nunca disparava mesmo com tentativas registradas corretamente no banco. Corrigido calculando a janela inteiramente no SQL (`tentativa_data > (NOW() - INTERVAL ? SECOND)`), eliminando a dependência de sincronismo de relógio entre os dois serviços — vale conferir se produção tem o mesmo tipo de divergência antes do lançamento.

Testado via `curl`: 5 tentativas de login com senha errada, cada uma descartando o cookie de sessão (simulando o padrão de brute force que o achado original descreve), bloqueiam a 6ª tentativa mesmo com a senha correta — tanto no login de técnico (`2026F001`) quanto no de cliente (`teste@netoteste.com`). Tabela de teste limpa (`DELETE FROM login_attempts`) ao final.

## 9. XSS armazenados/refletidos (S13-S18)

- **S13** (super admin, `app/Views/superadmin/dashboard.php`): `cleanVal()` só trocava falsy por `''`, não escapava HTML. Corrigida para escapar via DOM (`createElement('div').textContent = val` → `.innerHTML`), usada nos modais "Detalhes" e "Editar" que montam HTML via `innerHTML`. Achado extra: `data-cliente='<?php echo json_encode($cliente); ?>'` quebrava com aspas simples no dado do cliente — envolvido em `htmlspecialchars(..., ENT_QUOTES)`.
- **S14** (StyleManager, 4 tenants: `barbeariatheclub`, `barbeariaviana`, `apresenta_stylemenager`, `Excellence-Barbear-House`): mesmo padrão do S13 (`escapeHtml()` via DOM) aplicado à busca de cliente em `agendar_centralizado.php`. O `onclick='selecionarCliente(${JSON.stringify(cliente)})'` inline — que quebrava com aspas simples no nome — foi eliminado: a lista de resultados passou a ser montada com `createElement`/`appendChild` + `addEventListener`, não mais serializando o objeto inteiro dentro de um atributo HTML.
- **S15** (`admin/gerar_ordem_servico.php`): `htmlspecialchars()` aplicado a `cliente_nome`, `cliente_telefone`, `cliente_email` e `descricao`/`problema_relatado`, que estavam sem escape enquanto o protocolo no mesmo arquivo já era escapado corretamente (confirma que era omissão pontual, não padrão do arquivo).
- **S16** (e-mails HTML): `publics/processa_contato.php` e `admin/processar_licenca.php` — variáveis `_html` separadas das originais (usadas no `INSERT`/lógica de negócio sem escape) para não afetar o dado gravado, escapadas com `htmlspecialchars()` (mensagem também com `nl2br()`) só no ponto de montagem do corpo do e-mail.
- **S17** e **S18**: já estavam corrigidos em fases anteriores (Fase 1 e Fase 3, respectivamente) — confirmado lendo o código atual antes de mexer, nenhuma ação necessária.

Todos os arquivos tocados passaram em `php -l` (o `.php` das 4 cópias StyleManager também, mesmo a mudança sendo em JS embutido).

## 10. CORS allowlist, vazamento de erro, cookie seguro, JWT via query string (S19-S22)

- **S19** (`social/api/config/cors.php` — API separada do NetoNerd, projeto "agenda_creche"): trocado o padrão de refletir qualquer `Origin` com `Access-Control-Allow-Credentials: true` (antipadrão que anula a proteção do CORS) por allowlist explícita via `APP_CORS_ORIGINS` no `.env` (lista separada por vírgula) — origem fora da lista não recebe o header `Allow-Origin` nem `Allow-Credentials`. `social/api/.env.example` documentado com a nova variável. Apps mobile (Bearer token, sem `Origin`) continuam funcionando normalmente.
- **S20** (`admin/dashboard.php`): `echo "Erro na consulta: " . $conn->error` trocado por `error_log()` + mensagem genérica ao usuário.
- **S21** (cookie de sessão): ver detalhamento completo abaixo — foi o item que mais se expandiu além do achado original.
- **S22** (`admin/super_admin/JWTMiddleware.php`): aceite de token via `$_GET['token']` agora exige `isDevelopmentEnvironment()` (checa `APP_ENV` via `getenv()`, não depende de nenhuma classe `Config` específica do NetoNerd, já que o middleware é documentado para reuso em outros projetos como MyHealth/BarberShop).

### S21 em detalhe — cookie de sessão seguro

`configurarCookieSessaoSegura()` (nova, em `controller/auth_middleware.php`) chama `session_set_cookie_params()` com `httponly`/`secure` lidos de `SESSION_HTTPONLY`/`SESSION_SECURE` do `.env` e `samesite=Lax`, sempre antes do `session_start()` central do middleware.

**Achado mais amplo que o item original, descoberto ao investigar por que a correção não aparecia em todo lugar:** um agente de busca dedicado confirmou que **30 arquivos** do fluxo principal (a maioria de `admin/`, mais `cliente/detalhe_chamado.php` e 6 de `tecnico/`) chamavam `session_start()` **próprio** antes de incluir `auth_middleware.php` — a sessão já nascia com os parâmetros padrão do PHP antes do middleware ter qualquer chance de configurá-la (PHP ignora `session_set_cookie_params()` chamado depois que a sessão já iniciou). Corrigido removendo o `session_start()` redundante desses 30 arquivos, com o middleware assumindo essa responsabilidade sozinho. Casos à parte:
- `controller/valida_login.php` e `valida_loginTecnico.php` chamavam `session_start()` **antes até de incluir** o `auth_middleware.php` — corrigido invertendo a ordem dos `require_once`.
- `controller/validador_acesso.php` (padrão legado usado por vários arquivos de `cliente/`) fazia seu próprio `session_start()` sem nunca incluir o middleware — passou a `require_once` o middleware em vez de chamar `session_start()` diretamente.
- `admin/dashboard.php` não incluía `auth_middleware.php` em lugar nenhum, e checava admin via `if (!isset($_SESSION['tipo_usuario']) || ...)` direto, redirecionando para `../index.php` sem indicar o motivo — trocado por `requireAdmin()`, como todo o resto do sistema (também resolve a inconsistência de controle de acesso que esse arquivo específico tinha desde antes desta fase).

Testado via `curl`: `Set-Cookie` traz `HttpOnly; SameSite=Lax` em login (cliente e técnico), `admin/dashboard.php`, `admin/chamados_ativos.php`, `tecnico/meus_chamados.php`, tanto sem sessão (redirect correto para login) quanto autenticado (200 em todas as rotas testadas). `Secure` não aparece em ambiente local por ser HTTP puro (`SESSION_SECURE=false` no `.env` de dev) — esperado ativar automaticamente em produção com HTTPS via `SESSION_SECURE=true`.

## 11. CSRF via GET e debug público no StyleManager (S25, S27) — e remoção do StyleManager do repositório

**S25** foi corrigido nas 4 cópias de `StyleManager/*/admin/manage_recommendations.php` (`barbeariatheclub`, `barbeariaviana`, `apresenta_stylemenager`, `Excellence-Barbear-House`): as ações "Aprovar"/"Rejeitar" avaliação, hoje acionadas por link `<a href="?aprovar=...">` (GET, sem token), viraram forms POST usando `gerar_csrf_token()`/`verificar_csrf_token()` que já existiam em `includes/auth.php` desse projeto — não precisou criar nada novo, só não estava sendo usado nesse arquivo específico.

**S27** (`test_horarios.php`/`diagnostico.php` sem autenticação, expondo estrutura de banco) foi levado para decisão do usuário antes de codificar. Resposta: o usuário **removeu a pasta `StyleManager/` inteira do repositório** logo em seguida — os 4 tenants já haviam migrado para VPS própria e não fazia sentido esse código continuar dentro do repositório de trabalho do NetoNerd. Confirmado que `StyleManager/` nunca esteve no histórico do git deste repositório (não aparece em `git log`, não estava no `.gitignore`) — era uma pasta local, a remoção não deixou resíduo a limpar.

**Efeito retroativo:** S14 e S25 (achados do StyleManager já corrigidos nesta mesma sessão, antes da remoção) ficam registrados aqui como histórico do trabalho feito, mas o código a que se referem não existe mais neste projeto. S27 nunca chegou a ser corrigido no código — deixa de ser aplicável junto com a remoção da pasta.

## 12. Fase 4 concluída

Todos os itens da Fase 4 (C10, S2, BE2, C4, C5, S12 a S22, S25, S27) estão corrigidos ou não aplicáveis. Arquivos tocados nesta fase (contando as 4 cópias StyleManager já removidas) passaram em `php -l` sem erro antes de cada commit de trabalho.

**Pendências que ficam para o lançamento:**
- A migration `migracao_fase4_tabela_admins.sql` foi testada só em banco local — confirmar que o schema de produção tem os mesmos dados/estrutura assumidos aqui (em especial a FK real encontrada em `ordens_servico`, que pode ou não existir da mesma forma em produção).
- Confirmar se o PHP e o MySQL de produção têm o mesmo fuso horário configurado — a divergência encontrada aqui (S12) pode ou não existir lá, mas os testes locais não verificam isso.
- Confirmar `SESSION_SECURE=true` no `.env` de produção (S21) antes do lançamento, já que produção roda HTTPS — no `.env` local ficou `false` de propósito (ambiente HTTP).

# Fase 5 (2026-07-15) — Banco de dados: schema e conexão

## 1. A2 já estava resolvido

Antes de tocar em qualquer schema, investiguei o estado real da divergência de conexão apontada em A2 (106 vs 68 arquivos). `bandoDeDados/conexao.php` não é mais uma segunda conexão hardcoded — hoje é só `require_once __DIR__ . '/../config/bandoDeDados/conexao.php'`, delegando para a versão real que lê do `.env`. `admin/super_admin/config/database.php` e `app/Database/conexao.php` (árvore morta) também já leem do `.env`. Não havia mais nada a corrigir aqui — só atualizar o plano, que não registrava essa correção em nenhuma fase anterior.

## 2. A3 — tabela de backup

`chamados_backup_20260121` dropada via migração, depois de confirmar por busca (`grep` em todo o projeto) que nenhum arquivo PHP a referenciava — só é criada por `migracao_sistema_chamados.sql` como backup pontual, já cumprido.

## 3. A4 — trigger de histórico substituído por gravação explícita

O trigger `registrar_historico_status` (`AFTER UPDATE ON chamados`) sempre gravava `usuario_id = NEW.tecnico_id`, mesmo quando quem mudou o status não era o técnico do chamado — caso claro em `cliente/fechar_chamado.php` (cliente fecha o próprio chamado) e `admin/processar_ordem_servico.php` (admin abre OS a partir de um chamado). Isso também causava duplicação de linha nos 2 pontos que já tinham INSERT manual (`cliente/fechar_chamado.php`, `tecnico/processar_resolucao.php`).

Decisão, alinhada com o usuário: em vez de manter o trigger como rede de segurança, ele foi removido e substituído por uma função central `registrarHistoricoStatus($conn, $chamado_id, $usuario_id, $status_anterior, $status_novo, $comentario)` em `controller/historico_chamados.php`, chamada explicitamente em todo ponto que muda `chamados.status`:
- `tecnico/processar_chamado.php` — ações `iniciar`, `pausar`, `retomar`, e a transição condicional dentro de `atualizar` (grava `$tecnico_id` da sessão).
- `tecnico/processar_resolucao.php` — já tinha INSERT manual (com `$tecnico_id` correto); trocado pela função central, sem duplicar mais.
- `cliente/fechar_chamado.php` — já tinha INSERT manual (com `$usuario_id` do cliente); trocado pela função central.
- `cliente/salvar_edicao.php` — **não tinha registro nenhum antes** (dependia só do trigger, que gravaria o técnico do chamado mesmo sendo o cliente quem editou); agora busca o status atual antes do UPDATE e registra com o `usuario_id` do cliente.
- `admin/processar_ordem_servico.php` — **não tinha registro nenhum antes**; agora busca o status atual antes do UPDATE e registra com `$created_by` (admin da sessão).

`admin/processar_atribuicao.php` não muda `status` (só `tecnico_id`), não dependia do trigger, e já grava em `historico_chamados` com `status_anterior/novo = NULL` — deixado como está, fora do escopo deste achado.

**Achado novo, descoberto ao testar a migração localmente** (não estava em nenhum documento de auditoria anterior): `historico_chamados.id` não tinha `PRIMARY KEY` nem `AUTO_INCREMENT` no banco local — todas as 23 linhas existentes tinham `id=0`. O dump `app/Database/NetoNerd_BD.sql` (árvore morta) tem a definição correta (`ADD PRIMARY KEY` + `MODIFY id AUTO_INCREMENT`), mas nunca foi aplicada neste banco. Corrigido na mesma migração (`DROP COLUMN id` + recriar como `AUTO_INCREMENT PRIMARY KEY`, renumerando as linhas existentes pela ordem física). **Antes de rodar em produção, confirmar se lá tem o mesmo problema** (`SHOW CREATE TABLE historico_chamados`) — se produção já estiver correta, esse trecho específico da migração deve ser pulado.

Testado localmente: `registrarHistoricoStatus()` grava a linha certa (`id` sequencial, `status_anterior`/`status_novo`/`usuario_id`/`comentario` corretos) e um `UPDATE` direto de `chamados.status` não duplica mais nada (trigger de fato removido).

## 4. BE3 — unificação de `Ativo`/`status_tecnico`

`status_tecnico` (`'Active'`/`'Inactive'`) mantido como única coluna — é a mais lida no sistema e a única editável via `admin/editar_tecnico.php` (que nunca tocava em `Ativo`, causando a dessincronia do achado original). Migração sincroniza os dados (`Ativo = 1` onde `status_tecnico = 'Active'`) antes de dropar a coluna. Código atualizado nos 5 pontos de leitura (`admin/atribuir_chamados.php` ×2, `admin/gerar_ordem_servico.php`, `admin/listar_ordens_servico.php`, `admin/processar_atribuicao.php`) e no INSERT de `admin/processa_adicionar_tecnico.php` (removida a coluna `Ativo` do INSERT, mantendo `bind_param` consistente com as 6 colunas restantes). `admins.Ativo` é coluna própria de outra tabela (criada na Fase 4), sem redundância — não faz parte deste achado, não foi tocada.

## 5. BE26 / C9 — coluna `tecnicos.senha` removida

Confirmado por busca que nenhum INSERT/UPDATE/SELECT ativo lia ou gravava `tecnicos.senha` — login (`controller/valida_loginTecnico.php`) e criação de técnico (`admin/processa_adicionar_tecnico.php`) já usavam só `senha_hash`. Coluna removida na migração. **Pré-requisito que fica pendente para produção** (não é código, é dado): confirmar que `senha_hash` está populada e devidamente hasheada para todos os técnicos, e forçar troca de senha do registro `id=2` (achado C9 original, senha em texto puro) antes de rodar a migração lá — depois de dropar a coluna, esse dado não pode mais ser recuperado.

## 6. BE6 + BE13 — transação e arredondamento em `criarParceladas()`

`despesas/classes/Despesa.php::criarParceladas()` (PDO) não tinha `beginTransaction()` — uma falha na parcela 7 de um parcelamento de 12x deixava 6 despesas órfãs, sem forma automática de detecção. Envolvido o loop inteiro em `beginTransaction()`/`commit()`/`rollBack()`.

Aproveitado o mesmo método para corrigir BE13 (achado relacionado, mesma função): antes, todas as parcelas usavam `round($valorTotal / $totalParcelas, 2)`, deixando resíduo de centavos nunca cobrado (R$100 em 3x → 33.33 × 3 = 99.99). Agora a última parcela absorve o resíduo (`valorTotal - soma das parcelas anteriores`). Testado isoladamente (script PHP CLI, sem depender do banco de despesas): 33.33 + 33.33 + 33.34 = 100.00 exato.

## 7. BE14 — lock nos crons

`GET_LOCK`/`RELEASE_LOCK` do MySQL adicionado em `cron/verificar_licencas.php` (mysqli) e `despesas/cron_gerar_recorrentes.php` (PDO, via uma instância própria de `Database` só para o lock, já que `Despesa::$conn` é privada) — `despesas/web_cron.php` herda a proteção automaticamente por ser um wrapper que inclui o outro arquivo. Lock liberado via `register_shutdown_function` em vez de só no fim do bloco `try`, para ser robusto a erro fatal (não travar o próximo cron indefinidamente). Testado isoladamente contra o banco local que `GET_LOCK`/`RELEASE_LOCK` se comportam como esperado. `UNIQUE(usuario_id, nome_conta, data_vencimento)` em `despesas`, sugerido no achado original como proteção adicional em banco, não foi implementado — considerar se duplicação continuar acontecendo na prática mesmo com o lock.

## 8. Fase 5 concluída (código e teste local)

Todos os itens do plano (A2 a A4, BE3, BE6, BE13, BE14, BE26/C9) resolvidos. Migração única `config/bandoDeDados/migracao_fase5_banco_dados.sql` criada, aplicada e verificada no banco local (`netonerd`) antes de qualquer alteração de código que dependesse dela. Todos os arquivos PHP tocados passaram em `php -l` sem erro.

**Pendências que ficam para o lançamento:**
- A migration `migracao_fase5_banco_dados.sql` só foi testada em banco local — antes de rodar em produção, confirmar se `historico_chamados` lá também está sem `PRIMARY KEY`/`AUTO_INCREMENT` (ver seção 3) e se `senha_hash` de todos os técnicos está populada e hasheada corretamente (ver seção 5, C9 original sobre o registro id=2).
- `UNIQUE` em `despesas` (BE14) e em `chamados.protocolo` (BE1/BE8, mencionado desde a Fase 3) seguem como proteção de banco adicional não implementada — avaliar se vale a pena numa fase futura.

---

# Fase 7 (2026-07-15) — Design System: drawer lateral + paleta do logo

Itens 1-3 do plano (`docs/PLANO_DE_CORRECAO.md`). Trabalho puramente de frontend — nenhuma migração de schema, nenhum dado de produção tocado. Achado inicial que redefiniu o escopo: `docs/STATUS_MIGRACAO_DESIGN.md` (2026-01-22) afirmava "12/12 páginas migradas, 100% completo" para o Design System v2.0, mas uma checagem de código encontrou **26 páginas** já usando `includes/header.php`/`netonerd-global.css` — a migração continuou depois de janeiro sem que o documento fosse atualizado. Isso significava que a maior parte do trabalho de "migrar página por página" do plano original já não era necessária; restava trocar a camada de layout/paleta por baixo do que já existia, e migrar só as poucas páginas realmente fora do sistema.

## 1. Paleta do logo aplicada em `assets/css/netonerd-global.css`

Paleta trocada de Bootstrap genérico (`--primary-blue: #007bff`) para a extraída do logo (`docs/DESIGN_SYSTEM_NOTAS.md`): `#0B3D91` azul-marinho como primária, `#38BDF8` ciano como cor de destaque (`--color-accent`, variável nova), mais ajustes de sombra/texto/fundo para tons mais neutros (slate) do que os cinzas Bootstrap originais. Decisão: manter os **nomes de variável já existentes** (`--primary-blue`, `--bg-light`, `--text-dark` etc.) e só trocar os valores — evita qualquer mudança nos componentes que já as referenciam (`nn-card`, `nn-btn`, `nn-badge`, `nn-table`, `nn-form-control`, `nn-alert`, `nn-stats-grid`), que herdam a cor nova automaticamente sem precisar de nenhuma edição.

Duas classes utilitárias que não existiam foram adicionadas durante a migração de `visualizar_chamado.php` (seção 3): `.nn-badge-secondary` e `.nn-text-medium`/`.nn-text-light`.

## 2. Header horizontal → drawer lateral colapsável

`includes/header.php` reescrito por completo. Antes: `<nav class="nn-header-nav">` horizontal fixo no topo, com toggle mobile simples (abre/fecha, sem persistência). Depois: sidebar lateral fixa (`.nn-sidebar`), no padrão já usado por StyleManager/Escritorius — logo+toggle no topo (`.nn-sidebar-header`), bloco de usuário (`.nn-sidebar-user`), navegação por seção (`.nn-sidebar-section-title` + `.nn-sidebar-link`), logout no rodapé (`.nn-sidebar-footer`). Nomenclatura de classe seguiu a convenção `nn-*` já usada no projeto (decisão do usuário: nomenclatura de classe é irrelevante, o que importa é o resultado visual fixo e padronizado — não a paridade literal de nomes com o StyleManager).

Comportamento novo, que não existia antes:
- Colapso desktop (ícone-only) via botão na própria sidebar ou na topbar, persistido em `localStorage` (chave `nn_sidebar_collapsed`), restaurado no carregamento da página (só acima de 992px).
- Drawer mobile (abaixo de 992px): sidebar sai da tela (`transform: translateX(-100%)`) e volta como overlay ao clicar no hambúrguer da topbar, com fundo escurecido (`.nn-sidebar-overlay`) que fecha o menu ao ser clicado; fecha também automaticamente ao navegar para outro link.
- Tooltip com o nome do item ao passar o mouse sobre a sidebar colapsada (`content: attr(data-label)`).

A lógica PHP de variação de menu por `$_SESSION['tipo']` (admin/tecnico/cliente) e os badges de contagem dinâmica (chamados não atribuídos para admin, chamados ativos para técnico) foram preservados sem alteração de comportamento — só o HTML ao redor mudou de `<nav>` horizontal para `<nav class="nn-sidebar-nav">` vertical.

`includes/footer.php`: corrigida, de passagem, uma duplicidade encontrada durante a leitura do arquivo — carregava o Bootstrap Bundle JS duas vezes, em duas versões diferentes (`5.3.0` e `5.3.2`). Mantida só a `5.3.2`.

`includes/page-template-example.php`: comentário de cabeçalho atualizado (v2.0 → v2.1) para deixar claro que o template já reflete o layout em sidebar; o wrapper `nn-main-wrapper`/`nn-content-full` do template não precisou de nenhuma mudança estrutural, porque o novo CSS de layout já assume esse wrapper.

## 3. `cliente/visualizar_chamado.php` — migrada para o Design System

Era a última tela do fluxo do cliente ainda fora do sistema `nn-*`: montava seu próprio `<head>`/`<style>` inline, usando `../css/main.css` (o CSS antigo genérico) em vez de `includes/header.php`. Reescrita seguindo o mesmo padrão já usado em `editar_chamado.php` (migrada na Fase 3): toda a lógica de backend (queries preparadas, JOIN com técnico/admin, busca de respostas e anexos) foi preservada sem alteração; só o HTML foi reconstruído com `nn-card`, `nn-badge` (funções helper `getStatusBadgeClass()`/`getPrioridadeBadgeClass()` adaptadas para retornar classes `nn-badge-*` em vez das antigas), `nn-stats-grid` para os metadados do chamado, e a timeline de respostas com o mesmo layout visual em cards.

## 4. `cliente/abrir_chamado.php` — achado maior que o esperado, corrigido

Ao investigar por que essa página não aparecia na lista de páginas migradas apesar de ter um `include('../includes/header.php')` no topo, foi encontrado um bug estrutural: a página incluía o header (que já é um documento HTML completo, com `<!DOCTYPE>`/`<head>`/`<body>` e a sidebar) e **em seguida montava um segundo `<!DOCTYPE html><html><head>...<body>` por cima**, com um wizard de 4 etapas inteiro (categoria → detalhes → prioridade → revisão) em CSS/HTML próprio, nunca de fato integrado ao Design System. Isso resultava em HTML inválido (dois `<html>`/`<body>` aninhados) que os navegadores toleravam silenciosamente, mas a sidebar nunca aparecia de fato nessa tela.

Corrigido com o aval do usuário (mudança maior que a prevista originalmente para este item): removido o `<html><head><body>` duplicado, todo o CSS custom do wizard portado para classes `nn-*` novas (`nn-wizard-steps`, `nn-category-card`, `nn-priority-option`, `nn-file-upload-area` etc.) usando as variáveis de cor da paleta nova em vez dos hexadecimais hardcoded que o wizard tinha (`#667eea`/`#764ba2` — paleta antiga, nem sequer a `#007bff` do resto do sistema). Toda a lógica JavaScript original foi preservada 1:1 (navegação entre steps, contador de caracteres, drag-and-drop de anexos, validação por etapa, resumo final) — só os seletores de classe foram atualizados para os novos nomes `nn-*`. Também adicionado `requireCliente()` no topo do arquivo, que não existia antes (a página dependia implicitamente de `$_SESSION['id']` sem checagem explícita de sessão), na mesma linha do padrão já usado nas demais páginas de `cliente/`.

## 5. `tecnico/loginTecnico.php` — paleta atualizada

Tela de login do técnico (pré-sessão, não recebe sidebar). As 10 ocorrências de `#007bff`/`#0056b3` hardcoded no `<style>` inline foram trocadas para `#0B3D91`/`#082b68` (paleta nova), sem nenhuma outra mudança estrutural.

## 6. Testado via navegador (Playwright)

Como as páginas migradas exigem sessão de cliente autenticado, foi criada uma conta de teste isolada (`fase7.teste@netonerd.local`, e-mail e senha só para este teste) diretamente no banco local — **sem tocar nas contas reais existentes** — e removida ao final do teste, junto com o arquivo temporário de hash de senha. Fluxo validado com Chromium headless:
- Login com a conta de teste, chegada em `cliente/home.php` com a sidebar renderizando (logo, avatar com iniciais, navegação, paleta nova nos stat-cards).
- Colapso da sidebar via botão — reduz para ícones, conteúdo principal expande.
- Reload da página com a sidebar ainda colapsada — confirma a persistência via `localStorage`.
- Viewport mobile (480px): sidebar oculta por padrão, abre como drawer com overlay escurecido ao clicar no hambúrguer.
- `cliente/abrir_chamado.php`: navegação completa pelas 4 etapas do wizard (seleção de categoria, preenchimento de título/descrição, seleção de prioridade, tela de revisão com resumo dos dados preenchidos) sem erros no console do navegador.
- `tecnico/loginTecnico.php`: confirmada visualmente a paleta azul-marinho nova na tela de login.

## 7. Documentação atualizada

- `docs/STATUS_MIGRACAO_DESIGN.md`: reescrito (virou v2.1) corrigindo a contagem desatualizada (12→28 páginas) e documentando o que a Fase 7 mudou na camada de layout/paleta.
- `docs/PLANO_DE_CORRECAO.md`: itens 1-3 da Fase 7 marcados como concluídos, com o achado do HTML duplicado em `abrir_chamado.php` registrado.

**Pendente da Fase 7** (itens 4-7 do plano original, não tratados nesta sessão): migrar telas de técnico ainda fora do sistema (depende de decisão via B4 para as telas mortas); consolidar as 3 versões de Bootstrap coexistindo no projeto (4.0.0/4.5.2/5.3.x); polish de acessibilidade/UX (FE12-FE19: `pattern`/`maxlength` em telefone/CPF, proteção contra duplo-submit em `resolver_chamado.php`, `<label for=...>` consistente, `alt` em imagens). Fora do escopo por decisão explícita do usuário: `publics/` (site institucional sem login, continua com Bootstrap 4 antigo) e `app/` (árvore MVC paralela majoritariamente morta).

---

# Fase 7, continuação (2026-07-16) — item 4 + achados críticos de fluxo de anexos

## 8. `admin/configura.php` — migrado para o Design System (item 4)

Última página relevante ainda fora do sistema `nn-*`: montava seu próprio `<head>`, Bootstrap 4.5.2 via CDN, sem sidebar. Reescrita seguindo o mesmo padrão das demais páginas de admin (`require_once '../includes/header.php'`, `nn-card`/`nn-form-group`/`nn-stats-grid`). Toda a lógica de backend (leitura/gravação em `configuracoes_sistema`, agrupada por `grupo`) preservada sem alteração. Removida, de passagem, uma checagem de sessão redundante (`if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin')`) que duplicava exatamente o que `requireAdmin()` já fazia na linha acima. Aproveitado para corrigir mojibake (caracteres `�`) espalhados pelo arquivo original — texto restaurado para o português correto (`Configurações`, `autenticação`, `Segurança` etc.).

**Achado de dado, descoberto ao testar a página migrada:** a tabela `configuracoes_sistema` tinha o mesmo problema já visto em `historico_chamados` (Fase 5) e `logs_sistema` (Fase 6) — sem `PRIMARY KEY`/`AUTO_INCREMENT` em `id`. Aqui o efeito era mais visível: 7 chaves de configuração apareciam 3 vezes cada na tela (6 delas com a versão correta em outro grupo + 2 cópias com `id=0` no grupo "geral"; a exceção `sistema_nome` tinha sua própria versão correta também em "geral", junto das 2 cópias). Corrigido via `config/bandoDeDados/migracao_fase7_configuracoes_sistema.sql`: `DELETE` de todas as linhas com `id=0` (nenhuma delas era a versão correta de nenhuma chave) + `DROP COLUMN id` / recriar como `AUTO_INCREMENT PRIMARY KEY` + `UNIQUE KEY` em `chave` (impede a duplicação se repetir — antes o `ON DUPLICATE KEY UPDATE` já usado no INSERT de `admin/configura.php` não tinha nenhuma chave única para detectar duplicidade). Testado no banco local: tela passou de 15 campos duplicados para 7 campos únicos, um por grupo.

## 9. Bug crítico: chamado com anexo não persistia (relatado pelo usuário)

Usuário reportou que, ao abrir um chamado como cliente de teste anexando um PDF e uma imagem, nada era salvo — dashboard voltava com todos os cards zerados. Log do Apache (`error.log`) revelou a causa exata: `registra_chamado: Unknown column 'tipo_arquivo' in 'field list'`.

Causa raiz: tanto `cliente/registra_chamado.php` quanto `admin/abrir_chamado_admin.php` tinham um `INSERT INTO anexos_chamado` referenciando colunas que não existem no schema real (`tipo_arquivo`, `tamanho`) — o schema real usa `tipo_mime`, `tamanho_bytes`, e ainda exige `usuario_upload_id`/`tipo_usuario` (`NOT NULL`, nenhum dos dois preenchido pelo INSERT antigo). Esse bug é **anterior** a esta sessão (não foi introduzido pela Fase 7) — só não tinha sido pego por nenhum teste anterior porque nenhum teste havia anexado arquivo. Como o INSERT do anexo roda dentro da mesma transação do chamado (`begin_transaction`/`commit`/`rollback`), a exceção lançada pela query inválida disparava o `rollback()` e desfazia o chamado inteiro junto — por isso o usuário via "nada foi criado", não um erro parcial.

Corrigido nos 2 arquivos: `INSERT` ajustado para as colunas reais, com `usuario_upload_id` = id de quem está logado e `tipo_usuario` fixo (`'cliente'`/`'admin'` conforme o arquivo). Testado via Playwright reproduzindo o cenário exato do usuário (PDF + imagem anexados no wizard de `abrir_chamado.php`): chamado e os 2 anexos persistidos corretamente, com `tipo_mime`/`tamanho_bytes`/`usuario_upload_id`/`tipo_usuario` todos preenchidos.

## 10. Achado adicional: anexos nunca eram exibidos em nenhuma tela de detalhe (exceto uma)

Ao investigar o bug acima, ficou evidente que **nenhuma tela de detalhe de chamado (técnico, admin) mostrava os anexos enviados** — só `cliente/visualizar_chamado.php` (já migrada nesta fase) fazia a query em `anexos_chamado`. Adicionada a mesma seção "Anexos" (grid de cards com ícone por tipo de arquivo, nome, quem enviou e quando) em `tecnico/detalhes_chamado.php` e `admin/visualizar_chamado.php`, reaproveitando exatamente o padrão visual já usado em `cliente/visualizar_chamado.php`. Testado com os 2 anexos de teste (PDF + PNG) visíveis corretamente nas 3 telas (cliente, técnico, admin).

## 11. Achado maior: 2 telas de detalhe de chamado para o cliente, causando fluxo quebrado

Ao adicionar a seção de anexos, ficou claro que o cliente tinha **duas telas de detalhe diferentes e inconsistentes**: `cliente/home.php` linkava para `visualizar_chamado.php` (tinha formulário de resposta + botão "Confirmar Resolução"); `cliente/meus_chamados.php` linkava para `detalhe_chamado.php` (sem formulário de resposta nem botão de fechar). Pior: os processors reais dessas ações (`adicionar_resposta.php`, `fechar_chamado.php`) redirecionavam de volta para `detalhe_chamado.php` — ou seja, um cliente que entrasse pelo caminho de `meus_chamados.php` **nunca conseguia de fato responder ou fechar um chamado**, porque a tela que via não tinha os elementos que essas ações preenchem.

Decisão do usuário: consolidar em uma tela só, mesmo princípio já usado no M1 da Fase 6 (eliminar cópias divergentes em vez de mantê-las). `visualizar_chamado.php` foi escolhida (mais completa: resposta, fechamento, anexos) e recebeu o que só existia em `detalhe_chamado.php`: telefone do técnico responsável, seção "Fotos do Serviço" (tabela `chamado_fotos`, distinta de `anexos_chamado` — fotos que o técnico anexa durante o atendimento, não os anexos da abertura), e as mensagens de `?sucesso=`/`?erro=` usadas pelos processors. Os 3 pontos que apontavam para `detalhe_chamado.php` (`adicionar_resposta.php`, `fechar_chamado.php`, `meus_chamados.php`) foram atualizados para `visualizar_chamado.php`; o arquivo `cliente/detalhe_chamado.php` (sem mais nenhuma referência) foi movido para `_REMOVER_DO_SERVIDOR/cliente/` (já bloqueada no `.htaccess` desde a Fase 1).

Testado via Playwright: fluxo completo a partir de `meus_chamados.php` → clique no chamado → `visualizar_chamado.php` renderiza com anexo, histórico de interações e formulário de resposta, tudo numa tela só.

## 12. Testado com 3 contas de teste isoladas (admin, técnico, cliente)

Diferente do teste anterior desta fase (só cliente), essa rodada exigiu logins de admin e técnico para validar as telas correspondentes. Criadas 3 contas de teste isoladas — `ADM000001`/admin, `2026F9001`/técnico (login por matrícula em `tecnico/loginTecnico.php`), `fase7.teste@netonerd.local`/cliente (login por e-mail em `publics/login.php`) — **mantidas no banco a pedido explícito do usuário**, para uso contínuo em testes futuros (não foram removidas ao final da sessão, ao contrário do padrão anterior de limpar contas de teste). Os 3 chamados de teste criados durante a validação (ids 13, 14, 15 — incluindo um criado manualmente pelo próprio usuário) também foram mantidos, mesma decisão.

---

# Fase 7, continuação (2026-07-16) — bateria de bugs reportados pelo usuário no fluxo admin

Após a migração de `admin/configura.php`, o usuário testou o fluxo administrativo real (dashboard, OS, atribuição de chamados, LGPD) e reportou 15 itens. Uma investigação técnica de cada um (incluindo um agente de exploração dedicado a 6 dos itens) separou bugs reais de falsos-positivos antes de qualquer correção.

## 1. Erro 500 ao abrir chamado como admin — `bind_param` com contagem errada

`admin/abrir_chamado_admin.php:116`: a query `INSERT INTO chamados` tinha 13 colunas (incluindo `criado_por_admin`, adicionada em algum momento sem atualizar a string de tipos do `bind_param`), mas a string de tipos `"isssssisssii"` só tinha 12 caracteres — faltava um `i` para o último parâmetro (`$admin_id`). Corrigido para `"isssssisssiii"`. Testado: chamado criado sem erro 500, persistido corretamente no banco.

## 2. "Excluir Ordem de Serviço" nunca excluía — cópia acidental de outro arquivo

O botão "Excluir Ordem de Serviço" em `admin/visualizar_ordem_servico.php` chama `excluir_ordem_servico.php` só com `os_id` (sem `novo_status`, porque a intenção é DELETE). Só que `excluir_ordem_servico.php` era uma **cópia idêntica, byte a byte** (exceto a lista de status válidos) de `atualizar_status_os.php` — nunca fez `DELETE FROM ordens_servico`, só um `UPDATE status`. Sem `novo_status` no POST, caía direto no "Status inválido" reportado pelo usuário. Reescrito para fazer a exclusão real (`DELETE FROM ordens_servico WHERE id = ?`, com a mesma checagem de existência prévia e padrão de segurança dos demais arquivos de admin). Confirmado que não há tabela filha dependente de `ordens_servico` (schema sem FK apontando para ela). Testado: OS de teste criada isoladamente, excluída via fluxo real do modal, confirmado `DELETE` efetivo no banco.

## 3. CPF exposto sem proteção — implementado padrão do Escritorius

`admin/visualizar_ordem_servico.php` exibia o CPF do cliente em texto puro, sem nenhuma proteção. Usuário pediu o mesmo padrão já usado no projeto Escritorius (`Escritorius/src/helpers/functions.php::cpf_protected()` + `Escritorius/public/js/admin.js` + `Escritorius/src/controllers/admin/ProfileController.php::confirmPassword()`): CPF mascarado (`***.***.***-**`) por padrão no HTML (o valor real vai para o DOM via `data-cpf`, mas fica escondido visualmente), com botão "Ver CPF" que abre um modal pedindo a senha do admin logado; só após confirmação a máscara é trocada pelo valor real, sem nova consulta ao banco.

Implementado adaptando o padrão para `nn-*`: função `cpfProtegido()` em `admin/visualizar_ordem_servico.php` (formata e monta o HTML mascarado), modal `#cpfRevealOverlay` + JS de controle adicionados em `includes/header.php` (condicionados a `$tipo_usuario === 'admin'`, disponíveis em toda página de admin), endpoint novo `admin/confirmar_senha_cpf.php` (valida a senha via `password_verify()` contra `admins.senha_hash`, retorna só `{ok: true/false}` — nunca reenvia o CPF, que já estava no DOM). CSS novo em `netonerd-global.css` (`.nn-modal-overlay`, `.nn-modal-box`, `.nn-cpf-reveal-btn`). Testado via Playwright: CPF mascarado ao carregar, senha errada rejeitada com mensagem de erro, senha correta revela o CPF formatado e oculta o botão.

## 4. Endereço e CNPJ da empresa hardcoded e errados no template de impressão

`admin/imprimir_ordem_servico.php` tinha endereço (`R. Conselheiro Macedo Soares, 354 Sala 216 - Centro - Araruama/RJ`) e CNPJ (`51.243.583/0001-12`) fixos no HTML, sem vir de nenhuma config/tabela. Corrigido para o endereço real (`Rua Alameda Monte Castelo, 182 - Quebra Frascos - Teresópolis/RJ`) e o CNPJ do MEI do usuário (`65.663.425/0001-26`), confirmados diretamente com ele.

## 5. Busca quebra com espaço no final do termo

`admin/atribuir_chamados.php`, `admin/chamados_ativos.php` e `admin/listar_ordens_servico.php` liam `$_GET['busca']` sem `trim()` antes de montar o `LIKE '%...%'` — um termo como `"20260011 "` (com espaço à direita) virava `LIKE '%20260011 %'`, que não bate com nenhum protocolo real (que não tem espaço à direita no banco), fazendo a busca "quebrar" silenciosamente (nenhum resultado, sem erro). Corrigido adicionando `trim()` nos 3 arquivos. Testado: busca com espaço à direita agora encontra o resultado normalmente.

## 6. Cadastro de técnico com matrícula duplicada redirecionava para página inexistente

`admin/processa_adicionar_tecnico.php` redirecionava erros (`matricula_existente`, `email_existente`, `campos_obrigatorios` etc.) para `../admin/cadastrar_tecnico.php` — **arquivo que não existe no projeto**. O modal de cadastro de técnico fica embutido em `admin/dashboard.php`, não numa página própria. Por isso o usuário via "a página só recarrega": o redirect ia para uma rota inexistente, sem exibir mensagem de erro nenhuma. Corrigido apontando todos os redirects para `dashboard.php` (mesmo padrão já usado por `processa_adicionar_admin.php`, que nunca teve esse bug) — `dashboard.php` já tinha o tratamento de mensagem para a maioria desses códigos de erro.

## 7. Campo "Veículo" obrigatório no cadastro de técnico

Usuário apontou que nem todo técnico tem carro (pode ser interno). O campo `vehicle_of_the_day` tinha `required` no HTML do modal (`admin/dashboard.php`) e era tratado como obrigatório em `processa_adicionar_tecnico.php` (bloqueava o cadastro se vazio) — mas a coluna `tecnicos.carro_do_dia` no banco já aceita `NULL`. Removido o `required` do input e a checagem de obrigatoriedade no processor (grava `NULL` se o campo vier vazio).

## 8. Falta de `ELSE` no `CASE` de ordenação por prioridade (prevenção)

Investigação encontrou que a ordenação por prioridade em `admin/atribuir_chamados.php` e `admin/chamados_ativos.php` já usa um `CASE WHEN` correto por urgência lógica (crítica→1, alta→2, média→3, baixa→4) — **não** é ordenação alfabética como se suspeitava inicialmente. O risco real identificado: nenhum dos dois `CASE` tinha `ELSE`, então um chamado com `prioridade` NULL ou fora do ENUM (dado corrompido/legado) resultaria em `NULL` no `ORDER BY`, que o MySQL ordena antes de qualquer valor — colocando artificialmente esse chamado no topo da lista de "mais urgentes". Adicionado `ELSE 5` em ambos os `CASE`, por precaução (nenhum dado com esse problema foi encontrado no banco local, mas o risco existia).

## 9. `admin/abrir_chamado_admin.php` migrada para o Design System (achado da tela "visual diferente")

O que o usuário descreveu como "visual 100% diferente do adotado" (fundo escuro, sidebar preta) não era um bug de CSS isolado — essa tela nunca tinha sido migrada para o Design System da Fase 7 (nem para o v2.0 anterior): tinha `<head>`, `<style>` e sidebar HTML 100% próprios, com paleta hardcoded (`--primary: #0A1128`) sem nenhuma relação com a paleta `nn-*` usada no resto do admin desde 2026-01-22. Reescrita seguindo o padrão `nn-*` já usado nas demais telas de admin — `includes/header.php`, `nn-card`, `nn-form-group`, `nn-prioridade-card` (nova classe, mesmo conceito do wizard de `cliente/abrir_chamado.php` da Fase 7 anterior), `nn-drop-zone`. Toda a lógica de backend (criação de chamado com transação, upload de anexos, busca de cliente existente) preservada sem alteração — só o HTML/CSS/JS de apresentação foi reescrito. **Achado adicional no caminho**: a sidebar do admin (`includes/header.php`) não tinha nenhum link para essa tela — só era alcançável pela sidebar antiga (agora removida) ou por URL direta. Adicionado item "Novo Chamado" na seção "Chamados" do menu admin. Testado via Playwright: toggle cliente novo/existente, autocomplete de busca (funcionou com 3 letras), criação de chamado com cliente existente selecionado via autocomplete — tudo persistido corretamente no banco.

## Itens investigados e confirmados como falsos-positivos (não corrigidos, por não serem bugs)

- **"Filtro de OS só funciona com nome exato"**: `admin/listar_ordens_servico.php` já usa `LIKE '%...%'` (correspondência parcial) em `numero_os`, `cliente_nome` e `problema_relatado` — não há comparação exata em nenhum ponto dessa query. Hipótese mais provável: o usuário testou o autocomplete de `buscar_clientes.php` (usado em outras telas), que exige mínimo de 3 letras por design, e confundiu essa limitação com "precisa ser exato".
- **"Busca 'Junio' no LGPD não encontra"**: a query de `admin/buscar_clientes.php` está correta (`trim()`, `LIKE` parcial, collation case-insensitive). O cliente "Junio Asis" nunca foi cadastrado na tabela `clientes` — existe só como texto livre em `ordens_servico.cliente_nome` (uma OS antiga, `cliente_id NULL`), permitido pelo fluxo "Cliente Novo/Não Registrado" que tanto `abrir_chamado_admin.php` quanto `gerar_ordem_servico.php` oferecem. Isso é uma lacuna real de cobertura do LGPD (a busca de titulares não alcança clientes que só existem como texto solto em chamados/OS), mas não é bug de busca — fica registrado como item a avaliar numa iteração futura da Fase 6 (M2), não corrigido nesta sessão.

## Achado extra fora da lista do usuário (encontrado no log de erros do Apache)

`tecnico/paineltecnico.php:347` tinha `$stmt_tecnico->close()` chamado duas vezes (linha 22 e linha 347) — a segunda chamada lançava `Fatal error: mysqli_stmt object is already closed`, quebrando o painel do técnico com tela de erro branca a cada carregamento. Corrigido removendo a chamada duplicada. Não relacionado à Fase 7 nem reportado pelo usuário — encontrado ao investigar o log do Apache em busca de outros erros do dia.

## Pedido adicional registrado para tratamento futuro (não bug, melhoria de UX)

Usuário pediu máscaras de input para CPF, telefone, CNPJ e CEP (formatação automática enquanto o usuário digita), consistente com o item 7 do plano original da Fase 7 (`pattern`/`maxlength` em telefone/CPF). Não implementado nesta sessão — fica registrado como parte do trabalho pendente de polish de acessibilidade/UX da Fase 7.

---

# Fora do plano — Feature nova: encerrar chamado sem resolução (cliente sem resposta em 48h)

**Data:** 2026-07-16. Não é bug nem item do `PLANO_DE_CORRECAO.md` — é uma regra de negócio nova pedida pelo usuário, decorrente de um caso real que ele encontrou testando o sistema (chamado #20260012, onde o técnico enviou mensagem e o cliente nunca respondeu, deixando o chamado preso aguardando pagamento indefinidamente).

## Problema

`tecnico/resolver_chamado.php` só tinha um caminho para finalizar um chamado: "Resolver e Finalizar", que exige histórico do atendimento (mín. 50 caracteres), forma de pagamento (a menos que seja StyleManager Software) e pelo menos 1 foto do serviço. Não havia nenhuma forma de encerrar um chamado quando o cliente simplesmente não responde às mensagens internas do técnico — o chamado ficava preso indefinidamente em "em andamento"/"pendente".

## Decisões de produto (confirmadas com o usuário)

- **Status usado**: reaproveitado `'cancelado'` (já existente no ENUM de `chamados.status`, hoje usado quando o cliente cancela o próprio chamado) em vez de criar um status novo — evita migração de schema e mantém consistência com os pontos que já tratam `cancelado` como "sem cobrança, sem mais trabalho" (dashboards, relatórios, filtros). O histórico de status (`historico_chamados`) e a atualização registrada (`chamado_atualizacoes`) diferenciam a origem: texto explícito "Chamado encerrado sem resolução — cliente não respondeu em 48h", distinto do cancelamento feito pelo próprio cliente.
- **Gatilho**: liberado automaticamente 48h depois da última mensagem do técnico no chamado, **se** o cliente não respondeu depois dela. Sem intervenção manual de admin — o técnico decide usar ou não, mas só pode usar depois da janela de 48h.

## Implementação

### Cálculo de elegibilidade (`tecnico/resolver_chamado.php`)

Compara duas fontes de "mensagem do técnico" (o sistema tem duas tabelas paralelas de interação):
- `chamado_atualizacoes` com `tipo_atualizacao = 'comentario'` (comentários internos do técnico, ex: usados no modal "Adicionar Atualização" de `tecnico/detalhes_chamado.php`)
- `respostas_chamado` com `tipo_usuario IN ('tecnico', 'admin')` (respostas na timeline pública que o cliente vê em `cliente/visualizar_chamado.php`)

A mais recente das duas é considerada "última mensagem do técnico". Se não há nenhuma resposta do cliente (`respostas_chamado.tipo_usuario = 'cliente'`) **depois** dessa data, e já se passaram ≥48h, o chamado fica elegível — a seção "Encerrar sem Resolução" aparece com o campo de justificativa habilitado; caso contrário, mostra quantas horas faltam (ou pede para o técnico enviar uma mensagem primeiro, se nunca enviou nenhuma).

### Novo processor (`tecnico/processar_encerramento_sem_resolucao.php`)

Espelha o padrão de segurança de `processar_resolucao.php` (`requireTecnico()`, CSRF, transação) mas **revalida a elegibilidade de 48h no servidor**, em vez de confiar só na tela — importante porque essa é uma ação que fecha o chamado permanentemente. Exige justificativa de no mínimo 20 caracteres. Grava `status = 'cancelado'` + `data_fechamento = NOW()`, registra em `chamado_atualizacoes` (tipo `conclusao`) e em `historico_chamados` via `registrarHistoricoStatus()`, e loga a ação via `registrarLogSistema()` — mesmo padrão de auditoria já usado em `processar_resolucao.php`.

`tecnico/meus_chamados.php` ganhou o case `encerrado_sem_resolucao` no switch de mensagens de sucesso.

## Testado

- **Cenário elegível**: chamado de teste (id 13) com um comentário do técnico datado de 3 dias atrás e nenhuma resposta do cliente depois — a seção apareceu corretamente ("cliente não responde há 3 dia(s), 77h"), formulário de justificativa habilitado. Submissão testada via Playwright: `status` mudou para `cancelado`, `data_fechamento` preenchida, histórico e atualização gravados com o texto correto.
- **Cenário não elegível**: chamado de teste (id 15) com comentário do técnico de apenas 2h atrás — a seção não exibe o formulário (só a mensagem informativa de quanto falta).
- **Bypass via requisição direta**: tentativa de `POST` direto em `processar_encerramento_sem_resolucao.php` para o chamado 15 (não elegível), com CSRF válido — rejeitada pela validação server-side; `status` do chamado permaneceu `em andamento`, confirmando que a proteção não depende só da UI.

## Achado relacionado, registrado mas não implementado

Ao investigar o chamado #20260012 mencionado pelo usuário, o campo `historico_atendimento` continha uma sugestão de produto do próprio usuário (deixada ali como teste): trocar o campo de texto livre do histórico de atendimento por campos estruturados (Equipamento, Marca, Número de Série, etc.), para gerar uma "ordem de serviço" mais confiável e padronizada a partir da resolução do chamado. Não avaliado nem implementado nesta sessão — fica registrado como ideia de produto para uma iteração futura da tela `tecnico/resolver_chamado.php`.

---

# Fase 7, item 7 (parcial) — máscaras de input em CPF/telefone/CEP

**Data:** 2026-07-16. Pedido do usuário registrado em sessão anterior, item do plano da Fase 7 ("polish de acessibilidade/UX"). Escopo: só `cliente/`, `tecnico/`, `admin/` (áreas logadas já migradas para o Design System) — `publics/`/`app/` fora, mesma decisão de escopo já aplicada ao resto da Fase 7.

## Mapeamento

Um agente de exploração dedicado mapeou todos os `<input>` de CPF/telefone/CNPJ/CEP nas 3 pastas do escopo: **6 campos em 4 arquivos** (nenhum campo de CNPJ existe hoje no sistema — só CPF, telefone e CEP):
- `cliente/minha_conta.php`: `telefone`, `cep`
- `admin/abrir_chamado_admin.php`: `cliente_telefone`
- `admin/editar_tecnico.php`: `telefone`
- `admin/gerar_ordem_servico.php`: `cliente_telefone`, `cliente_cpf`

Nenhum desses campos tinha `maxlength`, `pattern` ou máscara funcional antes — 2 deles tinham só um placeholder de texto estático (`(00) 00000-0000`, `000.000.000-00`), sem nenhuma formatação real durante a digitação. Já existia uma implementação de máscara em `public/assets/js/main.js` (`setupInputMasks()`, baseada em jQuery), mas está fora do escopo (pasta `public/`) e não é referenciada por nenhuma página de `cliente/`/`tecnico/`/`admin/` — usada como referência de regex, não reaproveitada diretamente.

## Implementação

Função de máscara em JavaScript vanilla (sem jQuery, que não está carregado na área logada) adicionada em `includes/header.php`, aplicada via atributo `data-mask` (`"cpf"`, `"cnpj"`, `"phone"`, `"cep"`) — CNPJ implementado apesar de não ter uso hoje, para cobrir o pedido original do usuário caso um campo de CNPJ apareça no futuro. Preserva a posição do cursor durante a digitação (recalcula `selectionStart` após reformatar o valor, para não "pular" o cursor pro fim do campo a cada tecla). Aplicado `data-mask` + `maxlength` correspondente nos 6 campos mapeados.

**Bug encontrado e corrigido durante o próprio desenvolvimento**: a primeira versão registrava os listeners de máscara imediatamente ao carregar o script, mas `includes/header.php` é incluído no **topo** de cada página, antes do HTML do formulário (com os inputs de CPF/telefone) ser impresso — os `querySelectorAll('[data-mask=...]')` rodavam contra um DOM que ainda não tinha esses elementos, então nenhuma máscara era aplicada. Corrigido envolvendo o registro em `DOMContentLoaded` (ou execução imediata se o documento já tiver terminado de carregar, para cobrir o caso de scripts injetados depois do load).

## Testado

Via Playwright, digitando em cada um dos 6 campos nas 4 páginas: telefone formata para `(21) 99999-8888`, CPF para `111.222.333-96`, CEP para `25953-080` — todos em tempo real, sem erros de console.

---

# Fase 7 — Itens 5, 6 e 7 concluídos (2026-07-17), fechando a Fase 7 por completo

## Item 5 — Migrar técnico

Mapeamento (agente de exploração) dos arquivos de `tecnico/` fora dos 4 já migrados na Fase 7 anterior (`paineltecnico.php`, `meus_chamados.php`, `resolver_chamado.php`, `detalhes_chamado.php`):
- `tecnico/processar_chamado.php`, `tecnico/processar_resolucao.php`, `tecnico/processar_encerramento_sem_resolucao.php` — processors puros, sem HTML, só `header('Location: ...')`. Não são candidatos a Design System (nunca renderizam página visível).
- `tecnico/loginTecnico.php` — pré-login, sem sidebar, já tratado (paleta) na Fase 7 anterior.
- `tecnico/logoff.php` — **achado real**: órfão de navegação. Comparado byte a byte com `controller/logout.php` (o logout de fato usado por `includes/header.php`/`nn-sidebar-logout`) — são idênticos. Nenhum link ativo no sistema aponta para `tecnico/logoff.php` (confirmado via grep; as únicas ocorrências são em `app/`/`routes/`, código morto sem roteamento). Movido para `_REMOVER_DO_SERVIDOR/tecnico/` (pasta já bloqueada no `.htaccess` desde a Fase 1).

## Item 6 — Consolidar Bootstrap

Varredura confirmou que dentro do escopo ativo (`cliente/`, `tecnico/`, `admin/`, `includes/`) não havia mais nenhuma referência a Bootstrap 4.x — as versões antigas só existem em código morto (`$BACKUP_DIR/`, `app/`, `_REMOVER_DO_SERVIDOR/`). Único resíduo real: `tecnico/meus_chamados.php` carregava uma segunda tag `<script>` de Bootstrap 5.3.2 (redundante — `includes/footer.php` já carrega a mesma versão de forma confiável), comentada como "FORÇAR CARREGAMENTO", junto de ~60 linhas de um bloco `console.log` de debug do modal de atualização de chamado (`=== INICIANDO DEBUG DO MODAL ===`, `✓`/`✗` em cada checagem). Ambos removidos — mantida só a lógica funcional real do listener `show.bs.modal`, sem os logs.

**Achado extra, fora do escopo original do item 6**: ao confirmar que não sobrava nenhuma referência a Bootstrap 4.x no escopo ativo, apareceu `cliente/contato.php` — não constava em nenhuma auditoria anterior como pendente, mas tinha exatamente o mesmo bug estrutural já corrigido em `cliente/abrir_chamado.php` e `admin/abrir_chamado_admin.php` nas sessões anteriores da Fase 7: incluía `includes/header.php` (documento HTML completo com a sidebar `nn-*`) e **depois** montava um segundo `<!DOCTYPE html><html><body>` por cima, com Bootstrap 4.0.0 e CSS próprios, nunca de fato integrado ao Design System. Migrada seguindo o mesmo padrão das duas correções anteriores — toda a lógica de FAQ toggle (`toggleFAQ()`, `scrollToFAQ()`) preservada, HTML reconstruído com `nn-card`/`nn-quick-actions`/`nn-faq-item`. Adicionado `requireCliente()`, que faltava (a página só tinha `validador_acesso.php`, sem checagem explícita de tipo de usuário — mesmo padrão de reforço já aplicado nas outras migrações desta fase). Um ajuste de contraste corrigido no caminho: o título "Entre em Contato" dentro do card de fundo azul-marinho estava sem `color: white`, ficando ilegível.

## Item 7 — Polish de acessibilidade/UX

### Duplo-submit

`tecnico/resolver_chamado.php` tem 2 forms na mesma tela (resolver chamado normalmente, ou encerrar sem resolução — feature adicionada nesta mesma Fase 7). Ambos os botões de submit agora desabilitam e trocam para um spinner (`<i class="fas fa-spinner fa-spin"></i> Enviando...`) — no form principal, só **depois** de passar em todas as validações client-side existentes (histórico ≥50 caracteres, pagamento selecionado, foto anexada, confirmação via `confirm()`), para não deixar o botão travado por um `preventDefault()` de validação sem o form ter sido de fato enviado.

### `alt` em imagens

Verificado: as 3 únicas ocorrências de `<img>` dentro do escopo ativo (`cliente/visualizar_chamado.php`, `tecnico/detalhes_chamado.php` — fotos de serviço — e `tecnico/loginTecnico.php` — logo) já tinham `alt` descritivo (`"Foto do serviço"`, `"NetoNerd"`). Nenhuma ação necessária.

### `<label for=...>` — o item de maior escopo desta fase

Levantamento (`grep -c` comparando `nn-form-label` total vs. com `for=`) revelou um padrão amplo e não documentado em nenhuma auditoria anterior: **~150 labels em ~20 arquivos ativos**, quase todos sem associação `for`/`id` — só `cliente/editar_chamado.php` e `admin/configura.php` já seguiam o padrão correto (não por acaso, são as 2 páginas migradas com mais cuidado em sessões anteriores). Sem essa associação, um leitor de tela não anuncia a qual campo um label pertence, e clicar no texto do label não foca/marca o input correspondente — falha real de acessibilidade (WCAG 1.3.1, 4.1.2), não cosmética.

Corrigido via 4 agentes em paralelo, um por área, cada um com instrução idêntica (adicionar `id` ao campo se não tiver, `for="mesmo_id"` no label; pular campos com `name[]` — colchete não é `id` válido; pular labels que já envolvem o próprio input, como radiobuttons de prioridade/pagamento; verificar contra `getElementById` existente no mesmo arquivo antes de cravar um `id` novo, para não colidir com JS já funcionando):

| Área | Arquivos | Labels corrigidos |
|---|---|---|
| `cliente/` | `abrir_chamado.php`, `meus_chamados.php`, `minha_conta.php` | 2 + 3 + 9 = 14 |
| `tecnico/` | `detalhes_chamado.php`, `meus_chamados.php`, `resolver_chamado.php` | 2 + 4 + 2 = 8 |
| `admin/` (parte 1) | `abrir_chamado_admin.php`, `api_keys.php`, `atribuir_chamados.php`, `categorias.php`, `chamados_ativos.php`, `dashboard.php` | 8+11+4+4+5+10 = 42 |
| `admin/` (parte 2) | `editar_tecnico.php`, `gerar_ordem_servico.php`, `lgpd_titulares.php`, `licencas.php`, `listar_ordens_servico.php`, `relatorios.php`, `relatorio_tecnico.php`, `visualizar_ordem_servico.php` | 6+20+1+5+3+2+2+1 = 40 |

**Total: 104 labels corrigidos** (a contagem de ~98 estimada inicialmente ficou um pouco abaixo do real). Casos deixados intencionalmente sem `for`, confirmados pelos agentes como corretos:
- Radiobuttons/checkboxes onde o próprio `<label>` envolve o `<input>` (cards de prioridade, forma de pagamento, "Categoria Ativa") — já são acessíveis por si só, `for` seria redundante.
- Labels de **exibição** sem campo editável associado — a maior parte em `admin/visualizar_ordem_servico.php` (Nome, Telefone, CPF, Endereço, dados de equipamento, valores, datas — todos seguidos por `<div class="nn-info-value">`, não por um input).
- Campos com `name="anexos[]"`/`name="fotos[]"` (colchete não é `id` HTML válido).
- Em `admin/dashboard.php`, dois modais (Adicionar Técnico, Adicionar Admin) usam campos com o mesmo `name` (`nome`, `email`, `matricula`/`registration`, `password`) — os agentes prefixaram os `id` novos (`tecnico_nome` vs `admin_nome` etc.) para não colidir entre os dois modais na mesma página.
- Em `admin/licencas.php` e `admin/listar_ordens_servico.php`, nomes de `id` como `licenca_cliente_nome`/`filtro_status` (em vez de `cliente_nome`/`status` genéricos) para evitar ambiguidade com outros elementos da mesma página.

Nenhum `name`, lógica PHP ou comportamento JS pré-existente foi alterado em nenhum dos 4 lotes — só adição de `id`+`for` onde faltavam.

## Testado (todos os itens 5-7 juntos)

Varredura de sintaxe (`php -l`) em lote nos 22 arquivos tocados nesta etapa — todos limpos. Smoke test via Playwright cobrindo os 3 perfis (admin, técnico, cliente): login funcionando, navegação por 13+ páginas de admin (incluindo todas as tocadas pelos 4 agentes de label/for) e as páginas relevantes de técnico/cliente sem erro de console real (só um 404 de recurso estático não crítico, já visto em sessões anteriores). Checagem funcional pontual confirmando que `label[for="filtro_status"]` de fato aponta para um `<input id="filtro_status">` existente no DOM.

**Fase 7 (Design System) concluída por completo em 2026-07-17** — todos os 7 itens do plano fechados. Ver `docs/PLANO_DE_CORRECAO.md` para o resumo consolidado de toda a fase.
