# Codex Bootstrap Prompt — Fahar Theme Child

You are working inside an existing local Git repository named:

`Fahar-theme-child`

This repository was forked from the official **Hello Elementor Child Theme** starter and cloned locally.

Your task in this run is **NOT to build the full product UI yet**.

Your task is to transform the existing fork into a clean, production-oriented, maintainable development foundation for the **Fahar Experience Theme MVP**, while preserving compatibility with:

- WordPress
- Hello Elementor parent theme
- Elementor
- Elementor Pro when present
- RTL / Persian
- Existing Elementor-built pages
- Existing portfolio data and plugins
- Future custom Elementor widgets/extensions

The final repository must be ready for the next development phase.

---

# 1. Working rules

Before changing anything:

1. Inspect the entire current repository.
2. Read all existing files.
3. Run `git status`.
4. Identify what came from the upstream Hello Elementor Child repository.
5. Preserve anything required for correct child-theme behavior.
6. Do not delete working upstream files merely to match this prompt.
7. Do not modify the Hello Elementor parent theme.
8. Do not touch WordPress core.
9. Do not assume Elementor Pro is installed.
10. Do not introduce a JavaScript framework.
11. Do not introduce React/Vue/Svelte into the frontend.
12. Prefer native WordPress APIs, PHP, semantic HTML, modern CSS, and vanilla JavaScript.
13. Do not add runtime dependencies unless genuinely necessary.
14. Do not implement Portfolio Explore, likes, comments, filters, sliders, or AJAX in this task.
15. Do not create fake portfolio data.
16. Do not hardcode production URLs.
17. Do not make destructive database operations.
18. Do not edit any Elementor page content.
19. Keep the theme functional even if optional integrations are unavailable.
20. All PHP files must prevent direct access where appropriate.

If the current repository conflicts with an assumption in this document, inspect the actual code and choose the safest backward-compatible solution.

---

# 2. Project objective

This theme will eventually turn the existing WordPress website into a modern portfolio experience inspired by Pinterest while keeping the current content and Elementor editing workflow.

Future product goals include:

- `/portfolios/` Explore experience
- Pinterest-style masonry portfolio discovery
- search and filters
- portfolio detail experience
- image/video slider
- portfolio title and metadata
- expandable project description
- likes
- WordPress comments integration
- related portfolios
- return-to-explore behavior
- desktop header
- mobile bottom navigation
- no desktop sidebar
- Elementor-first editing workflow
- custom Elementor widgets and controls
- existing portfolio content reused rather than re-entered

This run is only the **foundation/scaffolding phase**.

---

# 3. Architecture decision

The current repository is the presentation layer:

`Fahar-theme-child`

It should own:

- global visual design
- CSS design tokens
- global typography
- layout primitives
- theme templates
- header/footer integration
- mobile navigation presentation
- portfolio template presentation
- Elementor theme compatibility
- Elementor global styling compatibility
- accessibility presentation
- RTL presentation
- conditional asset loading
- frontend utility JavaScript

Do NOT place large Elementor widget implementations directly into the theme during this task.

We may later build a separate companion plugin:

`fahar-elementor-core`

That future plugin will own:

- custom Elementor widgets
- custom Elementor controls
- Dynamic Tags
- AJAX endpoints
- reusable Elementor functionality
- functionality that should survive a theme switch

For now, the child theme only needs clean extension points for that future plugin.

---

# 4. Naming conventions

Use these identifiers consistently unless the existing repository already defines a safer compatible alternative:

Theme display name:

`Fahar Theme Child`

Theme slug / text domain:

`fahar-theme-child`

PHP function prefix:

`fahar_theme_`

PHP constants prefix:

`FAHAR_THEME_`

CSS class prefix:

`fahar-`

CSS custom property prefix:

`--fahar-`

JavaScript global namespace should be avoided when possible.

If a global namespace becomes necessary, use:

`FaharTheme`

Do not use generic global function names.

---

# 5. Required repository structure

Create or normalize the repository toward this structure.

Do not create meaningless empty PHP files just to satisfy the tree. Each PHP file that exists must have a clear responsibility and a safe minimal implementation.

```text
Fahar-theme-child/
├── style.css
├── functions.php
├── theme.json
├── screenshot.png
├── README.md
├── CHANGELOG.md
├── LICENSE
├── .editorconfig
├── .gitignore
│
├── inc/
│   ├── setup.php
│   ├── assets.php
│   ├── helpers.php
│   ├── compatibility.php
│   ├── elementor.php
│   ├── portfolio.php
│   ├── navigation.php
│   └── performance.php
│
├── assets/
│   ├── css/
│   │   ├── tokens.css
│   │   ├── reset.css
│   │   ├── globals.css
│   │   ├── typography.css
│   │   ├── layout.css
│   │   ├── components.css
│   │   ├── elementor.css
│   │   ├── header.css
│   │   ├── footer.css
│   │   ├── mobile-nav.css
│   │   ├── portfolio-explore.css
│   │   ├── portfolio-single.css
│   │   └── utilities.css
│   │
│   └── js/
│       ├── app.js
│       ├── navigation.js
│       ├── search.js
│       ├── portfolio-explore.js
│       └── portfolio-single.js
│
├── template-parts/
│   ├── header/
│   │   └── site-header.php
│   ├── footer/
│   │   └── site-footer.php
│   ├── navigation/
│   │   └── mobile-bottom-nav.php
│   └── portfolio/
│       ├── card.php
│       ├── empty-state.php
│       └── related-grid.php
│
├── templates/
│   ├── page-portfolios.php
│   └── portfolio-single.php
│
├── docs/
│   ├── ARCHITECTURE.md
│   ├── DEVELOPMENT.md
│   ├── DESIGN-SYSTEM.md
│   ├── ELEMENTOR-INTEGRATION.md
│   ├── PORTFOLIO-INTEGRATION.md
│   └── ROADMAP.md
│
└── languages/
    └── .gitkeep
```

Important:

- If the existing official starter uses a different safe bootstrap pattern, preserve compatibility.
- Do not blindly duplicate WordPress templates if they are not needed yet.
- Placeholder frontend files should contain only documented scaffolding, not fake UI.
- It is acceptable for files such as `portfolio-explore.css` to contain a short header comment and no feature implementation yet.

---

# 6. Bootstrap architecture

Refactor `functions.php` into a very small bootstrap file.

Its responsibility should be approximately:

1. deny direct execution if needed
2. define stable theme constants
3. load files from `/inc`
4. avoid application logic inside `functions.php`

Create constants for at least:

- theme version
- theme directory path
- theme directory URI

Use `wp_get_theme()` where appropriate instead of duplicating version numbers unnecessarily.

Example conceptual constants:

- `FAHAR_THEME_VERSION`
- `FAHAR_THEME_DIR`
- `FAHAR_THEME_URI`

Do not copy this literally if there is a more WordPress-native implementation.

---

# 7. inc/setup.php

Create a theme setup module that safely handles relevant child-theme setup.

It should:

- run on the correct WordPress hook
- load the text domain when appropriate
- declare only relevant theme support
- remain compatible with Hello Elementor
- avoid redefining features already handled correctly by the parent unless required
- prepare the codebase for future image sizes without adding arbitrary sizes now
- document why each theme support entry exists

Do not register unnecessary menus or sidebars yet.

Our final product intentionally has no Pinterest-style desktop sidebar.

---

# 8. inc/assets.php

Create a centralized asset registration/enqueue layer.

Requirements:

- enqueue child-theme CSS safely
- maintain correct dependency on the parent/Hello Elementor styles only if needed based on the actual parent behavior
- enqueue CSS in a deterministic order
- use file versions suitable for cache busting during development
- prefer `filemtime()` in development-safe code when appropriate, with a fallback
- do not enqueue every future portfolio JS file globally
- prepare helper functions for conditional loading
- frontend JS should use `defer` where safely possible
- do not load jQuery merely for convenience
- do not remove Elementor assets
- do not dequeue WordPress/Elementor assets as an optimization shortcut

Global CSS expected to be safe to enqueue:

- tokens.css
- reset.css
- globals.css
- typography.css
- layout.css
- components.css
- elementor.css
- utilities.css

Conditional assets should be architected for:

- header/footer if required
- mobile navigation
- portfolio Explore
- portfolio Single

Do not implement route detection based on a guessed portfolio post type. Put that behind integration helpers.

---

# 9. Design token foundation

Create `assets/css/tokens.css`.

Do not attempt the final brand design yet.

Create a sensible neutral token foundation that can later be changed from one place.

Token groups should include:

- colors
- surfaces
- typography families
- font weights
- font sizes using `clamp()` where useful
- spacing scale
- border radii
- shadows
- container widths
- z-index layers
- transitions/easing
- header dimensions
- mobile bottom navigation dimensions
- focus ring
- breakpoints only if CSS custom properties are actually useful for them

Use logical CSS properties where possible for RTL/LTR compatibility.

Example naming direction:

```css
--fahar-color-bg
--fahar-color-surface
--fahar-color-text
--fahar-color-muted
--fahar-color-border
--fahar-radius-sm
--fahar-radius-md
--fahar-radius-lg
--fahar-space-1
--fahar-space-2
--fahar-container
--fahar-motion-fast
```

Do not lock the brand into hardcoded Pinterest red.

We are inspired by Pinterest UX, not cloning Pinterest branding.

---

# 10. RTL-first requirements

The website is Persian/RTL.

The theme must:

- work correctly when WordPress is RTL
- use logical properties such as `margin-inline`, `padding-inline`, `inset-inline`, etc. where sensible
- not hardcode left/right when start/end is the semantic requirement
- maintain LTR compatibility for future multilingual use
- avoid flipping icons that should not be directionally mirrored
- document RTL decisions in `docs/DESIGN-SYSTEM.md`

Do not create a separate `rtl.css` unless there is a real need.

Prefer logical CSS architecture.

---

# 11. Elementor compatibility foundation

Create `inc/elementor.php`.

This task does not require custom Elementor widgets yet.

The module should:

- detect whether Elementor is active safely
- never cause a fatal error if Elementor is absent
- provide clear reusable helpers for future Elementor integration
- document expected responsibilities
- not rely on Elementor Pro
- prepare future hooks for:
  - widget extensions
  - Theme Builder compatibility
  - global styles
  - custom widget plugin integration

Do not modify Elementor core classes.

Do not monkey patch Elementor.

Do not duplicate core Elementor widgets.

Do not implement injected controls yet.

Add documentation to:

`docs/ELEMENTOR-INTEGRATION.md`

Explain the future split between the child theme and `fahar-elementor-core`.

---

# 12. Portfolio integration foundation

Create `inc/portfolio.php`.

Critical rule:

The exact portfolio plugin/post type must be discovered from the real site later.

Do not hardcode guessed values like `astra-portfolio` throughout the theme.

Instead:

- create a small integration boundary
- provide filterable helper functions for resolving:
  - portfolio post type
  - portfolio taxonomies
  - whether current screen is portfolio Explore
  - whether current object is a portfolio item
- use safe fallbacks
- document TODOs clearly

Provide filters with stable Fahar-prefixed names so later site-specific integration can override them.

For example, conceptually:

- portfolio post type filter
- portfolio taxonomy filter
- portfolio page ID/slug filter

Do not build the actual query yet.

Document expected integration in:

`docs/PORTFOLIO-INTEGRATION.md`

The documentation must emphasize:

- existing portfolio content will be reused
- no data migration is part of the current MVP foundation
- actual field mapping must happen after auditing the production/staging plugin data

---

# 13. Navigation foundation

Create `inc/navigation.php`.

Prepare hooks/helpers for:

- desktop header
- mobile bottom navigation
- active navigation state
- accessibility labels

Do not build a complicated menu system.

Do not register a desktop sidebar.

The mobile bottom navigation should eventually support:

- Home
- Explore
- Search
- Contact / primary action

For this task only create semantic template scaffolding and safe fallback behavior.

---

# 14. Template parts

Create minimal semantic template scaffolds.

## site-header.php

Should be:

- semantic
- accessible
- minimal
- compatible with a future Elementor Theme Builder override
- not visually elaborate yet

## site-footer.php

Same principles.

## mobile-bottom-nav.php

Create accessible semantic markup only.

Requirements:

- `<nav>`
- useful `aria-label`
- no icon library dependency
- no hardcoded SVG icon system yet unless the current repo already has one
- easy for future iteration

## portfolio/card.php

Create a defensive placeholder API for rendering a portfolio card from a valid WordPress post object or post ID.

Do not assume portfolio meta fields yet.

At most render:

- permalink
- featured image if available
- title

Mark extension points/TODOs for future media type, badges, video indicator, etc.

Escape all output correctly.

## portfolio/empty-state.php

Minimal accessible empty state.

## portfolio/related-grid.php

Only establish the template boundary.

Do not implement recommendation logic yet.

---

# 15. Template scaffolds

Create template scaffolding for:

`templates/page-portfolios.php`

and

`templates/portfolio-single.php`

But do NOT automatically force WordPress to use them for unknown content types yet.

The Explore template should only provide the future structural regions:

- header / app shell
- explore main
- search region
- filters region
- grid region
- load-more region
- mobile navigation

The single portfolio template should only provide future structural regions:

- back navigation
- media region
- portfolio title/meta
- social actions region
- expandable description region
- comments region
- related portfolios
- return to Explore

Use semantic HTML and WordPress escaping.

Avoid fake text and fake images.

---

# 16. CSS architecture

CSS responsibilities:

## reset.css
Only safe theme-level normalization. Do not fight Elementor's editor.

## globals.css
Body-level and global frontend rules.

## typography.css
Global typography primitives.

## layout.css
Containers and layout primitives.

## components.css
Reusable low-level visual components.

## elementor.css
Minimal compatibility layer only.
Do not aggressively target generated Elementor classes.
Prefer stable public selectors and Fahar wrapper classes.

## header.css
Future header styles.

## footer.css
Future footer styles.

## mobile-nav.css
Future bottom navigation styles.

## portfolio-explore.css
Scaffold for future Pinterest-inspired Explore UI.

## portfolio-single.css
Scaffold for future portfolio detail UI.

## utilities.css
Only a very small set of documented utilities.
Do not create a Tailwind clone.

---

# 17. JavaScript architecture

Use vanilla JS.

All JS must:

- be safe if expected DOM elements are absent
- avoid global pollution
- initialize on `DOMContentLoaded` or appropriate lifecycle
- include guards
- support multiple component instances where appropriate
- respect reduced-motion preferences in future animation work

For now:

## app.js
Minimal global initialization.

## navigation.js
Navigation scaffold only.

## search.js
Search UI scaffold only. No AJAX implementation.

## portfolio-explore.js
Explore behavior scaffold only.

## portfolio-single.js
Single portfolio behavior scaffold only.

Add concise module documentation comments.

Do not implement fake functionality.

---

# 18. Accessibility foundation

The scaffold must establish these rules:

- visible keyboard focus
- semantic landmarks
- buttons must be real buttons
- links must be real links
- interactive divs are prohibited
- images need appropriate alt handling
- UI should not depend only on hover
- future animations must support `prefers-reduced-motion`
- adequate tap target foundation for mobile navigation
- expandable descriptions should later use `aria-expanded`
- sliders should later be keyboard accessible

Document this in `docs/DEVELOPMENT.md`.

---

# 19. Security and WordPress coding standards

Use:

- escaping on output
- sanitization on input when input exists
- nonces for future mutations/AJAX
- capability checks for future admin operations
- WordPress APIs instead of direct SQL
- `ABSPATH` guards where appropriate

Do not add AJAX endpoints in this run.

Do not add REST endpoints in this run.

Do not create custom database tables.

---

# 20. Performance rules

Scaffold with these principles:

- no unnecessary dependencies
- no jQuery dependency for theme code
- no globally loaded portfolio slider library
- no globally loaded Masonry library unless proven necessary later
- no icon font
- no huge utility framework
- no CSS framework
- no duplicate Elementor CSS
- conditional portfolio assets
- lazy-loading compatible markup
- no autoplay video in portfolio grids
- future video cards should use thumbnails until interaction

Document this in:

`docs/ARCHITECTURE.md`

---

# 21. theme.json

Create or normalize `theme.json` conservatively.

Important:

- do not override Elementor design controls aggressively
- use it to provide sane WordPress editor defaults where useful
- avoid creating duplicate/conflicting design systems
- document the relationship between:
  - CSS Fahar tokens
  - WordPress theme.json
  - Elementor Global Colors/Fonts

The Fahar CSS token layer is the architectural source of frontend design values for this project.

If exact automatic synchronization with Elementor globals is not possible without brittle behavior, document the limitation rather than hacking it.

---

# 22. Documentation

Create useful documentation, not filler.

## README.md

Include:

- project name
- parent theme requirement
- purpose
- installation
- development status
- architecture overview
- directory overview
- dependencies
- current MVP phase
- contribution/development notes

## docs/ARCHITECTURE.md

Include:

- child theme responsibilities
- future companion plugin responsibilities
- module boundaries
- dependency principles
- why functionality is separated from presentation
- performance strategy

## docs/DEVELOPMENT.md

Include:

- local development workflow
- coding conventions
- naming rules
- how to add a new module
- how to add CSS
- how to add JS
- testing checklist
- accessibility rules

## docs/DESIGN-SYSTEM.md

Include:

- token groups
- RTL strategy
- responsive strategy
- typography strategy
- component philosophy

## docs/ELEMENTOR-INTEGRATION.md

Include:

- current Elementor compatibility
- rules against overriding Elementor core
- future `fahar-elementor-core`
- Theme Builder compatibility
- future widget-extension plan

## docs/PORTFOLIO-INTEGRATION.md

Include:

- existing-data-first approach
- no re-entry / no migration goal
- integration boundary
- post type/taxonomy discovery requirement
- future mapping checklist

## docs/ROADMAP.md

Include this high-level roadmap:

### Phase 0
Repository bootstrap — current task

### Phase 1
Site/staging audit and portfolio data mapping

### Phase 2
Fahar design system + global app shell

### Phase 3
`/portfolios/` Pinterest-inspired Explore

### Phase 4
Single Portfolio Pinterest-inspired detail experience

### Phase 5
Search, filtering, Load More, state restoration

### Phase 6
WordPress comments + likes architecture

### Phase 7
Related portfolio ranking

### Phase 8
Fahar Elementor Core custom widgets

### Phase 9
Performance/accessibility QA and production release

---

# 23. Git hygiene

Do not commit automatically unless the environment explicitly expects commits.

Before finishing:

1. show `git status`
2. show created files
3. show modified files
4. run syntax/lint checks available locally
5. if PHP CLI is installed, run `php -l` on all PHP files
6. verify there are no obvious syntax errors
7. do not hide warnings
8. do not modify unrelated files

If GitHub Actions or other CI already exists, preserve it.

Do not introduce a complicated CI system in this task.

---

# 24. Development tooling

Keep tooling lightweight.

Add `.editorconfig`.

Normalize `.gitignore` only if needed.

Do not add Node/npm/Composer merely because they are common.

If the repository already uses them, preserve and improve the existing setup.

If no build process exists, keep CSS and vanilla JS directly editable for now.

We can add a build pipeline later if the project actually requires one.

---

# 25. Compatibility expectations

The theme must remain safe when:

- Elementor is active
- Elementor is inactive
- Elementor Pro is absent
- portfolio integration is not configured yet
- a page has no featured image
- a menu is not assigned
- JavaScript is unavailable
- the website is in RTL
- WordPress debug mode is enabled

Do not suppress PHP errors globally.

---

# 26. Explicit non-goals for this run

Do NOT implement:

- final Pinterest UI
- masonry algorithm
- Infinite Scroll
- Load More AJAX
- live search
- filters
- likes storage
- comments customization
- sliders/carousels
- related portfolio algorithm
- custom Elementor widgets
- custom Elementor controls
- portfolio meta migration
- custom REST endpoints
- user accounts
- saves/boards
- notifications
- PWA
- service workers

Only scaffold clean extension points for these.

---

# 27. Acceptance criteria

The task is complete only if:

- the repository remains a valid Hello Elementor child theme
- WordPress can activate the child theme without fatal errors
- existing child-theme behavior is preserved
- `functions.php` is a clean bootstrap
- modules are split under `/inc`
- CSS architecture exists
- JS architecture exists
- design tokens exist
- Elementor compatibility is defensive
- portfolio integration does not hardcode unknown production data
- template parts are semantic and safe
- RTL is treated as first-class
- documentation exists and is useful
- no unnecessary framework has been added
- no fake portfolio implementation has been added
- no content/database migration has occurred
- PHP syntax checks pass
- the final Git diff is understandable

---

# 28. Execution sequence

Perform the work in this order:

1. Inspect repository.
2. Summarize current state.
3. Create a short implementation plan.
4. Preserve/normalize child-theme metadata.
5. Refactor bootstrap architecture.
6. Create `/inc` modules.
7. Create CSS architecture and design tokens.
8. Create JS scaffolding.
9. Create template parts.
10. Create template scaffolds.
11. Add Elementor compatibility foundation.
12. Add portfolio integration boundary.
13. Add RTL/accessibility foundations.
14. Write documentation.
15. Run validation.
16. Inspect Git diff.
17. Fix any issues found.
18. Provide a final implementation report.

Do not stop after merely generating folders.

Actually create the files and make the repository internally coherent.

---

# 29. Final response format

When you finish, report:

## Summary
What you changed.

## Repository structure
Show the final relevant tree.

## Important architecture decisions
Briefly explain the major choices.

## Validation
List checks you ran and results.

## Files changed
Separate created and modified files.

## TODO — next development phase
List only the next actionable tasks:

1. audit actual portfolio plugin/data
2. map portfolio post type/taxonomies/meta
3. implement final Fahar design system
4. build global header + mobile bottom nav
5. build `/portfolios/` Explore MVP
6. build Single Portfolio MVP

If something could not be validated locally, say so explicitly.

Do not claim WordPress/Elementor runtime compatibility was tested unless you actually ran the site.
