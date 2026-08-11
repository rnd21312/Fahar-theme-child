# AGENTS.md — Fahar Theme Child

## Goal
Fast Persian/RTL-first app-like WordPress portfolio.

- Parent: `hello-elementor`
- Visual/brand source: live `https://fahar.ir/`
- Pinterest: secondary interaction reference only (masonry/discovery/feed-detail)
- Preserve existing WordPress/Elementor content, URLs, media, taxonomies, comments and SEO/meta.
- Never edit WordPress/Hello Elementor/Elementor/vendor core.

## Codex mode: ultra-lean
Act as a coding agent, not a consultant.

- Do not narrate plans or reasoning.
- Do not explain architecture unless asked.
- Do not re-audit known project areas.
- Do not browse/research unless the task requires current/external evidence.
- Do not inspect the whole repo/tree.
- Search first; open only files needed to patch.
- Do not read docs unless directly relevant.
- Do not implement adjacent/future improvements.
- Do not refactor unrelated code.
- Do not create speculative abstractions.
- Prefer existing helpers/tokens/components.
- Patch → validate → stop.
- Ask only when blocked by a decision that cannot safely be inferred.

Default task flow:
`git status` → inspect 2–5 relevant files → edit → smallest validation → `git diff --check` → stop.

Final output max 6 short lines: changed files + validation + blocker only if any. No recap, essay, praise, or unsolicited next steps.

## Architecture
Keep `functions.php` small. Use existing `inc/` modules. Provider-specific portfolio logic stays behind `inc/portfolio.php`.

Theme owns presentation/templates/frontend enhancement. Reusable functionality that must survive theme changes belongs in future `fahar-elementor-core`.

## UI tasks
For visible UI/layout/interaction/responsive/motion/navigation work:

1. load **UI UX Pro Max**;
2. load repo skill `.agents/skills/fahar-ui-system/SKILL.md`;
3. use only the relevant UI-system reference file;
4. stop if a required UI skill is unavailable.

Visual priority: `fahar.ir` → implemented Fahar design system → Pinterest behavior only where Fahar has no equivalent pattern.

If `fahar.ir` cannot be visually inspected, keep existing verified tokens/components; never guess brand values.

## Design system
Canonical:
- `assets/css/tokens.css` — global semantic values
- `assets/css/components.css` — primitives
- dedicated component CSS — complex reusable components
- `.agents/skills/fahar-ui-system/references/` — contracts

Use `--fahar-*` tokens and `fahar-` classes. Page CSS owns composition/layout, not duplicate primitive styles. Reuse/extend before creating a component. Repeated visual behavior becomes a canonical component/token, not copied CSS.

Use logical CSS, native modern CSS, RTL-safe behavior, `:focus-visible`, reduced motion and ~44px practical touch targets. No framework/icon-font/heavy visual dependency unless explicitly required.

## Portfolio contract
Single media order from `fahar_theme_get_portfolio_media_items()`:
1. Featured Image
2. content media
3. verified provider cover/lightbox/video
4. remaining attachments

Featured Image stays first when present. Supported media renders in `.fahar-portfolio-single__media`, never duplicated in description. Never mutate stored content or weaken provider/URL validation.

## Code rules
**JS:** vanilla, progressive enhancement, guarded DOM, conditional loading, minimal listeners/layout work. Prefer native scroll/snap, IntersectionObserver, ResizeObserver, requestAnimationFrame.

**PHP:** WordPress APIs; `fahar_theme_*`; sanitize input; escape output; nonce/capability checks for mutations; no direct SQL/custom tables/new REST/AJAX unless task requires it; Elementor optional/defensive; never require Pro.

**Performance:** mobile-first; conditional assets; responsive/intrinsic images; primary Single image may be eager/high priority; other media lazy; native video `preload="metadata"`; no autoplay feed media or eager remote embeds.

**A11y/security:** semantic controls, keyboard/focus, accessible names, useful ARIA, contrast/touch/reduced-motion; sanitize/escape/validate/KSES; arbitrary stored embed HTML is forbidden.

## File map
UI system: skill + one relevant reference + tokens/component CSS only as needed.

Mobile nav: `template-parts/navigation/mobile-bottom-nav.php`, `assets/css/mobile-nav.css`, `inc/navigation.php`.

Explore: relevant template/parts + `portfolio-explore.css`, `portfolio-card.css`, `portfolio-explore.js`; `inc/portfolio.php` only for data behavior.

Single: relevant `template-parts/portfolio/*` + `portfolio-single.css`, `portfolio-single.js`; `inc/portfolio.php` only for data/media behavior.

## Current state
Foundation through static performance/accessibility QA is done. Do not restart Tasks 1–25.

Known UX debt: permanent desktop Explore sidebar conflicts with full-width/no-sidebar target.

## Validation
Run only what changed:
- PHP: `php -l file.php`
- JS: `node --check file.js`
- UI CSS: `python tools/check-ui-system.py changed.css`
- always: `git diff --check`

Never claim browser/Lighthouse/runtime tests unless actually run.

## Git
Do not overwrite unrelated changes. Do not commit/push/merge/amend unless explicitly requested. One task = one focused diff.

Priority: user request → Fahar brand → data/security/correctness → accessibility → mobile performance → architecture → interaction polish.
