# NetoNerd ITSM — Documentação da API

Este documento cobre duas categorias distintas de superfície de API do sistema:

1. **API pública** (`api/validar_chave.php`) — consumida por aplicativos/serviços externos ao ITSM (ex: apps móveis dos produtos do hub), autenticada por API key.
2. **Endpoints internos AJAX** — usados pelo próprio frontend do ITSM (`fetch()` a partir das telas de admin), autenticados por sessão de usuário logado, não pensados para consumo externo.

Não fazem parte deste documento: `admin/super_admin/**` (servidor de autenticação JWT central que atende múltiplos produtos do hub, não específico do ITSM — ver `admin/super_admin/JWTMiddleware.md`), `api/validar_licenca.php` (serviço de licenciamento genérico multi-produto), `api/v1/stylemanager/**`, `social/**`, `despesas/**` (sistemas separados hospedados no mesmo repositório).

---

## 1. API pública — `api/validar_chave.php`

Endpoint único, usado por aplicativos externos para obter as credenciais de conexão ao banco de dados de um cliente específico do hub NetoNerd (StyleManager, Escritorius, MyHealth, Despesas), a partir de uma API key emitida para esse cliente.

### Autenticação

A API key pode ser enviada por qualquer um destes 4 canais, checados nesta ordem de prioridade:

1. Header `Authorization: Bearer <chave>`
2. Header `X-API-Key: <chave>`
3. Corpo da requisição, em JSON: `{"api_key": "<chave>"}` (só em `POST`)
4. Query string: `?api_key=<chave>` (fallback para `GET`)

Além da chave em si, o endpoint exige que o **IP de origem da requisição** esteja na lista de IPs permitidos cadastrados para aquela chave (`api_keys.ip_permitido`, campo obrigatório desde a Fase 6 — uma chave sem nenhum IP cadastrado é sempre recusada, não existe "permitir qualquer IP").

### Requisição

```
POST /api/validar_chave.php
Content-Type: application/json
Authorization: Bearer <sua_api_key>
```

ou

```
GET /api/validar_chave.php?api_key=<sua_api_key>
```

`OPTIONS` é aceito e responde `200` vazio (CORS preflight).

### Resposta de sucesso — `200 OK`

```json
{
  "success": true,
  "message": "API key válida",
  "code": "API_KEY_VALID",
  "cliente": "Nome do Cliente",
  "database": {
    "host": "localhost",
    "name": "nome_do_banco",
    "user": "usuario_do_banco",
    "password": "senha_descriptografada",
    "port": 3306
  },
  "expira_em": "2027-01-01 00:00:00"
}
```

A senha do banco é armazenada criptografada com **AES-256-GCM** (`config/crypto.php`, chave `APP_SECRET_KEY` do `.env`) e descriptografada só neste ponto, na resposta ao chamador autenticado.

### Respostas de erro

| HTTP | `code` | Situação |
|---|---|---|
| 400 | `MISSING_API_KEY` | Nenhuma chave foi enviada por nenhum dos 4 canais |
| 401 | `INVALID_API_KEY` | Chave não existe no banco |
| 401 | `API_KEY_<STATUS>` | Chave existe mas não está `ativa` (ex: `API_KEY_REVOGADA`, `API_KEY_INATIVA`) — o `code` reflete o status literal da coluna |
| 401 | `API_KEY_EXPIRED` | `data_expiracao` já passou — a chave é automaticamente marcada como `revogada` no mesmo request |
| 403 | `IP_NOT_ALLOWED` | IP de origem não está na lista `ip_permitido` da chave (ou a chave não tem nenhum IP cadastrado) |
| 500 | `DECRYPT_ERROR` | Falha ao descriptografar a senha do banco armazenada |

Todos os erros seguem o formato `{"success": false, "error": "<mensagem>", "code": "<código>"}`.

### Efeitos colaterais

A cada chamada válida (200), `api_keys.ultimo_uso` é atualizado para `NOW()` e `api_keys.total_requisicoes` é incrementado em 1 — usado para exibir estatísticas de uso na tela `admin/api_keys.php`.

### Gerenciamento de chaves

Chaves são criadas/editadas/revogadas pela tela `admin/api_keys.php` (área admin do ITSM, requer `requireAdmin()`), que grava a senha do banco já criptografada antes de persistir. Ver `docs/resolucoesAuditoria.md` (item C8, Fase 6) para o histórico da migração de base64 para AES-256-GCM real.

---

## 2. Endpoints internos AJAX

Todos exigem sessão de usuário autenticado (via `controller/auth_middleware.php`) e não são pensados para consumo por terceiros — não têm CORS liberado, não têm versionamento, podem mudar de contrato sem aviso.

### `admin/buscar_clientes.php`

Autocomplete de busca de cliente por nome — usado em `admin/abrir_chamado_admin.php` e `admin/lgpd_titulares.php`.

- **Método:** `GET`
- **Auth:** `requireAdmin()`
- **Parâmetros:** `termo` (mínimo 3 caracteres — retorna `[]` se menor)
- **Resposta:** array de até 20 clientes, `[{"id": 1, "nome": "...", "email": "...", "telefone": "..."}, ...]`
- **Query:** `SELECT ... WHERE nome LIKE '%termo%' ORDER BY nome ASC LIMIT 20` — já usa `trim()` e correspondência parcial (não exige nome exato nem começo de string)

### `admin/buscar_cliente_ajax.php`

Busca um cliente específico por nome **e** telefone combinados — usado ao preencher formulários de chamado/OS para localizar automaticamente um cliente já cadastrado.

- **Método:** `POST`
- **Auth:** `requireAdmin()`
- **Parâmetros:** `nome`, `telefone` (ambos obrigatórios)
- **Resposta (encontrado):** `{"encontrado": true, "cliente": {"id", "nome", "telefone", "email", "endereco", "cpf"}}`
- **Resposta (não encontrado):** `{"encontrado": false}`
- **Nota:** compara telefone tanto formatado quanto só dígitos (`REPLACE` de parênteses/hífen/espaço), então funciona independente de como o telefone foi digitado.

### `admin/confirmar_senha_cpf.php`

Confirma a senha do admin logado antes de revelar um CPF mascarado na tela (proteção de dado sensível — ver `docs/resolucoesAuditoria.md`, item 3 da Fase 7 continuação, para o design completo do padrão de mascaramento).

- **Método:** `POST`
- **Auth:** `requireAdmin()` + CSRF (`csrf_token` no corpo, validado via `isValidCsrfToken()`)
- **Parâmetros:** `password`
- **Resposta (sucesso):** `{"ok": true}`
- **Resposta (erro):** `{"ok": false, "message": "..."}` — nunca retorna o CPF; o valor real já está no DOM da página (mascarado via CSS/JS), esta chamada só confirma a senha e "libera" a exibição no client-side.

### `admin/exportar_dados_cliente.php`

Exportação de dados de um titular para portabilidade LGPD (art. 18) — força download de arquivo em vez de resposta AJAX comum.

- **Método:** `GET`
- **Auth:** `requireAdmin()`
- **Parâmetros:** `cliente_id`
- **Resposta:** arquivo JSON (`Content-Disposition: attachment`) com os dados do cliente, chamados e ordens de serviço vinculados — ver `controller/lgpd.php::exportarDadosCliente()`.
- **Efeito colateral:** registra a exportação em `logs_sistema` via `registrarLogSistema()`.
- Em caso de `cliente_id` inválido ou não encontrado, redireciona (`Location:`) para `admin/lgpd_titulares.php` em vez de retornar JSON de erro.

### `controller/processa_cadastro.php`

Processa o autocadastro público de um novo cliente (formulário de registro do site).

- **Método:** `POST`
- **Auth:** nenhuma (é o próprio endpoint de criação de conta) — só exige CSRF token válido
- **Parâmetros:** `nome`, `email`, `senha`, `telefone`, `endereco`, `complemento`, `cep`
- **Resposta (sucesso):** redireciona (`Location:`) para a tela de login com mensagem de sucesso
- **Resposta (erro):** JSON com a mensagem de erro (email já cadastrado, senha fraca, etc.)

---

## Convenções gerais

- Todas as queries usam **prepared statements** (`mysqli::prepare()` + `bind_param()`) — nenhum endpoint interpola valor de request direto em SQL.
- Endpoints que recebem CSRF token o fazem via `controller/auth_middleware.php::isValidCsrfToken()` (comparação com `hash_equals()`).
- Nenhum endpoint interno usa rate limiting próprio — a proteção contra abuso do sistema como um todo vem do rate limiting de login (Fase 4) e da exigência de sessão autenticada em quase todos os endpoints (exceto o de cadastro público).
