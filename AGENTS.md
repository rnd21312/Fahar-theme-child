# AGENTS.md — Fahar Theme Child

## Mission
Build Fahar as a fast, Persian/RTL-first, app-like WordPress portfolio.

- Parent: `hello-elementor`
- UX reference: Pinterest interaction/layout patterns
- Brand: Fahar Dark + Desert Gold
- Existing WordPress/Elementor content is authoritative; never rebuild or migrate it unless explicitly asked.
- This child theme is the presentation layer, not a general application plugin.

## How to work
Treat this file as a map, not an encyclopedia.

For every task:
1. Read the active user request.
2. `git status`.
3. Inspect only the relevant files.
4. Make the smallest complete change.
5. Run the smallest relevant validation.
6. Review `git diff` and `git diff --check`.

Avoid whole-repo re-audits, large tree dumps, vendor/generated files, speculative abstractions, unrelated refactors, and future-task work.

Deeper docs:
- `docs/DEVELOPMENT.md` — workflow/release checks
- `docs/PERFORMANCE-ACCESSIBILITY-QA.md` — completed static QA + runtime limits
- `docs/ROADMAP.md` — broad roadmap

When docs and code disagree, verify behavior in code and update stale docs only when in scope.

## Product invariants
Target: portfolio web application, not a blog-style theme.

Core UX: `/portfolios/` full-width masonry discovery, search/filters, progressive loading, media-first Single Portfolio, related items, Back-to-Explore restoration, desktop header, mobile bottom nav.

Never add a permanent desktop sidebar.

Preserve Elementor pages/templates/editor workflow, portfolio posts/media/taxonomies, comments, SEO/meta, and URLs where practical. Never modify WordPress core, Hello Elementor, Elementor core, or vendor/plugin code.

## Architecture
Keep `functions.php` small; place focused logic in existing `inc/` modules.

Theme owns presentation, templates, CSS/tokens, frontend progressive enhancement, navigation presentation, Elementor compatibility, RTL/accessibility presentation, and conditional assets.

Functionality that should survive a theme switch belongs in a future companion plugin such as `fahar-elementor-core` (e.g. reusable Elementor widgets, persistent likes, general application APIs).

Use existing helpers before creating abstractions. Keep provider-specific portfolio logic behind `inc/portfolio.php`.

## UI/UX — mandatory
Any task changing visible UI, layout, interaction, responsive behavior, motion, or navigation must:

- load and apply the installed **UI UX Pro Max** skill before UI/UX decisions;
- stop and report if that skill is unavailable;
- use Pinterest as the primary high-fidelity reference for hierarchy, density, discovery, card/media behavior, navigation, and interaction;
- preserve Fahar Dark + Desert Gold and Persian/RTL-first behavior;
- never copy Pinterest branding, red, proprietary assets, screenshots, or source code;
- prefer media-first, compact app-like UI over traditional WordPress chrome;
- avoid excessive glow, gradients, glass, shadows, and decorative DOM.

Validate relevant mobile, desktop, RTL, keyboard/focus, touch, and reduced-motion states.

## Design system
Source of truth: `assets/css/tokens.css`.

Use existing `--fahar-*` tokens for repeated colors/states, spacing, radius, shadows, typography, containers, motion, focus, and navigation dimensions. Primary accent: Desert Gold. Class prefix: `fahar-`.

Prefer logical CSS properties and native modern CSS. Avoid CSS frameworks, utility explosions, brittle Elementor selectors, and unnecessary `!important`.

## Portfolio media contract
`fahar_theme_get_portfolio_media_items()` defines Single Portfolio media order:

1. Featured Image
2. content-authored media
3. verified provider cover/lightbox/video
4. remaining WordPress attachments

Featured Image is primary/first when present. Supported media renders in `.fahar-portfolio-single__media`, not duplicated in description. Never mutate stored `post_content` to remove media. Do not weaken existing media URL/provider validation.

## Frontend code
JavaScript default: vanilla only. Do not add React/Vue/Svelte/Alpine/jQuery, slider libraries, masonry libraries, or similar dependencies without proven need.

JS must use progressive enhancement, safe DOM guards, multiple-instance safety where relevant, reduced-motion support, minimal listeners/layout work, and conditional loading. Prefer native scroll/CSS scroll snap, IntersectionObserver, ResizeObserver, and requestAnimationFrame. Avoid globals; use `FaharTheme` only if unavoidable.

CSS: use Grid/Flexbox, logical properties, `clamp()`, `aspect-ratio`, `:focus-visible`, and `prefers-reduced-motion`.

## PHP / WordPress / Elementor
Follow WordPress APIs/conventions and current project patterns.

- Functions/hooks: `fahar_theme_*`
- Constants: `FAHAR_THEME_*`
- sanitize inputs; escape outputs
- use nonces + capability checks for mutations
- validate URLs/attachment IDs
- prefer WordPress APIs over direct SQL
- no custom tables or new REST/AJAX surfaces without explicit scope
- detect optional plugins defensively
- never assume Elementor Pro
- never break Elementor Editor

## Performance
Mobile performance is a product requirement.

Prefer conditional assets, WordPress responsive images, intrinsic dimensions, eager/high-priority primary Single image when appropriate, lazy non-primary images/iframes, native video with `preload="metadata"`, minimal JS/DOM, and local cacheable assets.

Do not globally enqueue feature assets, add icon fonts/heavy UI libraries, autoplay feed video, eagerly embed remote players in cards, or blindly dequeue WordPress/Elementor assets.

Never claim Lighthouse/runtime results unless actually measured.

## Accessibility + security
User-facing changes require semantic HTML, real links/buttons, visible focus, accessible names, keyboard-safe interactions, useful alt behavior, appropriate ARIA, adequate contrast/touch targets, and reduced-motion support. No clickable `<div>` or hover-only functionality.

Treat content/meta as untrusted: sanitize, escape, validate, restrict iframe providers, use KSES where appropriate, and never render arbitrary stored embed HTML.

## Relevant-file map
Open the smallest useful set.

**Mobile nav:** `template-parts/navigation/mobile-bottom-nav.php`, `assets/css/mobile-nav.css`, `inc/navigation.php`, tokens only if needed.

**Explore:** relevant Explore template/parts, `assets/css/portfolio-explore.css`, `assets/css/portfolio-card.css`, `assets/js/portfolio-explore.js`; open `inc/portfolio.php` only for data behavior.

**Single:** relevant `template-parts/portfolio/*`, `assets/css/portfolio-single.css`, `assets/js/portfolio-single.js`; open `inc/portfolio.php` only for media/data behavior.

Search before opening large files.

## Current state
Foundation through static performance/accessibility QA is implemented. Do not restart scaffold Tasks 1–25.

Known UX debt: Explore desktop CSS still contains a permanent filter/sidebar treatment; this conflicts with the full-width/no-sidebar target.

Default visual order unless the user changes priority:
1. mobile bottom-nav polish
2. Explore full-width app shell / remove desktop sidebar
3. search/filter polish
4. portfolio-card polish
5. Single media controls polish
6. Single action hierarchy
7. real-browser mobile Lighthouse/accessibility QA

The active user task always wins.

## Validation
No frontend build pipeline is required. Run only relevant checks:

- PHP: `php -l path/to/changed.php`
- JS: `node --check path/to/changed.js`
- General: `git diff --check`

For UI tasks, validate applicable mobile/desktop/RTL/focus/touch/reduced-motion/Elementor/no-JS states. Never claim browser or WordPress runtime testing unless it actually ran.

## Git + reporting
Do not overwrite unrelated user changes. Do not commit/push/merge/amend/rewrite history unless explicitly requested. Keep a task small enough for one focused commit.

Prefer commit forms such as `ui(explore): remove desktop sidebar`, `ui(single): refine media controls`, `perf(explore): reduce layout work`, `fix(portfolio): preserve media ordering`.

Final response: only concise **Summary**, **Files changed**, **Validation**, and one **Next** recommendation. Do not restate project context or paste unchanged code.

## Priority
If requirements conflict: explicit user request → data/content preservation → security/correctness → accessibility → mobile performance → existing architecture → UI/UX parity → visual polish.

Make the smallest high-quality change that moves Fahar toward a fast, accessible, Persian-first portfolio application.
