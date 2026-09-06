# Fahar Theme Child

Fahar Theme Child owns the Portfolio Explore discovery experience. It remains a child of [Hello Elementor](https://wordpress.org/themes/hello-elementor/); normal WordPress and Elementor template resolution owns Portfolio Single presentation.

## Requirements and installation

- WordPress 5.9 or newer
- PHP 7.4 or newer
- Hello Elementor parent theme installed
- Elementor is optional for theme activation; Elementor Pro is not required

Install the directory in `wp-content/themes/fahar-theme-child`, install Hello Elementor, then activate **Fahar Theme Child** in Appearance → Themes. Do not rename the parent theme directory from `hello-elementor`.

## Current status

Explore provides the portfolio query, search, filtering, cards, masonry, and progressive loading. Portfolio permalinks are not intercepted by the child theme.

## Architecture

`functions.php` defines stable constants and loads focused modules from `inc/`. CSS is directly editable, ordered, and token-driven. Explore JavaScript is dependency-free and conditionally loaded. No child-theme Single Portfolio routing or renderer is registered.

The child theme owns presentation. A future `fahar-elementor-core` plugin will own reusable Elementor widgets, controls, dynamic tags, endpoints, and other functionality that should survive a theme switch. See [Architecture](docs/ARCHITECTURE.md) and [Elementor integration](docs/ELEMENTOR-INTEGRATION.md).

## Directory overview

- `inc/` — setup, assets, compatibility, Elementor, portfolio, navigation, and performance boundaries
- `assets/css/` — tokens and layered global/conditional styles
- `assets/js/` — guarded vanilla JavaScript entry points
- `template-parts/` — defensive semantic presentation fragments
- `templates/` — manually selectable Explore scaffold
- `docs/` — architecture, integration, development, design, and roadmap decisions
- `languages/` — future translation catalogs

## Development

There is intentionally no Node or Composer build pipeline. Edit PHP, CSS, and JavaScript directly. Follow WordPress escaping and internationalization conventions, use the `fahar_theme_` PHP prefix, and keep direction-aware layout expressed with CSS logical properties. Run the checks in [Development](docs/DEVELOPMENT.md) before opening a pull request.

## Build an installable theme

Create the versioned WordPress theme ZIP with the dependency-free PowerShell builder:

```powershell
.\tools\build-theme.ps1
```

The archive is written to `build\fahar-theme-child-{VERSION}.zip`, using the `Version:` header in `style.css`. Its default package root is `fahar-theme-child/`.

The package root is the WordPress theme directory identity; it is separate from the theme name, text domain, and versioned ZIP filename. To replace an existing manually installed theme, this value must exactly match its current directory under `wp-content/themes`. If production uses a different directory, build with:

Production theme directory must be confirmed before claiming that an uploaded ZIP will replace the installed theme.

```powershell
.\tools\build-theme.ps1 -ThemeDirectoryName "EXISTING-DIRECTORY"
```

If local execution policy blocks scripts, use:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\build-theme.ps1
```

PowerShell 7 users may alternatively run `pwsh -NoProfile -File .\tools\build-theme.ps1`.

The original upstream `readme.txt` is retained for provenance; this file is the active project documentation.

## GitHub releases and automatic updates

The active theme checks the latest stable release from `rnd21312/Fahar-theme-child` through the GitHub Releases API and supplies a validated `fahartheme.zip` asset to WordPress's native theme updater. Release data is cached in the `fahar_theme_github_release` network transient for 12 hours; failures are cached for one hour. Developers can clear it with `wp transient delete --network fahar_theme_github_release` or `do_action( 'fahar_theme_clear_updater_cache' );`.

Keep the `Version:` header in `style.css` synchronized with the release tag, then publish a release by pushing the tag:

```bash
git tag v1.3.0
git push origin v1.3.0
```

The release workflow validates the tag/version pair, builds `fahartheme.zip` with `fahar-theme-child/` as its install-safe root, verifies the archive structure, and creates or updates the GitHub Release using `GITHUB_TOKEN`.
