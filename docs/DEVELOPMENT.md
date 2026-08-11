# Development

## Workflow

1. Install WordPress and Hello Elementor locally, then activate this child theme.
2. Enable `WP_DEBUG` and exercise the site with Elementor active and inactive.
3. Make focused edits without generated build output.
4. Run PHP syntax checks, validate `theme.json`, review browser console output, and inspect `git diff --check`.
5. Test both RTL and LTR, keyboard-only navigation, narrow mobile viewports, and Elementor editor mode.

## Conventions

Use `fahar_theme_` for PHP functions, `FAHAR_THEME_` for constants, `fahar-` for classes, `--fahar-` for CSS properties, and `fahar_theme_*` for hooks. Files deny direct access where applicable. Escape at output, sanitize at input, use nonces for mutations, check capabilities for privileged work, and use WordPress APIs rather than direct SQL.

To add a PHP module, create one guarded file in `inc/` with a single responsibility and add its basename to the bootstrap list. To add global CSS, place a focused file in `assets/css/` and explicitly add it to the ordered list in `inc/assets.php`. To add JavaScript, use a guarded IIFE, tolerate absent DOM, initialize after DOM readiness, and enqueue it only where needed.

## Accessibility rules

- Preserve visible `:focus-visible` styles and meaningful landmark labels.
- Use links for navigation and buttons for actions; never make a `div` interactive.
- Supply meaningful image alternatives or an empty alt for decorative imagery.
- Do not make information or actions hover-only.
- Keep mobile targets at least 44 CSS pixels in the future final UI.
- Respect `prefers-reduced-motion` for every animation.
- Future disclosure controls must synchronize `aria-expanded` and their controlled region.
- Future sliders must provide keyboard controls, names, status, and pause behavior.

## Pre-release checklist

Test activation with Hello Elementor, Elementor absent, Elementor active, and Elementor Pro absent. Check unassigned menus, missing featured images, JavaScript disabled, debug mode, translations, both text directions, responsive layouts, templates, escaping, console errors, PHP logs, and asset waterfalls. Runtime claims require an actual WordPress test environment.
