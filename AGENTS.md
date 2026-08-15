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

## Microtask rule — mandatory
All implementation work is split into structured microtasks.

One microtask = one small, measurable change in one UI/code concern.

Examples:
- UI phase 1 / part 1: primary button base style
- UI phase 1 / part 2: button hover/active/focus states
- UI phase 1 / part 3: button sizes
- Explore phase 1 / part 1: remove desktop sidebar layout only
- Explore phase 1 / part 1: card destination attributes only

A microtask should normally:
- touch 1–3 files; 4 only when technically inseparable;
- change one component, state group, behavior, or layout concern;
- have one clear acceptance condition;
- avoid unrelated cleanup;
- avoid completing the next part automatically.

If a requested task is broad, implement only the explicitly named part. Do not expand scope. Phases are organizational labels; each part is executed and validated independently.

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
Fahar owns Portfolio Explore only. Normal WordPress, Hello Elementor, and Elementor template resolution owns Portfolio Single presentation. The normalized `single-page` type must keep its normal WordPress permalink; never intercept portfolio singles, parse stored Elementor content, or add a replacement Single renderer.

## Code rules
**JS:** vanilla, progressive enhancement, guarded DOM, conditional loading, minimal listeners/layout work. Prefer native scroll/snap, IntersectionObserver, ResizeObserver, requestAnimationFrame.

**PHP:** WordPress APIs; `fahar_theme_*`; sanitize input; escape output; nonce/capability checks for mutations; no direct SQL/custom tables/new REST/AJAX unless task requires it; Elementor optional/defensive; never require Pro.

**Performance:** mobile-first; conditional assets; responsive/intrinsic images; no autoplay feed media or eager remote embeds.

**A11y/security:** semantic controls, keyboard/focus, accessible names, useful ARIA, contrast/touch/reduced-motion; sanitize/escape/validate/KSES; arbitrary stored embed HTML is forbidden.

## File map
UI system: skill + one relevant reference + tokens/component CSS only as needed.

Mobile nav: `template-parts/navigation/mobile-bottom-nav.php`, `assets/css/mobile-nav.css`, `inc/navigation.php`.

Explore: relevant template/parts + `portfolio-explore.css`, `portfolio-card.css`, `portfolio-explore.js`; `inc/portfolio.php` only for data behavior.

Single: owned by normal WordPress/Hello Elementor/Elementor resolution; no Fahar template routing or Single assets.

## Current state
Foundation through static performance/accessibility QA is done. Do not restart Tasks 1–25.

Current Explore includes the approved five-column desktop feed and sticky desktop sidebar.

## Validation
Run only what changed:
- PHP: `php -l file.php`
- JS: `node --check file.js`
- UI CSS: `python tools/check-ui-system.py changed.css`
- always: `git diff --check`

Never claim browser/Lighthouse/runtime tests unless actually run.

## Git
Do not overwrite unrelated changes. Do not commit/push/merge/amend unless explicitly requested. One microtask = one focused diff.

Priority: user request → Fahar brand → data/security/correctness → accessibility → mobile performance → architecture → interaction polish.
