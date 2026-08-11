---
name: fahar-ui-system
description: Enforce the Fahar repo design system for UI work. Use for any visible UI, layout, responsive, component, navigation, interaction, or styling task in this repository.
---

# Fahar UI System

Use this skill together with UI UX Pro Max for every user-facing UI task.

## Source hierarchy
1. Live `https://fahar.ir/` — primary visual/brand reference when it can be inspected.
2. `assets/css/tokens.css` — canonical implemented semantic values.
3. `assets/css/components.css` + existing reusable component CSS — canonical implementation patterns.
4. `references/components.md` — component ownership/contracts.
5. `references/patterns.md` — composition/interaction patterns.
6. Pinterest — secondary behavior reference only for app/discovery patterns not defined by Fahar.

If the live site cannot be visually inspected, preserve existing Fahar tokens/components. Never invent exact colors, typography, radii, or brand geometry and present them as observed from the live site.

## Before coding
1. Identify the UI primitive/component/pattern being changed.
2. Read only its relevant contract/reference.
3. Search the repo for an existing implementation.
4. Reuse or extend it before creating another component.
5. Use semantic `--fahar-*` tokens; do not introduce repeated raw design values.

## Component rules
- One visual responsibility has one canonical implementation.
- Page CSS owns layout/composition, not primitive visual definitions.
- Reusable UI belongs in `assets/css/components.css` or a dedicated reusable component stylesheet.
- Use `fahar-` class names and state through semantic attributes/classes already established by the component.
- Prefer variants over copied selectors.
- Every interactive component must define relevant default, hover, active/pressed, focus-visible, selected/current, disabled, loading, and error states.
- Hover enhancements must be guarded for hover-capable pointers when touch behavior would otherwise suffer.
- Minimum practical touch target is about 44px.
- Use logical properties for RTL/LTR compatibility.
- Respect `prefers-reduced-motion`.

## Token rules
- Colors, repeated spacing, radius, motion, shadows, focus, typography, and shared dimensions come from `tokens.css`.
- Add a token only when a value is semantic/reused or is part of a coherent scale.
- Do not create page-specific tokens to hide arbitrary values.
- Prefer semantic aliases (`button`, `control`, `surface`, etc.) over coupling components to primitive palette values.

## App patterns
Fahar should feel like a focused portfolio app:
- media/content first
- compact controls
- low chrome
- persistent navigation/context where useful
- shallow interaction depth
- clear feedback and state
- progressive enhancement
- no permanent desktop sidebar

Pinterest may inform masonry/discovery/feed-detail behavior, but never overrides Fahar's brand identity.

## Performance/accessibility gate
Do not add a dependency for visual convenience if native CSS/JS can do the job.
Avoid decorative DOM, icon fonts, autoplay feed video, eager remote embeds, and global feature assets.
Use semantic HTML, real links/buttons, visible focus, accessible names, keyboard-safe behavior, adequate contrast/touch targets, and reduced-motion handling.

## Validation
For changed CSS run:
`python tools/check-ui-system.py <changed-css-files>`

Then run relevant syntax/runtime checks plus `git diff --check`.

If the checker reports an intentional exception, fix the architecture first. Add an allowlist exception only when the value/component truly cannot be represented by the design system, and document why.
