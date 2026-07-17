# NetoNerd ITSM — Auditoria de Qualidade de Frontend

Auditoria de JavaScript, CSS, consistência visual, acessibilidade e responsividade, separada da auditoria de segurança (`docs/AUDITORIA_SEGURANCA.md`) e de backend (`docs/AUDITORIA_BACKEND.md`). Não repete B1-B4 do documento original (`docs/AUDITORIA_ACHADOS.md`), citados como contexto quando um achado novo está diretamente ligado.

Mesma convenção: `[ ]` pendente, `[x]` corrigido, `[~]` parcial.

---

## Quebra funcional confirmada — ação recomendada imediata

### FE1. `tecnico/loginTecnico.php` — JS quebrado (já era B3) + label também quebrado no mesmo id
`document.getElementById('email')` (linhas 473, 509) — campo real tem `id="matricula"` (linha 386). **Achado novo:** o `<label for="email">Matrícula</label>` (linha 377) tem o mesmo problema — aponta para um `id` que não existe no campo real. Clicar no texto do label não foca o campo; leitor de tela não associa a instrução ao input correto. Os dois bugs (JS e label) têm a mesma causa raiz (`id` do campo real é `matricula`, não `email`) e devem ser corrigidos juntos.
**Status: [ ]** Pendente — trocar `'email'` por `'matricula'` no JS (já era B3) e no `for=` do label (achado novo).

### FE2. `cliente/editar_chamado.php` — CSS órfão + 4 labels sem `id` correspondente
`<link href="estilos.css">` (linha 41) é o único CSS da página — `estilos.css` **não existe em lugar nenhum do repositório**. Página renderiza sem qualquer estilo (texto puro). Além disso, os 4 `<label for="...">` (`titulo`, `descricao`, `prioridade`, `status`, linhas 48-62) apontam para campos que não têm `id` nenhum. Combinado com A6 do documento original (este arquivo já tem `require` fatal quebrado), esta é a tela com mais problemas simultâneos do sistema — candidata a reescrita completa, não patch pontual.
**Status: [ ]** Pendente — ao corrigir A6 (Fase 3 do plano), aproveitar para também trocar o CSS por `includes/header.php` (Design System v2) e adicionar `id` aos campos.

### FE3. Link "Esqueceu sua senha?" morto em 2 telas de login
`tecnico/loginTecnico.php:429` e `publics/login.php:427` — `href="recuperar_senha.php"`, arquivo não existe em lugar nenhum do sistema principal. Não há nenhum fluxo de recuperação de senha implementado — o link promete uma funcionalidade inexistente.
**Status: [x]** Corrigido — link removido das duas telas. **Pendente (fora de escopo desta correção):** implementar o fluxo real de recuperação de senha, se for prioridade do produto.

### FE4. `agendamento_select` inexistente — replicado em 4 tenants StyleManager
`StyleManager/{apresenta_stylemenager,barbeariatheclub,barbeariaviana,Excellence-Barbear-House}/profissional/dashboard.php:804` — `document.getElementById('agendamento_select')` não existe em nenhum dos 4 arquivos (o HTML tem só `cliente_nome_wrap`/`cliente_nome_input`). Usa optional chaining (`sel?.value`) então não lança erro, mas o toggle "cliente avulso" nunca funciona como pretendido — parece campo removido do HTML sem remover o JS correspondente, replicado nos 4 tenants por serem cópias do mesmo código-base.
**Status: [ ]** Pendente — remover o JS morto ou restaurar o campo, nas 4 instâncias.

### FE5. CSS relativo quebrado (`css/main.css` inexistente) em 5 páginas
`cliente/contato.php:17`, `cliente/visualizar_chamado.php:108`, `tecnico/loginTecnico.php:10`, `tecnico/painelTecnicoCliente.php:45` — `<link href="css/main.css">` relativo; só existe `css/main.css` na raiz do projeto, não em `cliente/css/` ou `tecnico/css/`. Nas páginas com Bootstrap CDN como base a perda é só do CSS customizado da marca; sem Bootstrap, a página fica sem estilo algum.
**Status: [x]** Corrigido — os 4 arquivos citados (`contato.php`, `visualizar_chamado.php`, `loginTecnico.php`, `painelTecnicoCliente.php`) agora usam `../css/main.css`. Achado só listava 4 caminhos apesar do título dizer "5 páginas" — confirmado que não havia um 5º arquivo faltando no mesmo padrão dentro de `cliente/`/`tecnico/`.

### FE6. Ícone de menu mobile invisível — branco sobre branco (`publics/atendimento.php`)
`routes/header.php:71-73`, usado por `publics/atendimento.php` (página pública ativa): navbar com `bg-white`, botão hambúrguer usa `<span style="color: white;">☰</span>` **sem** a classe `navbar-toggler-icon` (que traria o ícone via `background-image`). Em telas < 992px o botão de menu fica invisível — navegação mobile impossível de descobrir nessa página.
**Status: [x]** Corrigido — trocado o `<span style="color: white;">☰</span>` por `<span class="navbar-toggler-icon"></span>` (confirmado que `css/main.css:48-52` já define o `background-image` correto para essa classe). `routes/header.php` é compartilhado por outras páginas de `publics/` além de `atendimento.php`, então a correção beneficia todas elas.

### FE7. `preencherModalAlterar` indefinida — 2 arquivos (telas já mortas, B4)
`tecnico/dashboard.php:126` e `app/Views/dashboard/painel-tecnico.php:126` — botão chama `preencherModalAlterar(this)`, mas a única função definida é `preencherModal(button)` (nunca chamada por ninguém). `Uncaught ReferenceError` ao clicar. Ambas as telas já são código morto (B4 do documento original), mas o bug confirma um padrão de cópia-cola sem QA que vale registrar.
**Status: [ ]** Pendente — resolve junto com a decisão de B4 (completar ou remover essas telas).

---

## Inconsistência estrutural — descredibiliza o produto, não quebra funcionalmente

### FE8. Migração de Design System não está 100% completa como `docs/STATUS_MIGRACAO_DESIGN.md` afirma
O documento afirma "100% COMPLETO — 12/12 páginas migradas", mas isso só contabiliza um subconjunto de páginas "principais". Pelo menos 4 telas de UI ativa continuam com HTML/CSS antigo montado do zero, sem usar `includes/header.php`/`includes/footer.php` (Design System v2):
- `cliente/editar_chamado.php`, `cliente/visualizar_chamado.php` — **a tela mais central do fluxo do cliente** (visualizar detalhe de um chamado).
- `admin/configura.php` — exceção dentro de `admin/`, que majoritariamente já migrou.
- `tecnico/dashboard.php`, `tecnico/painelTecnicoCliente.php` — já são telas mortas (B4), mas contam para o total de "páginas" caso a migração as inclua no cômputo.

Um cliente navegando de `home.php` (visual novo, Bootstrap 5.3.2) para `visualizar_chamado.php` (visual antigo, Bootstrap 4.0.0) percebe mudança abrupta de cor/tipografia/estrutura na mesma sessão.
**Status: [ ]** Pendente — atualizar `STATUS_MIGRACAO_DESIGN.md` para refletir a realidade, e migrar as páginas faltantes na Fase 7 do plano de correção (já depende de M1 — consolidar as 3 cópias de `cliente/` antes de redesenhar).

### FE9. 3 versões de Bootstrap coexistindo no sistema
- Bootstrap 4.0.0 (CDN): 13 arquivos — `cliente/abrir_chamado.php`, `cliente/contato.php`, `cliente/visualizar_chamado.php`, `tecnico/loginTecnico.php`, 9 em `publics/`.
- Bootstrap 4.5.2 (CDN): 4 arquivos — `admin/configura.php`, `tecnico/dashboard.php`, `tecnico/painelTecnicoCliente.php`, `publics/planos.php`.
- Bootstrap 5.3.x (CDN, via Design System v2): todas as páginas migradas.

Grid, componentes e breakpoints divergem ligeiramente entre versões majors — o comportamento visual (inclusive quando o menu vira hambúrguer) não é garantidamente idêntico entre páginas do mesmo sistema.
**Status: [ ]** Pendente — resolve naturalmente à medida que FE8 avança; não tratar isoladamente.

### FE10. 4 implementações de "header" diferentes, incluindo um par de nomes quase idênticos
`includes/header.php` (Design System v2, ativo), `routes/header.php` (header antigo do site público, metade do arquivo comentada), `routes/headerTecnico.php` (usado) e **`routes/header_tecnico.php`** (com underscore — não incluído por **nenhum** arquivo PHP do projeto, código morto com nome quase idêntico ao arquivo real, risco de confusão futura), mais `app/Views/layouts/header.php` (árvore MVC morta, ver BE11 em `AUDITORIA_BACKEND.md`).
**Status: [x]** Corrigido (parcial) — `routes/header_tecnico.php` movido para `_REMOVER_DO_SERVIDOR/routes/`, eliminando a ambiguidade de nome. As outras 3 implementações de header (`includes/header.php`, `routes/header.php`, `routes/headerTecnico.php`) continuam ativas e cobertas pela consolidação da Fase 7 (Design System).

### FE11. Árvore MVC morta `app/Views/` (97 arquivos) com dados mockados
Ver BE11 em `AUDITORIA_BACKEND.md` para o achado completo de código morto. Do ângulo de frontend: `app/Views/dashboard/painel-cliente.php` tem o nome fixo **"João Silva" repetido 7 vezes** em tabelas mockadas em HTML puro — mesmo padrão já documentado em `tecnico/painelTecnicoCliente.php` (B4), confirmando que não é caso isolado, é recorrente nessa árvore paralela nunca conectada ao roteamento real.
**Status: [ ]** Pendente — resolve junto com a decisão de BE11 (remover ou bloquear `app/`).

---

## Polish — baixo impacto individual, alto volume

### FE12. Ausência quase universal de `pattern`/`maxlength` em campos de telefone/CPF
`cliente/minha_conta.php:150`, `admin/abrir_chamado_admin.php:390`, `admin/editar_tecnico.php:136`, `admin/gerar_ordem_servico.php:172,200` (telefone e CPF), `publics/cadastro.php:485`, `publics/contato.php:347` — todos aceitam qualquer string, sem feedback client-side de formato (alguns têm `placeholder` como dica visual apenas, que não valida nada).
**Status: [ ]** Pendente — adicionar `pattern`/`maxlength` nos campos listados, prioridade maior em `gerar_ordem_servico.php:200` (CPF em documento fiscal).

### FE13. Ausência sistêmica de proteção contra duplo-submit
Nenhum formulário do sistema desabilita o botão de submit ou mostra estado de carregamento. Maior impacto em `cliente/abrir_chamado.php:724` (wizard de abertura de chamado — duplo-clique gera chamados duplicados) e `tecnico/resolver_chamado.php:327` (duplo-clique pode gerar múltiplos registros de resolução). Mais ~35 formulários administrativos com o mesmo padrão, de menor impacto individual.
**Status: [ ]** Pendente — priorizar `abrir_chamado.php` e `resolver_chamado.php` (desabilitar botão + spinner no submit); os demais podem seguir depois como padronização geral.

### FE14. Uso disseminado de `<label>` sem `for` (associação perdida mesmo quando visualmente correto)
Dos 45 usos de `<label for=...>` encontrados, a maioria dos formulários usa `<label>Texto</label>` **sem** `for`, mesmo quando o `<input>` logo abaixo tem `id` (`admin/gerar_ordem_servico.php:158-165`, `publics/contato.php:330-338`, dezenas de outros). Visualmente correto, tecnicamente desassociado — leitor de tela não anuncia o rótulo ao focar o campo.
**Status: [ ]** Pendente — baixa prioridade individual; considerar um passe geral de acessibilidade quando o Design System v2 cobrir 100% das páginas (FE8).

### FE15. `<label for="inputCategoria">` duplicado e desassociado em `tecnico/dashboard.php`
`tecnico/dashboard.php:222-223` — dois `<label>` seguidos, um com `for="inputCategoria"`, e o `<select name="categoria">` (linha 224) não tem `id` nenhum. HTML malformado (label duplicado) nessa tela já morta (B4).
**Status: [ ]** Pendente — resolve junto com a decisão de B4.

### FE16. Imagem sem `alt` — `publics/produtos.php:115`
`<img src="../src/imagens/logoNetoNerd.jpg" id="product-img" ...>` sem `alt`, é a imagem principal do produto sendo apresentado (título populado via JS). Leitor de tela não tem descrição da imagem central da página.
**Status: [ ]** Pendente — adicionar `alt` (pode ser dinâmico, populado pelo mesmo JS que popula o título).

### FE17. `publics/index.php` — `style="color: dark;"` (valor CSS inválido, inofensivo)
`dark` não é um valor válido para `color` — a declaração é ignorada pelo navegador silenciosamente. Nesse caso o ícone real vem de `navbar-toggler-icon` (diferente de FE6, que não tem essa classe), então não quebra nada, mas evidencia falta de revisão.
**Status: [ ]** Pendente — trocar por `text-dark` (classe Bootstrap) ou remover, é cosmético.

### FE18. Cobertura de responsividade menor nas páginas não migradas
Páginas antigas (`cliente/abrir_chamado.php`, `cliente/visualizar_chamado.php`, `admin/abrir_chamado_admin.php`) têm só um breakpoint (`@media max-width: 768px`), sem cobertura para telas muito pequenas (< 480px). O Design System v2 (`assets/css/netonerd-global.css`) já cobre 992px/768px/480px — outra manifestação de FE8.
**Status: [ ]** Pendente — resolve naturalmente com FE8.

### FE19. Código JS morto (funções definidas, nunca chamadas)
`admin/abrir_chamado_admin.php` (`preventDefaults`), `publics/produtos.php` (`carregarProduto`), `app/Views/home.php` (`atualizarEstatisticas`, `filtrarChamados` — árvore morta). Baixo impacto, ruído de manutenção.
**Status: [ ]** Pendente — remover ao tocar em cada arquivo por outro motivo; não vale um passe dedicado.

---

## Resumo para priorização

Ordem sugerida de ataque dentro do Frontend (a integrar nas fases do `PLANO_DE_CORRECAO.md`):
1. FE1, FE3, FE5, FE6 — bugs isolados e baratos de corrigir, sem dependência de outras fases.
2. FE2 — resolve junto com A6 (Fase 3, já planejada).
3. FE4 — isolado, mas precisa replicar em 4 tenants StyleManager.
4. FE8/FE9/FE18 — só fazem sentido na Fase 7 (design), depois de M1 (Fase 6) consolidar as cópias de `cliente/`.
5. FE11 — resolve junto com a decisão de BE11 em `AUDITORIA_BACKEND.md`.
6. FE12-FE17, FE19 — polish, sem urgência, podem entrar como parte da Fase 7 ou de manutenção contínua.
