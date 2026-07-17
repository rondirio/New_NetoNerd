# NetoNerd ITSM — Auditoria de Qualidade de Backend

Auditoria de arquitetura, manutenibilidade e correção funcional do backend, separada da auditoria de segurança (`docs/AUDITORIA_SEGURANCA.md`) e da original (`docs/AUDITORIA_ACHADOS.md`). Não repete achados já documentados ali (A1-A9, M1, M4, M6 do documento original tratam de temas próximos — citados como contexto quando relevante).

Mesma convenção: `[ ]` pendente, `[x]` corrigido, `[~]` parcial.

---

## CRÍTICO

### BE1. Abertura de chamado pelo cliente não é atômica — race condition real na geração de protocolo
`cliente/registra_chamado.php:40-47` (e as 4 cópias: `admin/abrir_chamado_admin.php:73-79`, `app/Views/auth/registra_chamado.php`, `assets/cliente/registra_chamado.php`, `apresenta_escritorius/assets/cliente/registra_chamado.php`):
```php
$query = "SELECT MAX(protocolo) as ultimo_protocolo FROM chamados";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$ultimo = $row['ultimo_protocolo'] ? intval(substr($row['ultimo_protocolo'], 4)) : 0;
$protocolo = date('Y') . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
```
Sem `begin_transaction()`/lock. Se dois clientes abrirem chamado ao mesmo tempo, ambos leem o mesmo `MAX(protocolo)` antes de qualquer INSERT — podem gerar o **mesmo protocolo**. A coluna `protocolo` não tem `UNIQUE` no schema. Além disso, o loop de INSERT de anexos (linhas 96-129) não é atômico com o INSERT do chamado principal — falha parcial deixa chamado sem alguns anexos, sem avisar o cliente.
**Status: [ ]** Pendente — envolver geração de protocolo + INSERT do chamado + INSERT de anexos em uma única transação, e adicionar `UNIQUE` em `chamados.protocolo` como rede de segurança. Centralizar a lógica numa função única (ver BE-D2) em vez de corrigir 5 cópias separadamente.

### BE2. Duas funções `isAdmin()` incompatíveis coexistindo — fonte estrutural do C10 já documentado
- `controller/auth_middleware.php:30` — `isAdmin()` sem parâmetros, checa `$_SESSION['tipo'] === 'admin'`.
- `controller/valida_loginTecnico.php:20` — `isAdmin($matricula)` com parâmetro, checa regex na matrícula (é o C10 documentado em `AUDITORIA_ACHADOS.md`).
- A mesma regex ainda aparece copiada por valor pela terceira vez em `admin/processar_atribuicao.php:54`.

Hoje não colidem porque nenhum arquivo inclui os dois ao mesmo tempo, mas é uma armadilha: mesmo nome, assinaturas incompatíveis — um refactor razoável (unificar os dois `require` de autenticação) quebra com `Fatal error: Cannot redeclare isAdmin()`. E enquanto a regra de "quem é admin" estiver replicada em 3 lugares com nomes diferentes, corrigir C10 em um deles e esquecer os outros dois é o tipo de erro que já aconteceu.
**Status: [ ]** Pendente — unificar em uma única função com um nome único, chamada pelos 3 pontos, como parte da correção de C10 (Fase 4 do plano de correção).

### BE3. `tecnicos.Ativo` e `tecnicos.status_tecnico` — dois controles redundantes de "técnico ativo" que dessincronizam
Duas colunas booleanas-de-fato na mesma tabela, mantidas por conjuntos de arquivos diferentes:
- Só grava/lê `Ativo`: `admin/atribuir_chamados.php`, `admin/gerar_ordem_servico.php`, `admin/listar_ordens_servico.php`, `admin/processar_atribuicao.php`.
- Só grava/lê `status_tecnico`: `admin/abrir_chamado_admin.php`, `admin/apresenta_tecnicos.php`, `admin/chamados_ativos.php`, `admin/dashboard.php`, **`admin/editar_tecnico.php`** (a única tela de edição de técnico só grava `status_tecnico`, nunca `Ativo`), `admin/relatorio_tecnico.php`.

**Impacto concreto:** um admin "desativa" um técnico pela única tela disponível (`editar_tecnico.php`, que só muda `status_tecnico`). A coluna `Ativo` continua `1`. `atribuir_chamados.php` e `gerar_ordem_servico.php` (que filtram por `Ativo = 1`) continuam oferecendo esse técnico "inativo" para novos chamados, mesmo que `apresenta_tecnicos.php` e `dashboard.php` já o mostrem como "Inactive" na listagem.
**Status: [x]** Corrigido na Fase 5 — coluna `Ativo` removida de `tecnicos` (após sincronizar dados: `Ativo = 1` onde `status_tecnico = 'Active'`), `status_tecnico` é a única fonte de verdade agora. Atualizados os 5 pontos de leitura (`admin/atribuir_chamados.php` ×2, `admin/gerar_ordem_servico.php`, `admin/listar_ordens_servico.php`, `admin/processar_atribuicao.php`) e o INSERT em `admin/processa_adicionar_tecnico.php`. `admins.Ativo` é coluna própria de outra tabela, não faz parte deste achado.

---

## ALTO

### BE4. Card do dashboard sempre mostra zero — comparação de status incompatível com o ENUM real
`admin/dashboard.php:26-28` — `WHERE status = 'Aberto'` (capitalizado), mas o ENUM real é `enum('aberto','em andamento','pendente','resolvido','cancelado')` (minúsculo). A query não falha, só nunca bate — erro silencioso de lógica, sem log. "Total de Chamados Abertos" fica sempre zerado no dashboard do admin. Mesmo bug replicado em `app/Views/dashboard/home.php:90` (árvore morta, mas confirma que o bug é anterior à cópia).
**Status: [ ]** Pendente — corrigir para `status = 'aberto'`.

### BE5. Falta de transação em `fechar_chamado.php`/`adicionar_resposta.php` — status e histórico podem dessincronizar
`cliente/fechar_chamado.php:51-77` — `UPDATE chamados` seguido de `INSERT INTO historico_chamados` como duas operações soltas em autocommit. Se o INSERT falhar (já falha hoje por causa de A1/A7 documentados), o UPDATE pode ter sido commitado antes — chamado muda de status sem registro correspondente no histórico. Mesmo depois de corrigir A1/A7, a falta de transação continua sendo risco estrutural.
**Status: [ ]** Pendente — envolver em `begin_transaction()`/`commit()`/`rollback()`, seguindo o padrão já usado corretamente em `tecnico/processar_chamado.php` e `admin/processar_atribuicao.php`.

### BE6. `despesas/classes/Despesa.php::criarParceladas()` — N parcelas em loop sem transação
```php
for ($i = 1; $i <= $totalParcelas; $i++) {
    $this->adicionarParcelada($despesaParcela);  // INSERT individual, PDO autocommit
}
```
Sem `beginTransaction()`. Falha na parcela 7 de um parcelamento de 12x deixa 6 despesas órfãs no banco, sem forma automática de detecção — `resumoParcelamento()` reporta `total_parcelas` divergente da contagem real de linhas.
**Status: [x]** Corrigido na Fase 5 — loop inteiro envolvido em `beginTransaction()`/`commit()`/`rollBack()`.

### BE7. `EmailService` reimplementado do zero em múltiplos sistemas do mesmo repositório
`config/EmailService.php` (core + `cron/verificar_licencas.php`) e `despesas/classes/EmailService.php` configuram PHPMailer/SMTP e templates HTML de forma totalmente independente, com estrutura visual copiada e colada. Um bug de template corrigido em um não se propaga para o outro.
**Status: [ ]** Pendente — considerar unificar numa classe compartilhada (baixa prioridade, é dívida técnica, não bug ativo).

### BE8. Geração de protocolo copiada em 5 arquivos (ver também BE1)
Mesma lógica de formato de protocolo (`date('Y') . str_pad(...)`) duplicada em 5 pontos diferentes, incluindo 2 cópias fora do caminho óbvio de navegação (`assets/cliente/`, `apresenta_escritorius/assets/cliente/`) que provavelmente seriam esquecidas numa correção futura de formato.
**Status: [ ]** Pendente — centralizar em uma função `gerarProtocolo($conn)` única, resolvendo junto com BE1.

### BE9. `chamados.categoria_id` nunca populado pelo código — quebra proteção de exclusão e várias telas
Nenhum ponto de criação/edição de chamado grava `categoria_id` (só a coluna de texto livre `categoria`). Isso deixa sempre `NULL`, o que:
- Anula a proteção "categoria em uso, não pode excluir" em `admin/processar_categoria.php:99-110` (`COUNT(*) WHERE categoria_id = ?` sempre retorna 0).
- Zera as estatísticas de `admin/categorias.php` (`LEFT JOIN` nunca casa).
- Impede que `admin/chamados_ativos.php`, `admin/relatorio_tecnico.php`, `cliente/detalhe_chamado.php`, `tecnico/detalhes_chamado.php` exibam cor/ícone da categoria (JOIN com `categorias_chamado` nunca casa) — funcionalidade pronta no schema e no código de exibição, mas morta por falta de dado.
**Status: [ ]** Pendente — popular `categoria_id` nos pontos de criação/edição de chamado (mapear a string livre para o ID correspondente, ou migrar o formulário para usar `<select>` de categoria).

### BE10. Coluna `data_ultima_atualizacao` referenciada não existe (reforça A1/A7 do doc original)
Confirmado no schema: a coluna correta `data_atualizacao` já tem `ON UPDATE current_timestamp()` automático. O `UPDATE ... SET data_ultima_atualizacao = ...` em `cliente/fechar_chamado.php:56` e `adicionar_resposta.php:86` não só usa nome errado — mesmo corrigido, seria redundante (o campo já se atualiza sozinho). Deveria ser removido, não só renomeado.
**Status: [ ]** Pendente — remover a atribuição manual, não apenas corrigir o nome.

### BE11. Árvore `app/` inteira é uma 4ª cópia morta do sistema, publicamente acessível
Confirmado por busca exaustiva: nenhum arquivo fora de `app/` faz include de `app/Views/`, `app/Controllers/`, `app/Middleware/`, `app/Database/`. Dezenas de arquivos (login, cadastro, área de superadmin, mais de 15 arquivos só em `app/Views/superadmin/`) reproduzem o sistema com código potencialmente desatualizado — confirmado por diff que `app/Views/auth/registra_chamado.php` não tem a validação de MIME por magic bytes que a versão ativa em `cliente/` tem. Sem `.htaccess` bloqueando a pasta.
**Status: [ ]** Pendente — decidir entre remover (se confirmado 100% morto) ou bloquear acesso via `.htaccess` enquanto não é removido. É superfície de confusão de manutenção e de ataque adicional.

### BE12. Fragmentação de driver de banco — mysqli em quase todo o sistema, PDO isolado em `despesas/`
156 ocorrências de PDO, todas em `despesas/classes/`. Todo o resto usa mysqli orientado a objeto. Não é bug, mas significa que não existe camada de acesso a dados compartilhada — qualquer melhoria (retry, logging de query, query builder) precisa ser implementada duas vezes.
**Status: [ ]** Pendente — sem ação imediata necessária; considerar ao planejar qualquer refactor maior de acesso a dados.

### BE13. Arredondamento de parcelas não fecha com o valor total
`despesas/classes/Despesa.php:548,564` — `round($valorTotal / $totalParcelas, 2)` por parcela, sem ajustar a última parcela para fechar a diferença de centavos. Ex: R$100 em 3x → 33.33 × 3 = 99.99, R$0,01 nunca cobrado nem registrado.
**Status: [x]** Corrigido na Fase 5, junto com BE6 — última parcela agora absorve o resíduo (`valorTotal - soma das parcelas anteriores`). Testado isoladamente: R$100 em 3x → 33.33 + 33.33 + 33.34 = 100.00 exato.

### BE14. Cron sem proteção contra execução concorrente
Nem `cron/verificar_licencas.php` nem `despesas/cron_gerar_recorrentes.php`/`web_cron.php` têm lock (`GET_LOCK()` do MySQL ou `flock`). Duas execuções simultâneas (crontab + disparo manual, ou timeout do agendamento anterior) enviam emails duplicados de licença (`verificar_licencas.php`) ou criam despesas recorrentes duplicadas (`despesas/`, sem `UNIQUE` de banco como rede de segurança).
**Status: [x]** Corrigido na Fase 5 — `GET_LOCK`/`RELEASE_LOCK` adicionado em `cron/verificar_licencas.php` e `despesas/cron_gerar_recorrentes.php` (`web_cron.php` herda automaticamente, é só um wrapper que inclui o outro arquivo), liberado via `register_shutdown_function` para ser robusto a erro fatal. `UNIQUE(usuario_id, nome_conta, data_vencimento)` em `despesas` **não implementado** — considerar como proteção adicional se duplicação continuar ocorrendo na prática.

---

## MÉDIO

### BE15. `$conn->query()` usado sem checar retorno antes de `fetch_assoc()`/`fetch_all()`
Padrão em `admin/categorias.php:26-27`, `admin/abrir_chamado_admin.php:74-75`, `admin/limpar_tecnicos_id_zero.php`, `admin/api_keys.php:39-40`. Se a query falhar (timeout, deadlock, permissão), `$conn->query()` retorna `false`, e `false->fetch_all()` é fatal error — tela em branco sem mensagem amigável nem log.
**Status: [ ]** Pendente — adicionar checagem `if ($result)` nos pontos listados.

### BE16. `admin/processar_categoria.php` — proteção de exclusão nunca funciona (mesma causa raiz de BE9)
Ver BE9. Listado aqui separadamente porque é o sintoma mais visível: um admin pode apagar uma categoria "em uso" pensando que o sistema teria avisado.
**Status: [ ]** Pendente — resolve junto com BE9.

### BE17. `despesas/recorrentes.php` + cron — TOCTOU real na geração de recorrentes
`despesas/classes/Despesa.php:418-497` — `SELECT COUNT` seguido de `INSERT` sem lock. Se o cron mensal rodar exatamente enquanto alguém clica "Gerar Próximo Mês" manualmente (ou duplo-clique sem debounce), ambos veem `0` no COUNT e ambos inserem — despesa duplicada. Ver também BE14.
**Status: [ ]** Pendente — resolve junto com BE14.

### BE18. `admin/api_keys.php` — DDL executado a cada carregamento de página
`admin/api_keys.php:17-46` tenta `CREATE TABLE`/`ALTER TABLE` toda vez que a tela é aberta, sem tratar falha parcial. Se o `ALTER` falhar (permissão insuficiente), a checagem continua achando a tabela desatualizada em toda visita, tentando de novo seguidamente.
**Status: [ ]** Pendente — mover DDL para uma migração única, fora do fluxo de request normal.

### BE19. Validação de duplicidade replicada com pequenas variações
`admin/processar_adicionar_tecnico.php:33-61`, `admin/processar_categoria.php:31-41`, `admin/processar_atribuicao.php` — cada um reimplementa inline `SELECT ... WHERE campo = ?` + checagem de `num_rows` para验证 duplicidade, em vez de uma função utilitária comum.
**Status: [ ]** Pendente — baixa prioridade, dívida técnica.

### BE20. `modules/` — scaffold morto, 12 diretórios vazios
`modules/BarberShop/`, `modules/HelpDesk/`, `modules/MyHealth/` (cada um com `Controllers/Models/Views` vazios) — planejamento de modularização nunca preenchido.
**Status: [ ]** Pendente — remover ou documentar como planejamento futuro explícito.

### BE21. Scripts de manutenção "one-shot" esquecidos em produção, com lixo de conteúdo colado
`admin/limpar_tecnicos_id_zero.php` (ver S4 em `AUDITORIA_SEGURANCA.md`), `despesas/fix_multiuser.php` (ver S23), `admin/super_admin/tests/test_connection.php` (diagnóstico público sem proteção), `admin/super_admin/config/redits.php` (arquivo de 0 bytes, provável erro de digitação de "redirects").
**Status: [ ]** Pendente — remover os 4 arquivos do servidor de produção.

### BE22. Contrato de resposta JSON inconsistente entre endpoints do mesmo sistema
`api/validar_chave.php` retorna `{"success", "error", "code"}` (inglês); `api/validar_licenca.php` retorna `{"sucesso", "erro"}` (português, sem `code`). Mesmo produto (licenciamento), dois formatos de erro diferentes para quem consome.
**Status: [ ]** Pendente — padronizar o contrato entre os dois endpoints.

---

## BAIXO

### BE23. `try/catch` de envio de email sem retry nem fila
`cliente/fechar_chamado.php:79-114`, `cliente/adicionar_resposta.php:94-130` — falha de SMTP é engolida (`error_log`, "não bloquear o fluxo"), correto para não travar a ação principal, mas sem qualquer mecanismo de re-tentativa — notificação se perde silenciosamente se o SMTP cair.
**Status: [ ]** Pendente — considerar fila simples (tabela `emails_pendentes` + cron de reenvio) se a confiabilidade da notificação for importante para o negócio.

### BE24. `display_errors` via `ini_set` também em `controller/configurar_log.php` e `publics/processa_contato.php`
Achado adicional aos 5 arquivos já listados em A5 do documento original — o padrão se repete em mais 2 arquivos do fluxo normal do admin.
**Status: [ ]** Pendente — resolve junto com A5.

### BE25. Nomenclatura de campos de formulário mistura português e inglês
`admin/processa_adicionar_tecnico.php:14-19` — `$_POST['status_technician']`, `$_POST['registration']`, `$_POST['vehicle_of_the_day']`, `$_POST['password']` (inglês) recebidos em variáveis `$status`, `$matricula`, `$veiculo`, `$senha` (português), enquanto o resto do sistema usa nomes em português nos dois lados.
**Status: [ ]** Pendente — baixa prioridade, cosmético.

### BE26. Coluna `senha` redundante em `tecnicos` (ver também C9 do doc original)
`tecnicos.senha` convive com `tecnicos.senha_hash`, sem uso funcional no login atual, mas ainda `NOT NULL`.
**Status: [x]** Corrigido na Fase 5 — confirmado por busca que nenhum INSERT/UPDATE/SELECT ativo referenciava a coluna (login e criação de técnico já usavam só `senha_hash`); coluna `senha` removida na migração. Ver C9 (`AUDITORIA_ACHADOS.md`) para o pré-requisito ainda pendente antes de rodar em produção (confirmar `senha_hash` do id=2).

### BE27. `admin/super_admin/JWTMiddleware.php` mistura implementação com ~170 linhas de exemplo de uso comentado
Não executável, mas dificulta leitura da classe real — documentação deveria estar em README, não no arquivo de implementação.
**Status: [ ]** Pendente — mover para documentação separada.

---

## Nota sobre o banco de dados local

Os achados de schema (BE3, BE9, BE10, BE26) foram confirmados contra o dump `.sql` disponível neste ambiente de trabalho. Como já registrado no plano de correção, o schema local pode não refletir com exatidão o banco de produção — confirmar cada achado de schema contra produção (ou uma cópia fiel) antes de qualquer migração, e sempre testar em cópia local primeiro.
