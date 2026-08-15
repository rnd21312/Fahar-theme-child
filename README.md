# Fahar Theme Child

Fahar Theme Child is the presentation foundation for the Fahar portfolio experience. It remains a child of [Hello Elementor](https://wordpress.org/themes/hello-elementor/) and supports existing WordPress and Elementor-authored content while the product is developed incrementally.

## Requirements and installation

- WordPress 5.9 or newer
- PHP 7.4 or newer
- Hello Elementor parent theme installed
- Elementor is optional for theme activation; Elementor Pro is not required

Install the directory in `wp-content/themes/fahar-theme-child`, install Hello Elementor, then activate **Fahar Theme Child** in Appearance → Themes. Do not rename the parent theme directory from `hello-elementor`.

## Current status

Phase 0 (repository bootstrap) is complete. The repository contains architecture and presentation scaffolding only: no production portfolio query, migration, masonry, filters, likes, AJAX, sliders, or custom Elementor widgets are implemented.

## Architecture

`functions.php` defines stable constants and loads focused modules from `inc/`. CSS is directly editable, ordered, and token-driven. JavaScript is dependency-free and conditionally loaded for future portfolio views. Template files are opt-in scaffolds and do not override an unknown portfolio post type.

The child theme owns presentation. A future `fahar-elementor-core` plugin will own reusable Elementor widgets, controls, dynamic tags, endpoints, and other functionality that should survive a theme switch. See [Architecture](docs/ARCHITECTURE.md) and [Elementor integration](docs/ELEMENTOR-INTEGRATION.md).

## Directory overview

- `inc/` — setup, assets, compatibility, Elementor, portfolio, navigation, and performance boundaries
- `assets/css/` — tokens and layered global/conditional styles
- `assets/js/` — guarded vanilla JavaScript entry points
- `template-parts/` — defensive semantic presentation fragments
- `templates/` — manually selectable Explore and Single scaffolds
- `docs/` — architecture, integration, development, design, and roadmap decisions
- `languages/` — future translation catalogs

## Development

There is intentionally no Node or Composer build pipeline. Edit PHP, CSS, and JavaScript directly. Follow WordPress escaping and internationalization conventions, use the `fahar_theme_` PHP prefix, and keep direction-aware layout expressed with CSS logical properties. Run the checks in [Development](docs/DEVELOPMENT.md) before opening a pull request.

## Build an installable theme

Create the versioned WordPress theme ZIP with the dependency-free PowerShell builder:

```powershell
.\tools\build-theme.ps1
```

The archive is written to `build\fahar-theme-child-{VERSION}.zip`, using the `Version:` header in `style.css`. If local execution policy blocks scripts, use:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\build-theme.ps1
```

PowerShell 7 users may alternatively run `pwsh -NoProfile -File .\tools\build-theme.ps1`.

The original upstream `readme.txt` is retained for provenance; this file is the active project documentation.
