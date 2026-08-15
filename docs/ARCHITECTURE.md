# Architecture

## Ownership

Fahar Theme Child owns the Portfolio Explore discovery experience, the visual token layer, layout primitives, header/footer and mobile navigation presentation, RTL and accessibility presentation, conditional frontend assets, and compatibility boundaries around Hello Elementor and Elementor. Portfolio Single requests remain under normal WordPress, Hello Elementor, and Elementor template resolution; the child theme does not intercept or reproduce them.

The future `fahar-elementor-core` companion plugin will own custom widgets and controls, dynamic tags, site-specific data mapping, AJAX/REST behavior, and reusable functionality that must survive a theme switch. Keeping data and behavior out of the theme prevents content loss and avoids coupling business rules to one presentation.

## Module boundaries

- `setup.php` adds only child-theme supports that complement the parent.
- `assets.php` owns registration, deterministic ordering, and conditional loading.
- `helpers.php` owns path/version utilities.
- `compatibility.php` exposes non-destructive environment state.
- `elementor.php` provides guarded detection and a stable readiness action.
- `portfolio.php` contains the provider adapter, Explore query helpers, and card destination resolution.
- `navigation.php` provides semantic, filterable fallback items and display state.
- `performance.php` optimizes only Fahar-owned assets.

Explore templates consume these boundaries without forcing template selection for portfolio permalinks. Site-specific code can configure the documented filters without editing templates.

## Dependency and performance principles

The Hello Elementor parent remains required. Elementor and Elementor Pro remain optional at activation time. Fahar frontend code uses no jQuery, framework, CSS framework, icon font, slider, or Masonry dependency. Global CSS is small and ordered; Explore and mobile-navigation assets load only when their integration helpers identify the relevant view. Theme code does not dequeue WordPress, Hello Elementor, or Elementor assets.

Header and footer styles are opt-in through `fahar_theme_load_header_assets` and `fahar_theme_load_footer_assets`, ready for a later Theme Builder fallback decision without loading unused presentation globally.

Future portfolio grids should use responsive WordPress image markup and thumbnails for video, avoid autoplay, and add libraries only after native CSS/JavaScript has been evaluated against measured requirements.
