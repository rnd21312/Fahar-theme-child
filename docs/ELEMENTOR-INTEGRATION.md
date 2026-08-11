# Elementor integration

The theme can activate without Elementor. `fahar_theme_is_elementor_active()` and `fahar_theme_is_elementor_pro_active()` are guarded helpers, and `fahar_theme_elementor_ready` fires only after Elementor's public initialization hook. No Elementor class is called unconditionally.

Existing Elementor pages continue through Hello Elementor's normal rendering. The compatibility CSS is intentionally narrow and does not target generated class names, disable plugin assets, monkey patch core, duplicate widgets, or assume Elementor Pro.

Future Theme Builder integration should first ask Elementor's public location APIs whether a header, footer, archive, or single location is rendered; the Fahar semantic parts then act only as fallbacks. This phase does not register or replace locations because the production setup has not been audited.

The future `fahar-elementor-core` plugin owns custom widgets, injected controls, dynamic tags, AJAX endpoints, and reusable editor functionality. It may subscribe to `fahar_theme_elementor_ready`, but must still work defensively when this theme is not active where practical. CSS tokens remain the frontend design source; Elementor globals are mapped intentionally rather than synchronized through private APIs.
