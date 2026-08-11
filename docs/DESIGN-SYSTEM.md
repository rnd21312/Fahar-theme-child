# Design system

Fahar uses a minimal, dark-by-default visual system for a Persian-first portfolio experience. Warm near-black surfaces keep media prominent; Desert Gold provides selective emphasis without becoming a dominant background color. CSS custom properties in `assets/css/tokens.css` are the frontend source of truth.

## Color

The palette progresses from the page background through three surface levels, paired with primary, secondary, and muted warm-neutral text. Borders use low-opacity neutral values so structure remains visible without making the dark interface noisy. Success, warning, and error have distinct semantic tokens.

`--fahar-color-desert-gold` is the provisional brand accent because no approved Fahar gold exists in the repository yet. Use it for links, focus, selected states, and occasional primary emphasis—not large surfaces or routine decoration. Hover and active values are centralized beside the base token so the approved brand value can replace them coherently later.

## Typography

The initial stack uses local system fonts only: Tahoma first for dependable Persian rendering, followed by Segoe UI, Arial, and the generic sans-serif fallback. Body and metadata sizes remain stable for readability; the heading scale uses restrained `clamp()` ranges. Persian body copy uses a generous line height while headings stay compact.

## Spacing, radius, and layout

Spacing follows a compact 4px-based eight-step scale. Small and medium radii suit controls and compact surfaces; large and extra-large radii are reserved for meaningful containers. Pill and round tokens should be used only when the component shape requires them.

Container, page-padding, header-height, and mobile-navigation-height tokens define future layout dimensions without styling those components in this phase.

## Motion and depth

Motion is fast and subtle: 120ms for immediate feedback, 200ms for normal transitions, and 320ms for deliberate state changes. The emphasized curve is reserved for meaningful transitions. Dark-mode shadows use neutral black opacity and avoid colored glow.

## RTL strategy

Tokens are direction-neutral. Components must use logical properties such as `padding-inline`, `margin-inline`, and `inset-inline`, allowing Persian RTL and future LTR layouts to share the same values. Directional icons are evaluated individually rather than mirrored globally.

## Global dark foundation

Normal WordPress pages inherit the warm dark background, readable primary text, Persian-first system font stack, restrained gold links, visible keyboard focus, and reduced-motion safeguards. Document selectors stay low-specificity so Elementor's local typography, color, and spacing controls remain authoritative.

## Primitives

Typography classes cover display, three heading levels, body, small, metadata, and muted text. The global heading defaults are intentionally restrained and use `:where()` to avoid competing with page-builder controls.

Layout primitives provide mobile-first page padding, content/wide/narrow containers, vertical stacks, wrapping clusters, and an unopinionated grid. They use logical properties and do not encode a sidebar, header, or portfolio layout. The single `48rem` foundation breakpoint matches the existing scaffold and only increases page padding for tablet and desktop widths.

Buttons are opt-in through `.fahar-button` and its primary, secondary, ghost, and icon variants. Gold is reserved for the primary variant; other controls use neutral surfaces. Fields, inputs, and textareas share dark surfaces, readable placeholders, disabled states, and an `aria-invalid` border state without implementing validation behavior.

Surfaces use subtle borders and neutral shadows. Raised surfaces add one level of contrast without transparency or glass effects. Keyboard focus uses the shared gold ring, controls retain adequate target height, and motion is limited to short state transitions.
