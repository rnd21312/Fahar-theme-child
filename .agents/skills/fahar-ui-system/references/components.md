# Fahar Component Contracts

Use this registry before creating or styling reusable UI.

## Primitive components

### Button
Canonical CSS: `assets/css/components.css`
Class: `.fahar-button`
Variants: `--primary`, `--secondary`, `--ghost`, `--icon`.
Use for actions. Links may use button presentation only when the destination/action hierarchy calls for it.
Do not create page-specific button systems.

### Field / Input / Textarea
Canonical CSS: `assets/css/components.css`
Classes: `.fahar-field`, `.fahar-input`, `.fahar-textarea`.
Use native form semantics and labels. Error uses `aria-invalid` plus semantic error feedback.

### Surface
Canonical CSS: `assets/css/components.css`
Classes: `.fahar-surface`, `.fahar-surface--raised`.
Use for generic contained/elevated regions. Page layouts should not invent competing generic panels.

### Divider
Canonical CSS: `assets/css/components.css`
Class: `.fahar-divider`.
Use only when spacing/hierarchy alone is insufficient.

## Complex components

### Portfolio Card
Canonical CSS: `assets/css/portfolio-card.css`
Owner: reusable feed/result media card.
Must remain media-first, RTL-safe, keyboard-focusable, touch-friendly, and lightweight. Parent layouts own columns/masonry placement.

### Mobile Bottom Navigation
Canonical CSS: `assets/css/mobile-nav.css`
Owner: mobile primary app navigation.
Must preserve safe-area handling, current-state semantics, touch targets, reduced motion, and Elementor-editor hiding.

### Explore UI
Canonical CSS/JS: `assets/css/portfolio-explore.css`, `assets/js/portfolio-explore.js`.
Owner: page composition, discovery/search/filter/feed states.
It may compose primitive components but must not redefine them. No permanent desktop sidebar.

### Single Portfolio
Canonical CSS/JS: `assets/css/portfolio-single.css`, `assets/js/portfolio-single.js`.
Owner: media viewer/detail composition.
Shared primitive actions should use/extend canonical button/control contracts rather than inventing local button systems.

## Adding a component
Create a new component only when all are true:
1. no existing component can express the role through a variant/composition;
2. it is likely to recur or has a distinct behavior/accessibility contract;
3. its API/states can be named clearly;
4. it uses semantic Fahar tokens;
5. its ownership file is obvious.

When adding one, update this registry in the same task.

## State contract
Interactive components define only relevant states, but never leave behavior accidental:
- default
- hover (hover-capable pointers)
- active/pressed
- focus-visible
- selected/current
- disabled
- loading/busy
- invalid/error where applicable

Prefer native attributes such as `disabled`, `aria-current`, `aria-pressed`, `aria-expanded`, `aria-busy`, and `aria-invalid` over cosmetic state classes when semantic attributes fit.
