# AGENTS.md — Fahar Theme Child

## Project

Repository: `Fahar-theme-child`

Base: Official Hello Elementor Child Theme

Project goal: build a modern, Pinterest-inspired, web-app-like WordPress portfolio experience while preserving existing Elementor pages and existing portfolio data.

This repository is the presentation/theme layer. Do not treat it as a general-purpose application plugin.

---

## Core Product Direction

The Fahar UI is:

- Dark mode by default
- Minimal
- Modern
- RTL-first
- Persian-first
- Web-app-like
- Pinterest-inspired for portfolio discovery
- Mobile-friendly
- Accessible
- Performance-conscious

Primary UI accent / secondary brand color:

- Desert Gold

Do not use Pinterest brand red or visually clone Pinterest branding.
The inspiration is interaction and information architecture, not brand imitation.

---

## Main UX Targets

Future product areas include:

- `/portfolios/` Explore page
- Pinterest-style masonry portfolio feed
- Search
- Filters
- Load More / progressive loading
- Portfolio detail page
- Media slider
- Portfolio title
- Like action
- WordPress comments
- Expandable description
- Related portfolios
- Back to Explore
- Desktop header
- Mobile bottom navigation
- No desktop sidebar

Do not implement future features unless the active task explicitly requests them.

---

## Existing Site Must Be Preserved

Never assume we are rebuilding the site from scratch.

Preserve:

- Existing Elementor pages
- Existing Elementor content
- Existing Elementor templates
- Existing portfolio items
- Existing portfolio media
- Existing portfolio URLs when possible
- Existing taxonomies
- Existing SEO data
- Existing WordPress content

Do not migrate or re-enter portfolio data unless explicitly requested in a dedicated migration task.

Do not modify Elementor page content unless the task explicitly requires it.

---

## Theme Architecture

Parent theme:

`hello-elementor`

Child theme:

`Fahar-theme-child`

The child theme owns:

- Global visual design
- CSS design tokens
- Typography
- Layout primitives
- Header/footer presentation
- Mobile navigation presentation
- Portfolio templates
- Elementor compatibility styling
- RTL behavior
- Accessibility presentation
- Conditional frontend assets
- Theme template parts

A future companion plugin may be used:

`fahar-elementor-core`

That plugin should own functionality that should survive a theme switch, such as:

- Custom Elementor widgets
- Elementor controls
- Dynamic Tags
- AJAX endpoints
- Reusable Elementor functionality
- Application behavior not strictly tied to presentation

Do not move major reusable functionality into the theme unless explicitly required.

---

## Elementor Rules

Elementor is a first-class dependency of the current site experience.

Requirements:

- Preserve Elementor editing workflow
- Do not modify Elementor core
- Do not monkey patch Elementor
- Do not duplicate core Elementor widgets
- Do not assume Elementor Pro is available
- Never cause a fatal error when Elementor is unavailable
- Prefer stable public Elementor hooks/APIs
- Keep custom integration defensive
- Avoid brittle selectors against generated Elementor markup
- Prefer Fahar wrapper classes and stable selectors

When building custom Elementor functionality later, prefer the companion plugin instead of the theme.

---

## UI Design Rules

### Theme

Default visual mode:

`Dark`

Accent:

`Desert Gold`

### Visual principles

- High-quality dark surfaces
- Strong but comfortable contrast
- Desert Gold used intentionally, not everywhere
- Avoid excessive glow
- Avoid excessive gradients
- Avoid glassmorphism unless a specific component benefits from it
- Avoid visual clutter
- Favor content-first portfolio presentation
- Generous whitespace
- Soft but clear radius system
- Minimal shadows in dark mode
- Motion should feel subtle and fast

### Pinterest inspiration

Use Pinterest as UX inspiration for:

- Discovery
- Masonry layout
- Detail flow
- Related content
- Back-to-feed behavior
- Mobile exploration

Do not copy:

- Pinterest logo
- Pinterest red
- proprietary assets
- exact branded UI

---

## Mandatory UI/UX Workflow — Task 26 and Later

Starting with Task 26, every task that changes visible UI, interaction design, layout, responsive behavior, component styling, or UX flow must follow this workflow.

### Required Codex skill

Before inspecting or editing UI/UX code, Codex must load and apply the installed **UI UX Pro Max** skill.

Preferred registered skill name / slug:

`ui-ux-pro-max`

If the installed skill uses a slightly different registered slug, use the installed skill that clearly matches **UI UX Pro Max**.

This is mandatory for UI/UX tasks.

If UI UX Pro Max is not available in the Codex environment:

- do not silently continue with a substitute
- stop the UI/UX implementation
- report that the required skill could not be loaded

Non-visual backend/data-only tasks do not need this skill unless they also change user-facing interaction.

### Pinterest is the primary UI/UX reference

For Task 26 and all later visual tasks, treat Pinterest's portfolio/discovery interaction model as the primary reference.

Aim for high-fidelity parity in the areas relevant to Fahar:

- visual density
- whitespace rhythm
- masonry behavior
- card proportions
- image-first presentation
- card hover/focus behavior
- search placement and prominence
- filter/chip behavior
- content hierarchy
- feed-to-detail navigation
- detail-page rhythm
- related-content presentation
- mobile exploration flow
- navigation hierarchy
- loading/empty/error states
- responsive behavior
- micro-interactions
- perceived simplicity

Do not merely make the UI "Pinterest-inspired". For relevant portfolio surfaces, make the interaction model and visual composition as close to Pinterest as practical while keeping the Fahar brand system.

### Fahar brand adaptation

Pinterest is the UX/layout reference, but Fahar remains the product identity.

Keep:

- Dark mode
- Desert Gold accent
- Fahar typography
- Fahar logo
- Fahar content
- RTL/Persian-first behavior

Do not copy or ship:

- Pinterest logo
- Pinterest name/wordmark
- Pinterest red as the brand accent
- proprietary Pinterest artwork/assets
- screenshots as production UI
- copied source code

Match the **experience, hierarchy, proportions, behavior, and interaction patterns**, not the third-party brand identity.

### Reference-first implementation

Before changing a user-facing component, Codex should:

1. Load UI UX Pro Max.
2. Read the active task and only the relevant Fahar files.
3. Identify the equivalent Pinterest interaction/pattern for that surface.
4. Compare the existing Fahar component against that pattern.
5. Implement the smallest diff that meaningfully improves parity.
6. Preserve Fahar Dark + Desert Gold branding.
7. Validate desktop, mobile, RTL, keyboard/focus, and reduced-motion behavior when relevant.

Do not invent an unrelated visual direction when a clear Pinterest pattern exists.

### Task prompt requirement

Every future UI/UX task prompt must explicitly include:

`Mandatory: load and apply the UI UX Pro Max skill before making UI/UX decisions.`

and:

`Use Pinterest as the primary high-fidelity UI/UX reference; preserve Fahar Dark + Desert Gold branding.`

This requirement remains active unless a later task explicitly defines a component-specific exception.

---

## Design Token Rules

All global design values should come from Fahar CSS custom properties.

Prefix:

`--fahar-`

Token categories should include:

- Background
- Surface levels
- Text primary
- Text secondary
- Text muted
- Border
- Desert Gold accent
- Accent hover/active
- Success
- Warning
- Error
- Spacing
- Radius
- Shadows
- Typography scale
- Font weights
- Container widths
- Motion durations
- Easing
- Z-index
- Header dimensions
- Mobile navigation dimensions
- Focus ring

Avoid scattering raw repeated colors and spacing values throughout component styles.

---

## RTL Rules

The site is Persian and RTL-first.

Use logical CSS properties whenever appropriate:

- `margin-inline`
- `padding-inline`
- `inset-inline`
- `border-inline`
- `text-align: start/end`

Avoid hardcoding `left` and `right` when the semantic meaning is start/end.

Maintain future LTR compatibility.

Do not mirror icons that should remain visually fixed.

---

## CSS Rules

Use modern CSS.

Preferred:

- CSS Custom Properties
- Logical properties
- `clamp()` where useful
- Grid
- Flexbox
- `aspect-ratio`
- `prefers-reduced-motion`
- `:focus-visible`

Avoid:

- CSS frameworks unless explicitly approved
- Tailwind-like utility explosion
- excessive `!important`
- deep brittle Elementor selectors
- duplicated design values
- global rules that break Elementor Editor

CSS class prefix:

`fahar-`

Keep CSS modular by responsibility.

---

## JavaScript Rules

Default:

Vanilla JavaScript.

Do not add React, Vue, Svelte, Alpine, jQuery, or other libraries unless the active task proves they are necessary.

Requirements:

- No global pollution
- Safe initialization
- Guard against missing DOM elements
- Support multiple component instances where relevant
- Respect reduced motion
- Progressive enhancement
- No frontend fatal errors
- No fake AJAX behavior
- Do not load feature scripts globally when conditional loading is possible

If a global namespace becomes unavoidable:

`FaharTheme`

---

## PHP Rules

Follow WordPress coding and security conventions.

Use:

- WordPress APIs
- Output escaping
- Input sanitization
- Nonces for mutations
- Capability checks
- `ABSPATH` guards where appropriate

Function prefix:

`fahar_theme_`

Constants prefix:

`FAHAR_THEME_`

Avoid generic function names.

Do not:

- query the database directly when WordPress APIs are sufficient
- suppress PHP errors globally
- create custom tables unless explicitly required
- add REST/AJAX endpoints outside a dedicated task

---

## Performance Rules

Performance is a product requirement.

Do not:

- enqueue every script everywhere
- add heavy libraries without justification
- load slider libraries globally
- autoplay portfolio video in grids
- add icon fonts
- duplicate Elementor CSS
- dequeue Elementor/WordPress assets blindly

Prefer:

- Conditional asset loading
- Native browser capabilities
- Lazy-loaded media
- Portfolio thumbnails in feeds
- Minimal JS
- Minimal DOM
- Cache-friendly assets
- Feature-scoped scripts/styles

---

## Accessibility Rules

Accessibility is required, not optional.

Use:

- Semantic landmarks
- Real buttons for actions
- Real links for navigation
- Visible keyboard focus
- `:focus-visible`
- Accessible labels
- Useful image alt behavior
- Adequate mobile tap targets
- Keyboard-safe interactions
- `aria-expanded` for expandable UI
- Reduced-motion support

Do not use clickable `<div>` elements.

Do not make functionality hover-only.

---

## Portfolio Data Rules

Existing portfolio data must be reused.

Do not hardcode an assumed portfolio post type until it has been audited on the real site/staging environment.

Keep portfolio integration behind helper functions / filters.

Future integration should discover and map:

- Portfolio post type
- Taxonomies
- Featured image
- Media source
- Video fields
- Website URL
- Description
- Gallery
- Existing metadata

No data migration in normal UI-development tasks.

---

## Explore UX Target

Route:

`/portfolios/`

Future Explore requirements:

- Pinterest-like masonry feed
- Dark UI
- Desert Gold accent
- Responsive grid
- 2-column mobile layout
- Search
- Filters
- Media type awareness
- Load More
- Skeleton states
- Empty states
- Good keyboard/focus behavior
- Preserve/restore Explore context when returning from a portfolio

No sidebar.

Desktop uses header navigation.

Mobile uses bottom navigation.

---

## Single Portfolio UX Target

Future detail experience:

- Pinterest-inspired detail page
- No sidebar
- Header on desktop
- Bottom navigation on mobile
- Main media slider
- First image acts as primary cover
- Portfolio title
- Like action
- Comment action
- WordPress comments
- Expandable/collapsible description
- Optional external website/video action
- Related portfolio section
- Two-column related grid
- Back to Explore action

Implement these as separate small tasks, not one large feature task.

---

## Codex Tasking Rules

This project is developed with Codex / GPT-5.6 Sol.

Default reasoning target:

`Medium`

Optimize prompts and implementation for low token usage.

### Every Codex task must be small

A task should ideally modify one logical area.

Good:

- Add Design Tokens v1
- Build header shell
- Build mobile bottom navigation markup
- Audit portfolio data adapter
- Build portfolio card markup
- Add Explore masonry CSS
- Implement description toggle

Bad:

- Build the whole Pinterest portfolio platform
- Redesign the entire site
- Implement Explore, Single, search, likes, comments and Elementor widgets together

### Task rules

For every task:

1. Read this `AGENTS.md`.
2. Inspect only the files relevant to the active task.
3. Do not re-audit the entire repository unless required.
4. Do not implement future tasks.
5. Do not refactor unrelated code.
6. Keep diffs small and reviewable.
7. Use existing project patterns.
8. Validate the changed area.
9. Report only:
   - summary
   - files changed
   - validation
   - next recommended task

Avoid long implementation essays.

---

## Token Efficiency Rules for Codex

To reduce token usage:

- Do not restate all project context in every prompt.
- Treat this file as persistent project context.
- Prompts should reference this file instead.
- Inspect only relevant source files.
- Avoid dumping full repository trees unless needed.
- Avoid reading generated/vendor files.
- Avoid repeating unchanged code in final responses.
- Prefer targeted diffs.
- Keep final reports concise.
- Do not produce speculative architecture when the active task is implementation.
- Do not write documentation unrelated to the task.
- Do not create extra abstractions “for later” unless they clearly reduce current complexity.

---

## Git Rules

Before editing:

- Check current working tree for relevant uncommitted changes.

Do not overwrite unrelated user changes.

Do not commit automatically unless explicitly asked.

At task end:

- Review diff
- Check unintended modifications
- Run relevant validation
- Report changed files

Each Codex task should be suitable for one small commit.

---

## Validation Rules

Run the smallest validation appropriate to the task.

Examples:

PHP task:
- `php -l` on changed PHP files

CSS task:
- inspect syntax
- check obvious selector/token errors

JS task:
- syntax check if tooling exists
- ensure no unguarded DOM access

Theme bootstrap task:
- PHP syntax
- WordPress-safe hooks
- no fatal dependency assumptions

Do not claim browser/WordPress runtime testing unless it was actually performed.

---

## Current Development Sequence

Use this as the default roadmap unless the user changes priorities.

1. Repository bootstrap — completed
2. Design Tokens v1
3. Global Dark UI Foundation
4. Desktop Header
5. Mobile Bottom Navigation
6. Audit real portfolio data
7. Portfolio integration adapter
8. Portfolio card
9. `/portfolios/` Explore layout
10. Explore responsive masonry
11. Explore search UI
12. Explore search behavior
13. Filter UI
14. Filter behavior
15. Single Portfolio shell
16. Portfolio media slider
17. Portfolio title/actions
18. Expandable description
19. WordPress comments integration
20. Likes architecture
21. Related portfolio query
22. Related portfolio two-column grid
23. Back-to-Explore state restoration
24. Elementor integration QA
25. Performance/accessibility QA

Do not skip ahead unless explicitly requested.

---

## Current Next Task

The expected next task after repository scaffolding is:

`Design Tokens v1`

Scope:

- Dark-mode token foundation
- Desert Gold accent
- Typography tokens
- Spacing
- Radius
- Shadows
- Layout/container tokens
- Motion
- Focus
- Header/mobile-nav dimensions

Do not build actual header, navigation, portfolio card, Explore UI, or Single Portfolio UI in that task.

---

## Definition of Good Work

Changes should be:

- Small
- Clear
- Reversible
- WordPress-native
- Elementor-safe
- RTL-safe
- Accessible
- Performance-conscious
- Consistent with Dark + Desert Gold design direction
- Easy for the next Codex task to build on
