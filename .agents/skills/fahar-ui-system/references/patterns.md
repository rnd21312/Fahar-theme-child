# Fahar UI Patterns

These are composition rules, not duplicate component implementations.

## Visual source order
For visual decisions use:
1. live `fahar.ir`
2. implemented Fahar tokens/components
3. Pinterest only for missing discovery/app interaction patterns

Do not infer exact live-site values when the site cannot be visually inspected.

## Discovery / Explore
- full-width content-first feed
- prominent search without oversized chrome
- filters as compact controls/chips/triggers
- secondary filter detail may use a popover/sheet/dialog depending on viewport and complexity
- no permanent desktop filter sidebar
- masonry/grid parent owns placement; portfolio cards own their internal presentation
- preserve return position/context after visiting a portfolio
- loading, empty, error, and no-JS states must remain understandable

## Feed card
- media dominates
- title/meta support rather than compete with media
- hover may add restrained feedback, never reveal essential actions only on hover
- maintain visible keyboard focus
- video cards use lightweight indicators; do not embed/autoplay remote video in the feed

## Detail / Single Portfolio
- media first
- controls close to the media they affect
- primary media leads; secondary media follows in authoring/provider order
- avoid website-style oversized chrome around the viewer
- metadata/actions form one clear hierarchy
- description/comments/related content follow the core project/media content
- Back to Explore restores discovery context when possible

## Mobile navigation
- fixed primary navigation near the thumb zone
- compact floating form only if consistent with Fahar live identity
- safe-area aware
- selected/current state obvious without relying only on color
- labels may be visually hidden only when icons and accessible names remain unambiguous

## Overlays / sheets / popovers
Use overlays only when they reduce navigation/context loss.
- bottom sheet: preferred for complex mobile filter/action surfaces
- popover: compact desktop contextual controls
- modal/dialog: only when attention/confirmation genuinely requires interruption
- preserve focus and keyboard escape/close behavior where applicable

## Motion
Motion communicates state or spatial relationship; it is not decoration.
- fast UI feedback
- no long entrance sequences
- no motion required to understand state
- honor reduced-motion preference

## Responsive rule
Do not shrink desktop UI mechanically. Recompose hierarchy for mobile while preserving the same component language and semantic tokens.
