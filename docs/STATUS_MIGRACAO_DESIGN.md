# Status de Migração para Design v2.1

**NetoNerd ITSM - Migração de Design**

**Última atualização:** 2026-07-15
**Versão:** 2.1
**Status:** ✅ Layout em drawer lateral + paleta do logo aplicados

---

## ⚠️ Correção sobre este documento

A versão anterior deste arquivo (datada de 2026-01-22) afirmava "12/12 páginas migradas, 100% completo" para a v2.0. Essa contagem estava desatualizada: uma auditoria de código na Fase 7 (2026-07-15) encontrou **26 páginas** já usando `includes/header.php`/`netonerd-global.css` nessa época — a migração continuou depois de janeiro sem que este documento fosse atualizado. Esta versão corrige a contagem e registra o trabalho da Fase 7.

---

## O que mudou na Fase 7 (v2.0 → v2.1)

A v2.0 já tinha os componentes reutilizáveis (`nn-card`, `nn-btn`, `nn-badge`, `nn-table`, `nn-form-control`, `nn-alert`, `nn-stats-grid`) prontos e em uso — isso **não mudou**. O que a Fase 7 substituiu foi só a camada de layout e a paleta:

- **Header horizontal fixo → drawer lateral colapsável** (`includes/header.php` reescrito), no mesmo padrão visual usado por StyleManager/Escritorius: sidebar com logo, usuário, navegação por seção e logout; colapsa/expande no desktop (estado persistido em `localStorage`); vira drawer com overlay no mobile.
- **Paleta Bootstrap genérica (`#007bff`) → paleta extraída do logo** (`#0B3D91` azul-marinho, `#38BDF8` ciano de destaque) em `assets/css/netonerd-global.css`, mantendo os mesmos nomes de variável (`--primary-blue` etc.) para não exigir mudança nos componentes.
- `includes/footer.php`: corrigida duplicidade de versão do Bootstrap Bundle JS (carregava 5.3.0 e 5.3.2 ao mesmo tempo).
- `includes/page-template-example.php`: comentário de cabeçalho atualizado para refletir o layout em sidebar.

Nenhuma página precisou de alteração de HTML só por causa da troca de header/paleta — o `<div class="nn-main-wrapper"><div class="nn-content nn-content-full">` já existente em todas as páginas v2.0 funciona automaticamente com o novo CSS de layout.

## Páginas migradas para nn-*/`includes/header.php` (v2.0/v2.1) — 28 páginas

### Admin (15 páginas)
`dashboard.php`, `atribuir_chamados.php`, `chamados_ativos.php`, `apresenta_tecnicos.php`, `relatorios.php`, `relatorio_tecnico.php`, `licencas.php`, `categorias.php`, `api_keys.php`, `editar_tecnico.php`, `gerar_ordem_servico.php`, `listar_ordens_servico.php`, `lgpd_titulares.php`, `visualizar_chamado.php`, `visualizar_ordem_servico.php`

### Técnico (4 páginas)
`paineltecnico.php`, `meus_chamados.php`, `resolver_chamado.php`, `detalhes_chamado.php`

### Cliente (9 páginas)
`home.php`, `meus_chamados.php`, `contato.php`, `detalhe_chamado.php`, `editar_chamado.php`, `minha_conta.php`, `abrir_chamado.php`, `visualizar_chamado.php` *(migrada na Fase 7 — antes usava `css/main.css` isolado)*

## Fora do escopo da Fase 7 (decisão explícita)

- **`publics/`** (site institucional: landing, login, cadastro, planos, contato etc.) — continua com Bootstrap 4 + `css/main.css` antigo. Não tem sidebar porque não há usuário logado nesse contexto; decisão do usuário de manter fora desta fase.
- **`tecnico/loginTecnico.php`** — recebeu só a atualização de paleta (troca de `#007bff`/`#0056b3` pelas cores novas no `<style>` inline), sem sidebar — é tela pré-login.
- **`app/`** (árvore MVC paralela, majoritariamente código morto / clone do Super Admin) — fora, sem mudança.

## Pendências conhecidas (não bloqueiam a Fase 7)

- Consolidar as 3 versões de Bootstrap coexistindo (4.0.0/4.5.2/5.3.x) — item do plano original, ainda não feito.
- Polish de acessibilidade/UX (`pattern`/`maxlength` em telefone/CPF, proteção contra duplo-submit, `<label for=...>` consistente, `alt` em imagens).
- `cliente/abrir_chamado.php` tinha um bug estrutural encontrado durante a Fase 7 (incluía `includes/header.php` e depois montava um segundo `<html><body>` por cima, nunca de fato usando a sidebar) — corrigido nesta mesma sessão junto com a migração de paleta/layout.
