# Plano de Migração do Banco de Dados — Lançamento em Produção

**Contexto:** produção ainda roda no schema antigo (pré-refatoração Fases 1-7). As migrações de fase (`config/bandoDeDados/migracao_fase3_*.sql` até `migracao_fase7_*.sql`, mais `migracao_protocolo_unique.sql`) só foram testadas em ambiente local. A estratégia escolhida é: **criar um banco novo do zero com o schema atualizado e importar os dados reais de clientes do banco antigo para dentro dele** — não aplicar `ALTER` incremental direto em cima do banco de produção vivo.

**Por quê banco novo em vez de ALTER em produção:** o banco antigo em produção fica intocado como backup natural durante todo o processo. Se algo der errado no meio da migração, o site continua no ar apontando para o banco antigo até o problema ser resolvido — só a troca final de `DB_NAME` no `.env` de produção decide o corte.

Este plano é um checklist genérico: execute-o quando tirar o dump real e atualizado de produção no dia do lançamento. Não use dumps antigos do repositório (`u478690921_netonerd.sql`, `NetoNerd_BD.sql`) como fonte de dados — eles são só referência de schema desatualizada.

---

## Por que não é um simples `mysqldump | mysql`

As migrações de fase existentes não são `CREATE TABLE` limpos — são `ALTER`/`UPDATE` escritos assumindo o schema *antigo* como ponto de partida, e várias delas têm passos de verificação manual porque encontraram dado sujo específico do ambiente local (ex: `id=0` em `historico_chamados` e `logs_sistema`, linhas duplicadas em `configuracoes_sistema`). **Produção pode ou não ter os mesmos problemas** — cada migração diz explicitamente para checar antes de aplicar.

Por isso a ordem certa é: dump de produção → banco de staging → aplicar migrações de schema (na ordem, com as checagens) → só então tratar esse banco de staging como o "banco novo" a promover.

---

## Passo a passo

### 1. Preparação (antes do dia do lançamento)

- [ ] Revisar se surgiram novas migrações de schema desde a última vez que este plano foi lido (`ls config/bandoDeDados/migracao_*.sql`, ordenar por conteúdo/data no cabeçalho, não por nome de arquivo).
- [ ] Confirmar que todas as migrações de fase já rodaram com sucesso em ambiente de teste/staging usando um dump de produção **recente** (não o antigo de referência).
- [ ] Ter downtime avisado ou janela de manutenção, se o volume de dados exigir tempo de import perceptível.

### 2. Dump de produção (dia do lançamento)

```bash
mysqldump -u <user> -p --single-transaction --routines --triggers u478690921_netonerd > prod_dump_YYYYMMDD.sql
```

- `--single-transaction` evita lock longo em tabelas InnoDB durante o dump (não trava o site enquanto tira o backup).
- Guardar este dump como backup do estado antes da migração — não é o arquivo que vira a fonte da migração, é a rede de segurança para reverter.

### 3. Banco de staging — schema antigo + dados reais

```sql
CREATE DATABASE netonerd_staging_migracao;
```

```bash
mysql -u <user> -p netonerd_staging_migracao < prod_dump_YYYYMMDD.sql
```

Agora `netonerd_staging_migracao` é uma cópia exata de produção (schema antigo + dados reais), isolada do banco vivo.

### 4. Aplicar as migrações de fase, em ordem, com as checagens manuais

Ordem de aplicação (pela numeração de fase, não pela ordem alfabética do `ls`):

1. `migracao_fase3_status_fechado.sql`
2. `migracao_fase4_tabela_admins.sql`
3. `migracao_fase5_banco_dados.sql` — **antes de rodar:** `SHOW CREATE TABLE historico_chamados;` para confirmar se a coluna `id` já tem PK/AUTO_INCREMENT neste dump ou se tem o mesmo problema encontrado localmente (todas as linhas com `id=0`).
4. `migracao_fase6_lgpd_estrutural.sql` — mesma checagem manual, mas em `logs_sistema`.
5. `migracao_fase7_configuracoes_sistema.sql` — **antes de rodar:** `SELECT chave, COUNT(*) FROM configuracoes_sistema GROUP BY chave HAVING COUNT(*) > 1;`. Se não houver duplicatas em produção, pular o `DELETE FROM configuracoes_sistema WHERE id = 0` e aplicar só a normalização de PK + índice único.
6. `migracao_protocolo_unique.sql` — **antes de rodar:** `SELECT protocolo, COUNT(*) FROM chamados GROUP BY protocolo HAVING COUNT(*) > 1;`. Se houver duplicados reais, resolver manualmente (renumerar) antes do `ADD UNIQUE`, ou o `ALTER TABLE` falha.

Rodar cada uma contra `netonerd_staging_migracao`, conferindo o `SELECT` de verificação impresso ao final de cada script antes de seguir para a próxima.

- [ ] Verificar também se há migrações fora de `config/bandoDeDados/` relevantes (`despesas/migrate_parcelamento.sql`, `despesas/config/migration_valor_pago.sql`) — só se o Despesas também estiver subindo dados reais nesse lançamento.

### 5. Validação do banco de staging migrado

Antes de promover, comparar contagens de linhas por tabela entre `prod_dump` original e `netonerd_staging_migracao` pós-migração — toda diferença deve ser explicável pelas migrações aplicadas (ex: linhas removidas de `usuarios` na fase 4, linhas de `configuracoes_sistema` deduplicadas na fase 7):

```sql
SELECT table_name, table_rows FROM information_schema.tables WHERE table_schema = 'netonerd_staging_migracao';
```

- [ ] Testar login de cliente, técnico e admin contra o banco migrado (ambiente de staging da aplicação apontando para `netonerd_staging_migracao`).
- [ ] Testar abertura e fechamento de chamado (valida o ENUM novo da fase 3 e o trigger removido da fase 5).
- [ ] Conferir que a tabela `admins` está populada corretamente e `tecnicos` não contém mais quem foi movido (fase 4).

### 6. Corte para produção

- [ ] Renomear/promover `netonerd_staging_migracao` para o nome de banco definitivo (ou apontar a `DB_NAME` do `.env` de produção para ele — decisão de infraestrutura, confirmar qual caminho o hosting/Hostinger permite).
- [ ] Manter o banco antigo de produção intocado por um período de segurança (não dropar imediatamente) para rollback rápido se algo passar despercebido na validação.

---

## Pendência relacionada (não bloqueia a migração, mas precisa de atenção)

`config/bandoDeDados/NetoNerd_BD.sql` está commitado no histórico do Git (já enviado ao GitHub) e contém `INSERT INTO` com dados reais de abril/2025. Bloqueado por `.htaccess` para acesso HTTP direto, mas exposto no histórico do repositório público. Considerar limpar do histórico (`git filter-repo` ou equivalente) — tratar como item separado, não faz parte deste plano de migração.
