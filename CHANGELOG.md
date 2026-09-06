# Changelog

All notable changes to Fahar Theme Child are documented here.

## 1.2.2 - 2026-09-06

- Added real sort options to the Explore page (Latest, Oldest, A–Z by title), preserved across filters, search, and infinite-scroll pagination. "Popular"/"Trending" sorting is intentionally not included yet: no view/like data exists in the theme, and per docs/LIKES-ARCHITECTURE.md that data is owned by a not-yet-built companion plugin, not the theme.

## 1.2.1 - 2026-09-06

- Added hover motion to portfolio cards (lift, image zoom, elevated shadow, gold title accent) using existing Fahar tokens, with a `prefers-reduced-motion` fallback.

## 1.0.0 - 2026-08-08

- Renamed and normalized the Hello Elementor child-theme metadata for Fahar.
- Added a modular, guarded PHP bootstrap and integration boundaries.
- Added RTL-first design tokens, frontend CSS layers, and vanilla JavaScript scaffolds.
- Added accessible portfolio, navigation, header, and footer template boundaries.
- Added conservative `theme.json` editor defaults and project documentation.
