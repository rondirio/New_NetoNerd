# NetoNerd ITSM — Refatoração 2026: por que, o quê, e onde está tudo

Este documento é o ponto de entrada para entender a refatoração completa do NetoNerd ITSM realizada entre 2026-07-13 e 2026-07-17. Se você está chegando agora neste projeto — como novo desenvolvedor, ou revisitando depois de um tempo — comece aqui.

---

## Por que esta refatoração aconteceu

O NetoNerd ITSM é o sistema de gestão de chamados e ordens de serviço usado internamente pela empresa, em produção real (`netonerd.com.br`), atendendo clientes de verdade. Uma auditoria inicial do código (pasta baixada diretamente do servidor de produção, incluindo `.env` real e dump do banco de dados) revelou um quadro sério:

- **Um backdoor de login** (`matricula === 'teste' && password === 'senha'`) dava acesso total de super admin a qualquer pessoa que soubesse a combinação.
- **Escalação de privilégio real**: qualquer técnico com uma matrícula no padrão certo (`\d{4}A\d{3}`) virava admin automaticamente no login — sem essa ser a intenção, e sem nenhuma camada de verificação além de um regex.
- **CSRF token gerado mas nunca validado** em nenhum formulário do sistema — toda ação de escrita (abrir, editar, excluir chamado) era falsificável por um site de terceiros.
- **Credenciais reais em texto puro no código-fonte**: senha de banco de dados, secrets de JWT, chave de criptografia AES, senha de app do Gmail — tudo hardcoded, versionado no histórico do git.
- **Ações destrutivas sem autenticação nenhuma**: um script de reset de senha em massa, acessível por URL direta, sem login.
- **Bugs que quebravam o uso diário**: editar, excluir ou fechar um chamado resultava em erro fatal (`require` de caminho relativo incorreto); um trigger de banco gravava sempre o técnico como autor de qualquer mudança de status, mesmo quando era o cliente ou um admin quem tinha agido.
- **Race condition na geração de protocolo de chamado**: duas requisições simultâneas podiam gerar o mesmo número de protocolo — sem transação, sem lock, sem `UNIQUE` no banco.
- **Um sistema visual desatualizado e inconsistente**: header horizontal genérico com paleta azul de Bootstrap padrão (`#007bff`), sem nenhuma relação com a identidade visual da empresa, enquanto os outros produtos do mesmo hub (StyleManager, Escritorius) já usavam um padrão de drawer lateral com paleta de marca própria.

**Decisão fundamental que orientou toda a execução:** o site continua no ar o tempo todo. Nenhuma fase da refatoração poderia exigir downtime, mesmo quando o estado do código sugeria que o sistema "não deveria estar em produção" no seu estado atual — é produção real, usada ativamente pela empresa. Por isso a refatoração foi dividida em fases, ordenadas da mais segura/isolada para a mais arriscada (mudança de schema de banco com dado real fica por último, testada sempre em cópia local antes).

**Decisão de lançamento:** o projeto sobe para produção **por inteiro**, de uma vez, ao final de toda a atualização — não é deploy incremental fase por fase. Isso significa que várias correções (principalmente as que envolvem migração de schema) foram feitas e testadas em ambiente local, aguardando esse lançamento único para irem ao ar.

---

## Como este trabalho está documentado

Este projeto usa 3 documentos complementares, cada um com um propósito:

- **`docs/AUDITORIA_ACHADOS.md`**, **`docs/AUDITORIA_SEGURANCA.md`**, **`docs/AUDITORIA_BACKEND.md`**, **`docs/AUDITORIA_FRONTEND.md`** — os achados originais, por severidade e categoria. É a fonte da verdade sobre "o que estava errado", com status `[x]`/`[ ]`/`[~]` atualizado.
- **`docs/PLANO_DE_CORRECAO.md`** — a ordem de execução das 7 fases, com checklist do que foi feito em cada uma. É o documento a consultar para saber "em que pé está" o trabalho.
- **`docs/resolucoesAuditoria.md`** — o changelog técnico detalhado: para cada item corrigido, qual foi a causa raiz, o que exatamente mudou no código, e como foi testado. É o documento a consultar para entender "como" algo foi corrigido.

Este documento (`REFATORACAO_NETONERD_ITSM.md`) é um quarto nível, acima dos outros três: um resumo executivo para quem precisa da visão geral sem entrar em cada detalhe técnico.

---

## As 7 fases, em resumo

Todas as 7 fases estão **concluídas** (2026-07-17). Migrações de banco de dados foram testadas e aplicadas em ambiente local; aguardam o lançamento único em produção.

### Fase 1 — Correções isoladas, sem risco (2026-07-13)
Bugs e falhas de segurança que podiam ser corrigidos sem tocar em schema de banco e sem mudar comportamento visível (exceto onde o comportamento já estava quebrado). Incluiu: backdoor de login removido, `.htaccess` de proteção na raiz, upload de arquivo validado por magic bytes (não apenas extensão), credenciais migradas para `.env`, destruição de sessão redundante no logout.

### Fase 2 — Ações fora do código
Redefinida no meio do caminho: como o lançamento é único e substitui produção por inteiro, a maior parte das ações "fazer isso em produção agora" ficou desnecessária — o estado atual de produção será substituído de qualquer forma. Only item com valor real de execução imediata: limpeza de uma conta de teste do próprio desenvolvedor.

### Fase 3 — Bugs de fluxo totalmente quebrado (2026-07-13)
A fase com maior impacto percebido pelo usuário: editar, excluir ou fechar um chamado resultava em erro fatal. Corrigidos os `require` de caminho relativo quebrados, a race condition de protocolo (com transação + `FOR UPDATE` + `UNIQUE` no banco como segunda rede de segurança), e o `categoria_id` que nunca era populado ao abrir um chamado.

### Fase 4 — Controle de acesso e CSRF (2026-07-13/14)
A escalação de privilégio (técnico virando admin por regex de matrícula) foi fechada com uma solução mais robusta que a originalmente prevista: em vez de uma coluna de "role" em `tecnicos`, os admins foram separados fisicamente numa tabela `admins` própria. CSRF real implementado e aplicado em 27 formulários/handlers. Rate limiting de login (IP + banco). Cookie de sessão com atributos de segurança (`HttpOnly`, `SameSite`) centralizado.

### Fase 5 — Banco de dados: schema e conexão (2026-07-15)
A fase mais arriscada — mexe em schema com dado real de produção, por isso feita por último entre as técnicas e sempre testada em cópia local primeiro. O trigger que gravava sempre o técnico como autor de qualquer mudança de status (mesmo quando o cliente ou um admin agiam) foi removido e substituído por uma função central chamada explicitamente em cada ponto que muda o status de um chamado. Colunas duplicadas/mortas removidas (`tecnicos.Ativo`, `tecnicos.senha`).

### Fase 6 — Estrutural e LGPD (2026-07-15)
Criptografia real (AES-256-GCM) para a senha de banco de dados de cliente exposta via API. Remoção de cópias de vitrine desatualizadas e vulneráveis do portal do cliente. Log de auditoria centralizado (`registrarLogSistema()`). MVP de titular LGPD: exportação de dados e anonimização de cliente. Um achado grande no caminho: a pasta `app/Views/superadmin/` não era código morto — era o repositório real de outro produto do hub (Super Admin), clonado por engano dentro do ITSM, com `.git` aninhado próprio e ainda ativo em produção.

### Fase 7 — Design System (2026-07-15 a 2026-07-17)
A fase visual, feita por último de propósito — redesenhar uma tela com fluxo quebrado ou triplicada por engano seria retrabalho garantido. Substituiu o header horizontal genérico por um drawer lateral colapsável com a paleta extraída do logo da empresa, no mesmo padrão já usado pelos outros produtos do hub (StyleManager, Escritorius). Migrou todas as páginas que ainda não seguiam esse padrão, corrigiu bugs reais descobertos ao testar cada migração (alguns não relacionados a design — um erro 500, um botão "Excluir" que nunca excluía, CPF exposto sem proteção), adicionou máscaras de formatação em campos de CPF/telefone/CEP, e corrigiu acessibilidade (~104 associações `label`/`input` faltando em ~20 arquivos). Ver a seção dedicada abaixo — é a fase mais longa e com mais achados incidentais.

---

## Fase 7 em detalhe — por que ela cresceu tanto

A Fase 7 começou como "trocar o header por uma sidebar e aplicar a paleta certa", mas quase toda vez que uma tela era migrada de verdade — não só visualmente, mas testada de ponta a ponta — apareciam bugs reais que nenhuma auditoria anterior tinha pego, porque exigiam *usar* a funcionalidade, não só ler o código:

- Duas páginas (`cliente/abrir_chamado.php`, `admin/abrir_chamado_admin.php`, depois também `cliente/contato.php`) incluíam o novo header (um documento HTML completo) e depois montavam um **segundo** documento HTML por cima — nunca tinham sido migradas de verdade, apesar de "incluir" o arquivo certo.
- Um `INSERT` de anexo de chamado usava nomes de coluna que não existiam mais no banco real — bug que só se manifestava quando um usuário efetivamente anexava um arquivo ao abrir um chamado, e que desfazia o chamado inteiro por rodar dentro da mesma transação.
- O botão "Excluir Ordem de Serviço" nunca excluía nada — o arquivo por trás dele era uma cópia acidental de outro script, que só atualizava status.
- Duas telas de detalhe de chamado do cliente coexistiam, divergentes, e os processadores de resposta/fechamento sempre redirecionavam para a errada — um cliente que entrasse pelo caminho "errado" nunca conseguia responder ou fechar um chamado.
- CPF de cliente exibido em texto puro numa tela de admin, sem nenhuma proteção — corrigido replicando o mesmo padrão de mascaramento + confirmação de senha já usado no Escritorius.

Esses achados, mais uma bateria de 15 itens reportados diretamente pelo usuário ao testar o fluxo administrativo real (erro 500, matrícula duplicada quebrando o formulário, campo obrigatório que não deveria ser, ordenação sem proteção contra dado nulo), foram todos investigados, corrigidos e testados dentro do guarda-chuva da Fase 7. O detalhamento item a item está em `docs/resolucoesAuditoria.md`.

Uma feature de produto nova também nasceu no meio desse trabalho, fora do escopo original do plano: técnicos agora podem encerrar um chamado sem resolução quando o cliente não responde há 48h, em vez de deixá-lo preso indefinidamente esperando confirmação de pagamento.

---

## O que fica para depois (fora do escopo desta refatoração)

- **Lançamento único em produção**: promover `.env.production` a `.env` no servidor, e rodar as migrações SQL das Fases 5, 6 e 7 (testadas só localmente) contra o banco real.
- **M3/M7 (LGPD)**: expurgo automático de dados após o período de retenção, e revisão jurídica formal da política de privacidade — travados esperando uma decisão de negócio sobre a regra de retenção.
- **Decisão sobre o `.git` aninhado** em `app/Views/superadmin/` (o clone acidental do Super Admin) — mover para repositório próprio ou manter e ignorar.
- **Itens de baixa prioridade** (BE18, BE19, BE22, BE23, BE25) — dívida técnica de backend documentada em `docs/AUDITORIA_BACKEND.md`, sem urgência.
- **Migração para Laravel + Vue** — decisão de arquitetura de longo prazo, intencionalmente adiada para depois de toda correção de segurança e design estarem consolidadas.

---

## Documentação da API

Ver `docs/API.md` para a documentação completa dos endpoints do sistema — a API pública (`api/validar_chave.php`, usada por aplicativos móveis externos) e os endpoints internos AJAX usados pelo próprio frontend.
