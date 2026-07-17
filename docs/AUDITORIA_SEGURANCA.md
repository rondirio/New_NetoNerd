# NetoNerd ITSM — Auditoria de Segurança (Complementar)

Este documento complementa `docs/AUDITORIA_ACHADOS.md` (primeira auditoria). Reúne achados de uma segunda rodada de varredura, mais ampla, cobrindo áreas que a primeira passagem não tinha alcançado: `admin/super_admin/`, `despesas/`, `StyleManager/*` (como superfície de exposição dentro do domínio NetoNerd), `social/`, `api/v1/`, `app/`. Não repete itens já documentados em `AUDITORIA_ACHADOS.md` — ver seção final para o mapeamento entre os dois documentos.

Mesma convenção: `[ ]` pendente, `[x]` corrigido nesta pasta de trabalho, `[~]` corrigido parcialmente / precisa de ação fora do código.

**Restrição válida para toda correção deste documento:** o site é produção real e não pode sair do ar em nenhum momento. Toda correção precisa ser aplicável sem downtime.

---

## CRÍTICO

### S1. Backdoor de login hardcoded no Super Admin — qualquer um vira CEO
`app/Views/superadmin/auth/login_handler.php:23-32`:
```php
// ATENÇÃO: O acesso de teste (bypass) deve ser removido em um ambiente de produção.
if ($matricula === 'teste' && $password === 'senha') {
    session_regenerate_id(true);
    $_SESSION['superadmin_loggedin'] = true;
    $_SESSION['superadmin_id'] = 0;
    $_SESSION['superadmin_nome'] = 'Usuário de Teste';
    $_SESSION['superadmin_cargo'] = 'CEO';
    header("Location: ../dashboard.php");
    exit;
}
```
Confirmado ativo lendo o código: qualquer pessoa que envie `matricula=teste&password=senha` via POST recebe sessão de superadmin com cargo CEO, sem tocar no banco de dados. O próprio comentário do código já reconhecia o risco. Dá acesso total ao painel `app/Views/superadmin/` (cadastro de clientes, criação de banco de dados de cliente via `processa_cliente.php`, gestão de funcionários).
**Status: [ ]** Pendente — remover o bloco de bypass inteiro.

### S2. Cadastro de técnico sem autenticação — combina com C10 para virar admin sem nenhuma credencial
`admin/processa_adicionar_tecnico.php` (arquivo inteiro) não tem `session_start()` nem `requireAdmin()`/qualquer checagem de sessão, apesar de ser uma ação restrita a administradores:
```php
// Nenhuma checagem de sessão em todo o arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ...
    $sql = "INSERT INTO tecnicos (nome, email, status_tecnico, Ativo, matricula, carro_do_dia, senha_hash)
            VALUES (?, ?, ?, 1, ?, ?, ?)";
```
**Cadeia de exploração completa:** um atacante não autenticado envia `POST /admin/processa_adicionar_tecnico.php` com `registration=2026A999` (bate no regex `\d{4}A\d{3}` de C10, já documentado em `AUDITORIA_ACHADOS.md`), `password` própria e email/nome à escolha. O técnico é criado ativo. O atacante faz login normalmente em `tecnico/loginTecnico.php` com essa matrícula — `isAdmin()` promove automaticamente a admin. Acesso total a `admin/dashboard.php` sem nunca ter tido credencial prévia alguma.
**Status: [ ]** Pendente — adicionar `session_start()` + checagem de admin. Deve ser corrigido **junto ou antes** de C10, senão C10 sozinho não fecha a brecha.

### S3. `admin/excluir_tecnico.php` — checagem de admin comentada, DELETE acessível por qualquer visitante
`admin/excluir_tecnico.php:7-13`:
```php
session_start();
// // 1. Validação de Acesso (Apenas Admins podem excluir)
// if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SIM' || $_SESSION['tipo_usuario'] !== 'admin') {
//     header('Location: ../tecnico/loginTecnico.php?login=erro_acesso');
//     exit();
// }
```
O restante do arquivo executa `DELETE FROM tecnicos WHERE id = ?` usando só `filter_input(INPUT_GET, 'id', ...)`. Qualquer visitante, sem login, acessa `GET /admin/excluir_tecnico.php?id=2` e apaga o técnico de produção.
**Status: [ ]** Pendente — descomentar e corrigir a checagem de acesso.

### S4. `admin/limpar_tecnicos_id_zero.php` — script de manutenção sem autenticação, executa DELETE/UPDATE em massa
`admin/limpar_tecnicos_id_zero.php` não tem `session_start()`, não inclui middleware de auth. Ao ser acessado via GET simples, dispara imediatamente:
```php
$sql_update_chamados = "UPDATE chamados SET tecnico_id = NULL WHERE tecnico_id = 0";
...
$sql_delete = "DELETE FROM tecnicos WHERE id = 0";
```
Também vaza `$conn->error` na tela e contém um bloco de texto solto em Markdown colado no meio do `.php` (evidência de copy-paste de chat/IA nunca limpo antes de ir para produção).
**Status: [x]** Corrigido — movido para `_REMOVER_DO_SERVIDOR/admin/`. **Pendente:** apagar do servidor de produção via FTP/painel.

### S4b. `config/bandoDeDados/criar_usuarios_teste.sql` — cria contas de admin com senha fraca e conhecida (achado novo, fora do escopo original da varredura)
Script SQL (não `.php`, por isso não apareceu na varredura de código) que insere um cliente de teste e **3 técnicos**, incluindo `2026ADM001` (senha `admin123`) e `2026A002` (senha `admin456`) — a mesma matrícula `2026A002` citada em C10 (`docs/AUDITORIA_ACHADOS.md`) como exemplo real de escalação de privilégio via regex. O hash bcrypt é reaproveitado entre as 4 contas. Se este script já rodou contra o banco de produção, essas credenciais fracas e públicas (documentadas em texto puro no próprio script) estão ativas hoje.
**Status: [x]** Corrigido — movido para `_REMOVER_DO_SERVIDOR/config/bandoDeDados/`. **Pendente ação fora do código:** verificar se as matrículas `2026ADM001` e `2026A002` existem no banco de produção; se existirem, forçar troca de senha ou desativar essas contas imediatamente.

### S5. `cliente/atualizarChamado.php` — UPDATE de qualquer chamado sem autenticação e sem checar dono (IDOR + acesso anônimo)
`cliente/atualizarChamado.php:1-31` (e as 2 cópias mortas em `assets/cliente/` e `apresenta_escritorius/assets/cliente/`):
```php
include_once '../config/bandoDeDados/conexao.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $descricao = $_POST['descricao'];
    ...
    $sql = "UPDATE chamados SET status = ?, descricao = ? WHERE id = ?";
```
Sem `session_start()`, sem checagem de `cliente_id` no `WHERE`. Qualquer visitante não autenticado altera status/descrição de **qualquer chamado de qualquer cliente**, incluindo marcar como resolvido/cancelado.
**Status: [ ]** Pendente — adicionar auth + `AND cliente_id = ?` no WHERE.

### S6. API de JWT do Super Admin sem nenhuma autenticação — geração/revogação/enumeração de tokens de tenant por qualquer um
`admin/super_admin/api/bootstrap.php` (base de `api/jwt/generate.php`, `revoke.php`, `list.php`, `stats.php`, `validate.php`, e do roteador `api_jwt.php`):
```php
header('Access-Control-Allow-Origin: *');
...
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
...
$jwtHandler = new JWTHandler($db);   // nenhuma checagem de sessão/API key antes disso
```
Nenhum dos 5 endpoints tem qualquer verificação de sessão, API secret, ou header de autorização. Em teoria: `POST /admin/super_admin/api/jwt/generate` cria um tenant e devolve um JWT válido por 1 ano de graça; `list.php?tenant_id=X` enumera tokens de qualquer tenant; `revoke.php` derruba acesso de um cliente pagante.

**Nota importante confirmada nesta auditoria:** `bootstrap.php:46` instancia `new JWTHandler($db)`, mas a classe real definida em `admin/super_admin/core/JWTHandler.php:16` é `JWTHandlerV2`, não `JWTHandler`. Isso significa que hoje esse `new JWTHandler(...)` provavelmente lança exceção, capturada pelo `catch (Exception $e)` do próprio `bootstrap.php`, retornando erro 500 — **o endpoint pode já estar inoperante na prática, não é exploração ativa neste exato momento.** Isso não reduz a gravidade do achado: ao corrigir o nome da classe (bug funcional separado), a ausência de autenticação passa a ser imediatamente explorável. **A correção da autenticação deve ser feita antes ou junto da correção do nome da classe, nunca depois.**
**Status: [x]** Corrigido — `bootstrap.php` e `api_jwt.php` (os dois pontos de entrada, cobrindo os 5 endpoints em `jwt/` + o roteador legado) agora exigem header `X-Api-Key` batendo com `SUPERADMIN_API_MASTER_KEY` do `.env`, verificado com `hash_equals()`; se a variável não estiver configurada, a API responde 503 em vez de operar sem proteção. Isso é a autenticação mínima, feita **antes** de tocar no bug de nome de classe (`JWTHandler` → `JWTHandlerV2`), como recomendado. **Pendente:** corrigir o bug de classe separadamente (é funcional, não é regressão de segurança agora que a auth está no lugar) e distribuir a chave `SUPERADMIN_API_MASTER_KEY` de forma segura aos consumidores legítimos da API.

### S7. Secret JWT hardcoded no código-fonte (StyleManager API e módulo social)
`api/v1/stylemanager/config/clientes.php:23`:
```php
'jwt_secret' => 'stylemanager_dev_secret_2026_CHANGE_IN_PRODUCTION',
```
Fallback duplicado em `api/v1/stylemanager/config/jwt.php:20-22` e padrão repetido em `social/api/helpers/JWT.php:6` (`env('JWT_SECRET', 'change_me_in_production')`). Se esses valores forem os efetivamente carregados em runtime (não há indicação de override em produção), qualquer pessoa com acesso ao código-fonte pode forjar localmente um JWT válido com privilégio de admin.
**Status: [x]** Corrigido — os 3 pontos agora exigem a variável de ambiente (`STYLEMANAGER_JWT_SECRET` / `JWT_SECRET`) e lançam erro se não estiver configurada, sem fallback hardcoded. **Pendente ação fora do código:** o valor migrado para `.env` local ainda é o mesmo `stylemanager_dev_secret_2026_CHANGE_IN_PRODUCTION` que estava no código-fonte (portanto potencialmente já exposto) — gerar um secret novo e forte antes de aplicar em produção.

### S7b. Fallback fraco de `APP_KEY` para criptografia de credenciais (StyleManager suporte) — achado novo
`StyleManager/suporte/app/Services/{GeradorPacoteService,ProvisionamentoService,SincronizacaoPlanoService}.php` e `app/Controllers/admin/ProvisionamentoController.php` (5 ocorrências): `DotEnv::get('APP_KEY', 'stylemanager_key')` — a chave usada para cifrar (AES-256-CBC) a senha do banco de dados de cada salão/cliente tinha um fallback previsível e público (`'stylemanager_key'`) caso `APP_KEY` não estivesse definido no `.env`. Equivalia a não cifrar nada, para qualquer instalação sem essa variável configurada.
**Status: [x]** Corrigido — os 5 pontos agora exigem `APP_KEY` via `.env` e lançam exceção se ausente, sem fallback. **Pendente ação fora do código:** confirmar que `APP_KEY` está configurado em produção antes do próximo deploy (senão as rotinas de provisionamento/encriptação de credenciais de tenant param de funcionar, por design — é a troca correta: falhar de forma visível em vez de operar inseguro silenciosamente).

### S8. SQL Injection real em `despesas/processar_instalacao.php`
`despesas/processar_instalacao.php:31-32`:
```php
$db_name = $_POST['db_name'] ?? 'despesas_db';
...
$conn->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->exec("USE $db_name");
```
`$db_name` vem direto de `$_POST`, sem sanitização, concatenado cru em `CREATE DATABASE`/`USE`. O guard de proteção (`despesas/install.php:11`, só recusa se existir `config/.installed`) é fraco — se esse arquivo de marcação nunca foi criado ou foi apagado, `install.php`/`processar_instalacao.php` continuam publicamente acessíveis e reexploráveis em produção. O mesmo arquivo também reescreve `config/database.php` com `$db_host`/`$db_user`/`$db_pass` de input não validado.
**Status: [x]** Corrigido nesta pasta de trabalho — `despesas/config/.installed` criado (bloqueia o instalador mesmo se os arquivos existissem) e `install.php`/`processar_instalacao.php` movidos para `_REMOVER_DO_SERVIDOR/despesas/`. **Pendente ação fora do código:** confirmar em produção se `despesas/config/.installed` existe; se não, replicar a mesma correção lá (criar o arquivo e apagar os dois scripts do servidor via FTP/painel).

### S4c. Script de debug com hash bcrypt real hardcoded, confirmando a conta `teste`/`senha` do backdoor S1 — achado novo
`app/Views/superadmin/auth/verificar_senha.php` (arquivo inteiro) — sem autenticação nenhuma, imprime na tela um hash bcrypt real (`$hash_do_banco`) que corresponde à senha `'senha'`, e testa `password_verify()` publicamente. É quase certamente o script usado para gerar o hash da conta `teste`/`senha` que motivou o backdoor S1 — evidência de que essa conta existiu de fato no banco (`equipe_netonerd`), não só no código.
**Status: [x]** Corrigido — movido para `_REMOVER_DO_SERVIDOR/app/Views/superadmin/auth/`. **Pendente ação fora do código:** já que o hash confirma que a conta `teste`/`senha` provavelmente existe em `equipe_netonerd` no banco de produção, verificar e remover/desativar essa conta (relacionado a S1).

### S7d. Senha de app do Gmail pessoal em comentário de código morto (`app/Views/superadmin/pages/handle_adicionar_usuario.php`) — achado novo
Linha 106 (dentro de bloco totalmente comentado, código morto/rascunho): `$mail->Password = '[REDACTED — senha de app Gmail real, 16 caracteres, ver histórico de correção]';` — senha de app do Gmail de `rondi.rio@gmail.com` em texto puro. Mesma classe de achado já visto em `StyleManager/suporte/.env` (S24), mas aqui dentro do próprio código PHP versionado, não em `.env`.
**Status: [ ]** Pendente — revogar essa senha de app específica no Google Account (mesma recomendação de S24) e remover o bloco morto do arquivo (ligado à decisão maior de BE11 sobre a árvore `app/`).

### S7c. Senha SMTP de produção real como valor default hardcoded (`config/config.php`) — achado novo
```php
'password' => self::get('MAIL_PASSWORD', 'NetoNerd@#$234'),
```
Mesmo com `.env` configurado corretamente (o que já era o caso), o valor default embutido no código expõe a senha real da conta `contato@netonerd.com.br` para qualquer pessoa que leia o arquivo — usada por pelo menos 2 sistemas de produção (NetoNerd ITSM e StyleManager, conforme já observado em `docs/AUDITORIA_ACHADOS.md`).
**Status: [x]** Corrigido — default trocado para string vazia; o valor real só existe no `.env` (fora do controle de versão). **Pendente ação fora do código:** como a senha já esteve exposta no código-fonte versionado, considerar trocar a senha da conta de e-mail.

### S8b. Senha de banco de produção real hardcoded e commitada no git (`despesas/config/database.php`) — achado novo
```php
define('DB_PASS', '=0$xVm32iR1');
```
Credencial real do banco `u478690921_despesas` (Hostinger), commitada no histórico do git (confirmado no commit `ec79493`). Mesmo padrão em `admin/super_admin/config/database.php` (usuário `root` sem senha) e `app/Views/superadmin/data_base/conection.php` (idem).
**Status: [x]** Corrigido — as 3 conexões agora leem host/nome/usuário/senha do `.env` (`DESPESAS_DB_*`, `SUPERADMIN_API_DB_*`, `SUPERADMIN_DB_*`), sem valor hardcoded no `.php`. **Pendente ação fora do código:** a senha `=0$xVm32iR1` já está no histórico do git — rotacionar essa credencial na Hostinger é obrigatório, mudar só no código não invalida o que já vazou no histórico.

### S9. Upload de foto/logo sem validação nenhuma de extensão/MIME — RCE via webshell (4 tenants StyleManager)
`StyleManager/apresenta_stylemenager/profissional/handle_upload_foto.php:12-16` (idêntico em `barbeariatheclub/`, `barbeariaviana/`, `Excellence-Barbear-House/`):
```php
$ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
$nome = bin2hex(random_bytes(16)) . '.' . $ext;
$caminho = "../assets/img/mural/$nome";
if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
```
Sem lista branca de extensão, sem checagem de MIME/magic bytes. Um profissional autenticado (perfil de baixo privilégio) pode subir `shell.php` renomeado, salvo como `<hex>.php` executável em pasta pública sem `.htaccess` bloqueando PHP. Mesmo padrão em `admin/configuracoes.php:39-43` (upload de logo do tenant), acessível ao admin do salão-cliente.
**Status: [x]** Corrigido — `handle_upload_foto.php` e `admin/configuracoes.php` (upload de logo) nas 4 instâncias agora validam via `finfo`/magic bytes com lista branca de extensão (jpg/png/webp/gif, +svg para logo); `.htaccess` criado em `assets/img/` das 4 instâncias desabilitando execução de PHP (herda para `assets/img/mural/` e `assets/img/avatars/`).

### S10. `.env` de produção reais expostos publicamente em 4 subpastas do StyleManager (sem `.htaccess`)
Confirmado que estas pastas são servidas publicamente e **não têm `.htaccess`** bloqueando dotfiles (diferente da raiz do repo, já corrigida em C1 de `AUDITORIA_ACHADOS.md`):
- `StyleManager/apresenta_stylemenager/.env`
- `StyleManager/barbeariatheclub/.env`
- `StyleManager/barbeariaviana/.env`
- `StyleManager/Excellence-Barbear-House/.env`

Cada uma com credenciais de banco e SMTP reais e **distintas** por tenant (`DB_PASS`, `MAIL_PASS` diferentes em cada uma). `GET https://netonerd.com.br/StyleManager/.../. env` retornaria o arquivo em texto puro.
**Status: [ ]** Pendente — replicar o `.htaccess` de bloqueio de dotfiles (mesmo padrão de C1) nas 4 subpastas.

### S11. IDOR financeiro: usuário autenticado pode editar despesa de outro usuário
`despesas/classes/Despesa.php:83-117` (`atualizar()`), chamado de `despesas/editar.php:66` — único método da classe que **não** filtra por `usuario_id` (diferente de `marcarComoPago()`, `deletar()`, `buscarPorId()`, que aceitam `$usuarioId` opcional e checam). O `GET` de `editar.php` valida posse corretamente, mas o `POST` de gravação usa só `$_GET['id']` cru.
**Cenário:** usuário A obtém CSRF token válido na própria sessão (abrindo qualquer chamado seu) e envia POST manual para `editar.php?id=<id_de_outro_usuario>` reaproveitando o mesmo token (o CSRF check não varia por `id`). O UPDATE roda sem checar dono, alterando valor/vencimento/status/dados de boleto de outro usuário.
**Status: [ ]** Pendente — adicionar filtro `usuario_id` em `atualizar()`, igual ao padrão já usado nos outros métodos da mesma classe.

---

## ALTO

### S12. Rate limiting de login contornável (técnico) e inexistente (cliente)
`controller/valida_loginTecnico.php:30-55` guarda o contador de tentativas em `$_SESSION`, não por IP/banco — um atacante que descarte o cookie de sessão a cada tentativa (padrão em scripts de brute force) nunca é bloqueado. Existe implementação correta e não usada em `config/config_systens/auth_system.php:55-69` (consulta `login_attempts` por IP no banco), mas é código morto — nenhum fluxo de login real a chama. `controller/valida_login.php` (login de clientes) não tem nenhum mecanismo de bloqueio, nem o frágil.
**Status: [x]** Corrigido em 2026-07-14 — em vez de migrar os 2 logins para a classe `AuthSystem` inteira (reescreveria todo o fluxo de sessão/hash), foram extraídas 3 funções reutilizáveis para `controller/auth_middleware.php`: `garantirTabelaLoginAttempts()`, `isLoginBloqueado($conn, $identificador, $tipo_usuario)` e `registrarTentativaLogin($conn, $identificador, $tipo_usuario, $sucesso)`, usando a mesma tabela `login_attempts` (por identificador **e** IP, banco de dados, não sessão). Aplicadas em `valida_login.php` e `valida_loginTecnico.php`, que perderam o contador em `$_SESSION`. **Achado extra durante o teste**: a query original calculava a janela de tempo com `date()` do PHP, mas o servidor PHP e o MySQL locais rodam em fusos horários diferentes (~5h de diferença) — isso fazia o bloqueio nunca disparar (a janela calculada no PHP ficava no futuro em relação aos timestamps gravados pelo MySQL). Corrigido usando `NOW() - INTERVAL ? SECOND` calculado inteiramente no SQL, eliminando a dependência de sincronismo de relógio entre os dois serviços — relevante também para produção, onde o fuso do PHP/MySQL pode divergir de forma diferente. Testado via `curl`: 5 tentativas erradas com sessão nova a cada tentativa (cookie descartado) bloqueiam a 6ª mesmo com senha correta, tanto no login de técnico quanto no de cliente.

### S13. XSS Armazenado — painel Super Admin, dado de cliente injetado via `innerHTML` sem escape
`app/Views/superadmin/dashboard.php:468-498,510-563` — `cleanVal()` só troca `null`/`undefined` por `''`, não escapa HTML. Campos como `nome_barbearia` e `observacoes` (gravados sem sanitização em `processa_cliente.php:18-28`) são interpolados via template literal em `innerHTML` nos modais "Detalhes" e "Editar". Um operador cadastrando um cliente com `observacoes` contendo `<img src=x onerror=...>` compromete a sessão de qualquer super admin que abra os detalhes desse cliente.
**Status: [x]** Corrigido em 2026-07-14 — `cleanVal()` agora usa `document.createElement('div').textContent = val` e retorna `.innerHTML` (escape real via DOM, não substitui `innerHTML` por `textContent` porque a string monta HTML estrutural junto com o dado). Achado extra no mesmo arquivo: `data-cliente='<?php echo json_encode($cliente); ?>'` (linhas 277 e 281) quebrava se algum campo do cliente contivesse aspas simples — corrigido para `htmlspecialchars(json_encode($cliente), ENT_QUOTES)`.

### S14. XSS Armazenado — busca de cliente no agendamento centralizado (StyleManager, 4 tenants)
`StyleManager/*/admin/agendar_centralizado.php:405-414` (4 cópias idênticas) — `nome`/`telefone`/`email` de `vw_clientes_unificado` (inclui auto-cadastro de cliente) injetados via `innerHTML` sem escape; o mesmo `nome` também quebra o atributo `onclick='...${JSON.stringify(cliente)}'` se contiver aspas simples. Cliente com nome malicioso compromete o navegador de qualquer admin/recepcionista que busque por ele.
**Status: [x]** Corrigido em 2026-07-14 nas 4 cópias (`barbeariatheclub`, `barbeariaviana`, `apresenta_stylemenager`, `Excellence-Barbear-House`) — função `escapeHtml()` nova (mesmo padrão DOM de S13) aplicada a `nome`/`telefone`/`email`/`tipo` antes de interpolar. O `onclick='selecionarCliente(${JSON.stringify(cliente)})'` inline (frágil a aspas simples no nome) foi eliminado por completo: a lista de resultados agora é montada com `createElement`/`appendChild` e o clique é um `addEventListener` real, em vez de serializar o objeto cliente dentro de um atributo HTML. **Nota:** a pasta `StyleManager/` foi removida do repositório pelo usuário logo depois desta correção (4 tenants migrados para VPS própria) — ver S25/S27 para o mesmo aviso.

### S15. XSS Armazenado — Ordem de Serviço gerada a partir de chamado
`admin/gerar_ordem_servico.php:163,176,193,287` — `cliente_nome`/`cliente_telefone`/`cliente_email`/`descricao` impressos em `value="<?= ... ?>"` sem `htmlspecialchars` (a linha 127 do mesmo arquivo já usa `htmlspecialchars` no protocolo — confirma que é omissão pontual). Cliente que abre chamado com dado malicioso compromete o admin que gerar OS a partir daquele chamado.
**Status: [x]** Corrigido em 2026-07-14 — `htmlspecialchars()` aplicado aos 4 campos (`cliente_nome`, `cliente_telefone`, `cliente_email`, `descricao`/`problema_relatado`). `chamado_id` (linha 126) já era `intval()`, não precisava de correção.

### S16. XSS Armazenado — e-mails HTML de contato e de licença sem escape
`publics/processa_contato.php:126-131,169-170` e `admin/processar_licenca.php:127` — `$nome`/`$email`/`$assunto`/`$mensagem` interpolados direto em corpo de e-mail `isHTML(true)`, sem `htmlspecialchars`. Cliente de e-mail moderno renderiza HTML/CSS — payload como `<img onerror=...>` ou link disfarçado chega à equipe interna (e ao próprio remetente).
**Status: [x]** Corrigido em 2026-07-14 — `publics/processa_contato.php`: variáveis `_html` separadas (`$nome_html`, `$email_html`, `$assunto_html`, `$mensagem_html` com `nl2br(htmlspecialchars(...))`) usadas nos 2 e-mails (equipe + confirmação ao cliente), mantendo as variáveis originais sem escape para o `INSERT` no banco. `admin/processar_licenca.php`: `$produto_nome_html`/`$cliente_nome_html` escapados antes de montar o corpo do e-mail de licença gerada (a API key em si não foi escapada — é gerada pelo sistema, não input de usuário).

### S17. XSS Refletido — filtros de data em relatórios (e SQLi associada)
`admin/relatorios.php:16-17,71,77` e `admin/relatorio_tecnico.php:37-38,179,185` — `$_GET['data_inicio']`/`data_fim` impressos direto em `value=""` sem escape. **Agravante:** os mesmos valores são concatenados diretamente na query SQL (linhas 29/41 de `relatorios.php`) sem `bind_param` — há SQL injection associada a este achado, não coberta em detalhe pelo agente de SQLi por estar fora do padrão típico de busca (concatenação em filtro de relatório, não em WHERE de listagem simples). Recomenda-se tratar como crítico combinado (XSS + SQLi no mesmo ponto).
**Status: [x]** Corrigido — `admin/relatorios.php`: as 2 queries que interpolavam `$data_inicio`/`$data_fim` diretamente agora usam `prepare()`/`bind_param()`. `admin/relatorio_tecnico.php`: as 3 queries já usavam `bind_param()` (a auditoria original citou SQLi aqui por semelhança de padrão com `relatorios.php`, mas na leitura direta do arquivo não havia interpolação crua — só o XSS era real). Em ambos os arquivos, `$data_inicio`/`$data_fim` agora passam por validação de formato `Y-m-d` (`checkdate()`) antes de qualquer uso, e o output em `value="..."` usa `htmlspecialchars()`.

### S18. XSS Refletido — `id` de chamado sem escape em 5 cópias de "Editar Chamado"
`cliente/editar_chamado.php:17,46` e as 4 cópias (`app/Views/chamados/editar.php`, `apresenta_escritorius/assets/cliente/`, `assets/cliente/`, `$BACKUP_DIR/`) — `$_GET['id']` impresso em `value="<?= $chamado_id ?>"` sem `intval`/escape (diferente de `titulo`/`descricao` no mesmo arquivo, que já são escapados corretamente).
**Status: [ ]** Pendente — `intval()` no `id` antes de usar, ou `htmlspecialchars()` no output.

### S19. CORS refletido com credentials habilitado (`social/api/config/cors.php`)
```php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
...
header('Access-Control-Allow-Credentials: true');
```
Reflete qualquer origem e permite credentials — antipadrão clássico que anula a proteção do CORS. Mitigado parcialmente porque a API usa Bearer token (não cookie automático), mas deveria usar allowlist explícita de domínios.
**Status: [x]** Corrigido em 2026-07-14 — `setCorsHeaders()` agora lê `APP_CORS_ORIGINS` do `.env` (lista separada por vírgula) e só envia `Access-Control-Allow-Origin`/`Allow-Credentials` se a `Origin` da requisição estiver na lista; origem fora da allowlist não recebe o header (navegador bloqueia a resposta). `social/api/.env.example` documentado com a nova variável. Apps mobile (Bearer token, sem `Origin`) não são afetados.

### S20. Mensagens de erro de banco vazadas em `admin/dashboard.php`
`admin/dashboard.php:289` — `echo "Erro na consulta: " . htmlspecialchars($conn->error)`, vazamento direto de `mysqli->error` mesmo com `display_errors` desligado, revelando estrutura de tabela/query em caso de falha.
**Status: [x]** Corrigido em 2026-07-14 — erro agora vai para `error_log()`, tela mostra mensagem genérica ("Não foi possível carregar a lista de técnicos...").

---

## MÉDIO

### S21. Cookie de sessão sem HttpOnly/Secure/SameSite explícitos no fluxo principal
Nenhum arquivo do fluxo principal (`controller/valida_login.php`, `valida_loginTecnico.php`, `admin/*`, `cliente/*`, `tecnico/*`) chama `session_set_cookie_params()`. As únicas ocorrências corretas ficam em `StyleManager/suporte/app/Core/Session.php` e `controller/logout.php` — fora do sistema principal. Depende inteiramente da configuração padrão do `php.ini` de produção.
**Status: [x]** Corrigido em 2026-07-14 — `configurarCookieSessaoSegura()` (nova, em `controller/auth_middleware.php`) chama `session_set_cookie_params()` com `httponly` (lido de `SESSION_HTTPONLY` do `.env`), `secure` (lido de `SESSION_SECURE`) e `samesite=Lax`, sempre antes do `session_start()` central do middleware. **Achado à parte, mais amplo que o item original:** um agente de busca confirmou que **30 arquivos** do fluxo principal chamavam `session_start()` próprio *antes* de incluir `auth_middleware.php` — a sessão já nascia com os parâmetros padrão do PHP antes do middleware ter chance de configurá-la, anulando a correção se aplicada só ali. `session_start()` redundante removido dos 30 arquivos (incluindo `valida_login.php`, `valida_loginTecnico.php`, `validador_acesso.php`, que chamavam antes até de incluir o middleware). `admin/dashboard.php` não incluía `auth_middleware.php` em lugar nenhum e checava admin via `$_SESSION['tipo_usuario']` direto — passou a usar `requireAdmin()` como o resto do sistema. Testado via `curl`: `Set-Cookie` agora traz `HttpOnly; SameSite=Lax` em login, páginas admin, técnico e cliente.

### S22. Token JWT aceito via query string
`admin/super_admin/JWTMiddleware.php:93-96` — `if (isset($_GET['token'])) return $_GET['token'];`, sem trava de ambiente. Token de 1 ano de validade pode vazar via logs de acesso, histórico de navegador, cache de proxy, header `Referer`.
**Status: [x]** Corrigido em 2026-07-14 — `extractTokenFromRequest()` só aceita `$_GET['token']` se `isDevelopmentEnvironment()` (nova, checa `APP_ENV` via `getenv()`/`$_ENV`, não depende de nenhuma classe `Config` específica já que o middleware é reusado por outros projetos) retornar `true`.

### S23. Script de manutenção esquecido em produção — `despesas/fix_multiuser.php`
Sem `session_start()`/auth, executa `UPDATE despesas SET usuario_id = 1 WHERE usuario_id = 0 OR usuario_id IS NULL` ao ser acessado via GET. O próprio comentário do arquivo já diz "DELETE este arquivo".
**Status: [ ]** Pendente — remover do servidor.

### S24. Credencial pessoal (Gmail) comentada em `.env` de produção
`StyleManager/suporte/.env:39-42` — senha de app do Gmail pessoal (`rondi.rio@gmail.com`) comentada, mas presente em texto puro. Diferente do SMTP de teste do StyleManager antigo já descartado na primeira auditoria — este é um `.env` diferente.
**Status: [ ]** Pendente — revogar a senha de app no Google Account e remover do arquivo, mesmo comentada.

### S25. CSRF em ações destrutivas via GET — `StyleManager/*/admin/manage_recommendations.php` (4 tenants)
```php
if ($_GET['aprovar'] ?? '') { $pdo->prepare("UPDATE recomendacoes SET aprovado = 1 WHERE id = ?")->execute([$_GET['aprovar']]); }
if ($_GET['rejeitar'] ?? '') { $pdo->prepare("DELETE FROM recomendacoes WHERE id = ?")->execute([$_GET['rejeitar']]); }
```
Mesmo padrão do C5 já documentado (exclusão via GET sem token), replicado em módulo não coberto pela primeira auditoria.
**Status: [x]** Corrigido em 2026-07-14 nas 4 cópias (`barbeariatheclub`, `barbeariaviana`, `apresenta_stylemenager`, `Excellence-Barbear-House`), usando o `gerar_csrf_token()`/`verificar_csrf_token()` que já existia em `includes/auth.php` desse projeto (não precisou criar nada novo, só não estava sendo usado aqui) — ações convertidas de link GET para form POST com token. **Nota:** o usuário removeu a pasta `StyleManager/` do repositório logo depois desta correção (os 4 tenants migraram para VPS própria e não fazia sentido continuar aqui) — a correção ficou registrada no histórico de trabalho, mas o código já não existe mais neste projeto.

### S26. Mercado Pago — token de produção real usado mesmo com `MP_SANDBOX=true`
`StyleManager/suporte/.env:24-27` — `MP_ACCESS_TOKEN` com prefixo `APP_USR-` (produção real, não `TEST-`), apesar de `MP_SANDBOX=true`. Recomenda-se confirmar no código (`BoletoService.php`) se `MP_SANDBOX` de fato altera o endpoint chamado.
**Status: [ ]** Pendente — confirmar comportamento e rotacionar token/webhook secret independentemente.

### S27. Debug público expõe estrutura de banco (`test_horarios.php`, `diagnostico.php` — 4 tenants StyleManager)
`StyleManager/*/cliente/test_horarios.php` roda `SHOW TABLES`/`SHOW COLUMNS` (nomes fixos no código, não é SQLi) e imprime resultado via `print_r`, sem nenhuma checagem de acesso. Vazamento de reconhecimento de schema para um atacante.
**Status: [x]** Não se aplica mais em 2026-07-14 — o usuário removeu a pasta `StyleManager/` do repositório (4 tenants migrados para VPS própria, sem relação com esta atualização do NetoNerd). O achado deixa de existir junto com o código.

---

## BAIXO

### S28. Instalador de despesas (`despesas/install.php`) e utilitário `fix_multiuser.php` sem trava robusta
Ver S8/S23. Script de instalação/correção acessível publicamente é padrão de risco recorrente no módulo `despesas/`.
**Status: [ ]** Pendente — revisar todos os scripts de instalação/manutenção do módulo.

### S29. `CRON_SECRET_TOKEN`/`APP_KEY` em texto puro no `.env` do StyleManager suporte
`StyleManager/suporte/.env:17,46` — se o `.env` estiver acessível publicamente (a confirmar se essa subpasta específica está exposta como as 4 do S10), o cron pode ser disparado por terceiros.
**Status: [ ]** Pendente — confirmar exposição e rotacionar se necessário.

---

## Itens já cobertos por `docs/AUDITORIA_ACHADOS.md` (não repetidos aqui)

C1-C10, A1-A9, B1-B4, M1-M7 do documento original. Em especial: C10 (regex de matrícula → admin) é pré-condição direta de S2 acima — corrigir C10 sem corrigir S2 deixa a escalação de privilégio ainda alcançável, só que por um caminho diferente (criar o técnico "malicioso" via cadastro público em vez de já existir na base).
