# NetoNerd ITSM — Notas para adoção do Design System do Hub

Referências vivas: `StyleManager/includes/header.php` + `StyleManager/assets/css/custom.css` (estrutura de tokens, sidebar colapsável) e `Escritorius/public/css/admin.css` (mesma estrutura, paleta própria). O ITSM hoje (`css/main.css`, `assets/css/netonerd-global.css`) usa azul Bootstrap genérico (`#007bff`), sem identidade de marca.

## Paleta aprovada (extraída de `imagens/logoNetoNerd.jpg`)

```css
:root {
    --color-primary:        #0B3D91; /* N sólido, azul-marinho */
    --color-primary-light:  #4A7FE8; /* N translúcido, azul médio */
    --color-accent:         #38BDF8; /* círculo/texto, azul-ciano */

    --color-bg:             #F1F5F9;
    --color-surface:        #FFFFFF;
    --color-surface-alt:    #F8FAFC;

    --color-text:           #0F172A;
    --color-text-secondary: #475569;
    --color-text-light:     #94A3B8;

    --color-border:         #E2E8F0;

    --color-success:        #10B981;
    --color-warning:        #F59E0B;
    --color-danger:         #EF4444;
    --color-info:           #3B82F6;

    --sidebar-width:       260px;
    --sidebar-collapsed:   72px;
    --topbar-height:       64px;
}
```
Convenção de nomenclatura de token alinhada ao Escritorius (`--color-*`) por ser a mais recente/legível; StyleManager usa `--cor-*`/`--text-*` sem prefixo `color-` em alguns casos — não é preciso replicar a inconsistência.

## Estrutura a adotar (drawer lateral)

Baseado em `StyleManager/includes/header.php`:
- `<div class="sidebar" id="sidebar">` com `sidebar-header` (logo + botão de colapsar), `sidebar-user` (avatar + nome + role), `sidebar-nav` (itens agrupados por `nav-section-title`).
- Toggle colapsável via `#sidebarToggle`, ícone que gira (`#toggleIcon`), persistindo estado provavelmente em localStorage (confirmar no JS do StyleManager antes de replicar).
- Navegação varia por tipo de usuário (`if ($tipo === 'cliente')`, etc.) — no ITSM isso mapeia para cliente / técnico / admin, que hoje são pastas/portais separados sem layout compartilhado.
- Fonte: Inter (Google Fonts), já usada em ambos os produtos de referência — manter.
- Reset base: `*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }`.

## Diferença estrutural relevante do ITSM

O ITSM hoje **não tem um layout compartilhado** — cada página em `cliente/`, `tecnico/`, `admin/` provavelmente monta seu próprio HTML (a julgar pelas 3 cópias idênticas de `cliente/` já mapeadas na auditoria). Migrar para o padrão de drawer implica primeiro ter um `header.php`/`layout.php` compartilhado (como o StyleManager tem), não só trocar CSS. Isso é pré-requisito antes de aplicar o visual nas páginas — sem isso, qualquer CSS novo seria colado em cima de markup inconsistente entre páginas.

## Ordem de execução sugerida (ver plano de correção geral)

1. Criar o token CSS (`assets/css/netonerd-design-system.css`) com a paleta acima + estrutura de sidebar do StyleManager adaptada.
2. Criar um layout compartilhado (`includes/header.php` + `includes/sidebar.php` equivalente) — pré-requisito, não opcional.
3. Migrar telas uma por vez, começando pelo portal do cliente (já que vai ser consolidado de 3 cópias para 1 mesmo por causa da correção estrutural — aproveitar o mesmo esforço).
4. Migrar admin e técnico por último, são telas com mais volume.
