# Astra Portfolio source audit

This is a source-only audit of the read-only plugin directory
`C:\Users\pouya\Desktop\astra-portfolio`. It does not describe the contents of
the live WordPress database.

## 1. Plugin identity

- Plugin name: `WP Portfolio`
- Plugin slug/directory and text domain: `astra-portfolio`
- Version: `1.11.8`
- Bootstrap: `astra-portfolio.php`
- Main class: global singleton `Astra_Portfolio`
- Relevant constants: `ASTRA_PORTFOLIO_VER`, `ASTRA_PORTFOLIO_FILE`,
  `ASTRA_PORTFOLIO_BASE`, `ASTRA_PORTFOLIO_DIR`, `ASTRA_PORTFOLIO_URI`
- Source: `astra-portfolio.php:3-22` and
  `classes/class-astra-portfolio.php:16-111`

There is no PHP namespace. The bootstrap loads the main singleton, which in turn
loads the post type, REST, shortcode, template, admin, import, and compatibility
classes.

## 2. Portfolio post type

Registration: `Astra_Portfolio_Admin::register_post_and_taxonomies()` in
`classes/class-astra-portfolio-admin.php:513-558`.

| Property | Source-derived value |
| --- | --- |
| Post type | `astra-portfolio` |
| Public | `true` |
| Publicly queryable | `true` |
| Has archive | `true` |
| Hierarchical | `false` |
| Query var | `true` |
| REST | `true`; base `astra-portfolio`; controller `WP_REST_Posts_Controller` |
| Supports | `title`, `editor`, `thumbnail` |
| Rewrite | Default plugin setting: `astra-portfolio`. A saved non-empty `rewrite` setting replaces `rewrite.slug` through `Astra_Portfolio_Admin::change_portfolio_url_slug()` (`classes/class-astra-portfolio-helper.php:54-81`; `classes/class-astra-portfolio-admin.php:919-942`). The live saved value remains runtime-verification work. |

The complete arguments can be changed by `astra_portfolio_post_type_args` before
registration.

## 3. Taxonomies

All three taxonomies are attached to `astra-portfolio` in
`Astra_Portfolio_Admin::register_post_and_taxonomies()`.

| Taxonomy | Purpose/labels | Hierarchy | Visibility and REST | Rewrite behavior | Source |
| --- | --- | --- | --- | --- | --- |
| `astra-portfolio-categories` | Categories | Hierarchical | Admin UI/column, query var, export, and REST are explicitly enabled. `public` is not explicitly declared. | Default setting `astra-portfolio-categories`; a saved non-empty `rewrite-categories` value replaces `rewrite.slug`. | `classes/class-astra-portfolio-helper.php:54-81`; `classes/class-astra-portfolio-admin.php:578-606`, `891-896` |
| `astra-portfolio-other-categories` | Other Categories; imported Starter Template data uses it for page builder classification | Hierarchical | Admin UI/column, query var, export, and REST are explicitly enabled. `public` is not explicitly declared. | Default setting `astra-portfolio-other-categories`; a saved non-empty `rewrite-other-categories` value replaces `rewrite.slug`. | `classes/class-astra-portfolio-helper.php:54-81`; `classes/class-astra-portfolio-admin.php:614-639`, `898-903`; import mapping at `classes/class-astra-portfolio-page.php:207-240` |
| `astra-portfolio-tags` | Tags | Non-hierarchical/tag-like | Admin UI/column, query var, export, and REST are explicitly enabled. `public` is not explicitly declared. | Default setting `astra-portfolio-tags`; a saved non-empty `rewrite-tags` value replaces `rewrite.slug`. | `classes/class-astra-portfolio-helper.php:54-81`; `classes/class-astra-portfolio-admin.php:647-672`, `884-889` |

The category registration arguments are filterable through
`astra_portfolio_categories_args`. Equivalent dedicated argument filters are not
present for the other two registrations. All three can still be affected by the
WordPress `register_taxonomy_args` filter used by the plugin's rewrite-setting
handler.

## 4. Portfolio types

The source of truth is the scalar post meta key `astra-portfolio-type`.
`Astra_Portfolio_Page::get_portfolio_types()` declares the exact values in
`classes/class-astra-portfolio-page.php:948-973`. Missing type meta is rendered as
`iframe` by `Astra_Portfolio_Rest_API::get_portfolio_type()`.

| Label | Stored value | Rendering and click behavior |
| --- | --- | --- |
| Website | `iframe` | Uses `astra-site-url`. If `astra-site-open-in-new-tab` is `1`, the card is a link with `target="_blank"`; otherwise JS opens a WordPress Thickbox iframe preview. It does not use the post single permalink. |
| Image | `image` | Opens Magnific Popup as an image using `astra-lightbox-image-id`, falling back in the card template to the cover URL from `astra-portfolio-image-id`. |
| Video | `video` | Opens Magnific Popup in iframe mode using `astra-portfolio-video-url`. |
| Single Page | `page` | Uses the REST response's WordPress `link` (the post permalink). `astra-site-open-portfolio-in` selects `new-tab`, `same-tab`, or Thickbox `iframe`. |

Rendering source: `includes/tmpl-portfolio-list.php:47-146` and
`assets/js/shortcode.js:314-408`. There is no plugin-defined Case Study or
Gallery type. `astra_portfolio_add_new_types` can add third-party type definitions,
so any runtime-added values require site verification.

## 5. Meaningful portfolio meta keys

### `astra-portfolio-type`

- Purpose: exact portfolio type discriminator.
- Stored format: scalar string (`iframe`, `image`, `video`, or `page`).
- Read source: admin UI, REST field callback, exclusion logic, and item behavior;
  notably `classes/class-astra-portfolio-rest-api.php:280-289`.
- Write source: add-new `meta_input`, imports, and meta-box save in
  `classes/class-astra-portfolio-page.php:918-932`, `218-226`, and
  `classes/class-astra-portfolio-admin.php:501-503`.
- Notes: missing values fall back to `iframe` in REST output.

### `astra-site-url`

- Purpose: external URL for Website items.
- Stored format: scalar URL string.
- Read source: REST field registration and Website card template at
  `classes/class-astra-portfolio-rest-api.php:107-114` and
  `includes/tmpl-portfolio-list.php:121-144`.
- Write source: meta box and Starter Template import at
  `classes/class-astra-portfolio-admin.php:481-483` and
  `classes/class-astra-portfolio-page.php:222-226`.
- Notes: source does not validate a specific provider or domain.

### `astra-portfolio-video-url`

- Purpose: Video item source URL.
- Stored format: scalar URL-like string entered in a text input.
- Read source: REST callback and Video template at
  `classes/class-astra-portfolio-rest-api.php:301-304` and
  `includes/tmpl-portfolio-list.php:81-98`.
- Write source: `classes/class-astra-portfolio-admin.php:385-396`, `477-479`.
- Notes: rendered by the bundled lightbox's iframe module.

### `astra-portfolio-image-id`

- Purpose: listing cover/thumbnail attachment.
- Stored format: WordPress attachment ID.
- Read source: REST URL and image metadata callbacks at
  `classes/class-astra-portfolio-rest-api.php:199-233`.
- Write source: media meta box, Starter Template import, and legacy migration at
  `classes/class-astra-portfolio-admin.php:335-344`, `473-475`;
  `classes/class-astra-portfolio-page.php:242-258`; and
  `classes/class-astra-portfolio-update.php:123-143`.
- Notes: imports also call `set_post_thumbnail()`, and the migration copied the
  WordPress `_thumbnail_id` into this key.

### `astra-lightbox-image-id`

- Purpose: full image opened for an Image item.
- Stored format: WordPress attachment ID.
- Read source: REST URL callback at
  `classes/class-astra-portfolio-rest-api.php:245-254`.
- Write source: Image meta box and save at
  `classes/class-astra-portfolio-admin.php:370-383`, `469-471`.
- Notes: this is distinct from the listing cover.

### `astra-site-open-in-new-tab`

- Purpose: Website click target/preview choice.
- Stored format: integer-like scalar `1` or `0`.
- Read source: REST field and item template at
  `classes/class-astra-portfolio-rest-api.php:89-96` and
  `includes/tmpl-portfolio-list.php:13-17`, `121-144`.
- Write source: `classes/class-astra-portfolio-admin.php:354-359`, `489-493`.
- Notes: `1` produces an external `_blank` link; `0` uses the iframe preview.

### `astra-site-open-portfolio-in`

- Purpose: Single Page click behavior.
- Stored format: scalar key: `new-tab`, `same-tab`, or `iframe`.
- Read source: REST field and item template at
  `classes/class-astra-portfolio-rest-api.php:98-105` and
  `includes/tmpl-portfolio-list.php:19-21`, `49-80`.
- Write source: `classes/class-astra-portfolio-admin.php:398-419`, `495-499`.
- Notes: save fallback is `new-tab`.

### `astra-site-call-to-action`

- Purpose: HTML-capable call-to-action shown on the Thickbox preview bar.
- Stored format: scalar HTML sanitized with `wp_kses_post()`.
- Read source: REST field and preview JS at
  `classes/class-astra-portfolio-rest-api.php:80-87` and
  `assets/js/shortcode.js:142-145`.
- Write source: `classes/class-astra-portfolio-admin.php:360-366`, `411-417`,
  `485-487`.
- Notes: used by Website and Single Page iframe previews, not as the portfolio
  description.

### `astra-remote-post-id`

- Purpose: identity of an imported Starter Template item, used for deduplication
  and update/exclusion bookkeeping.
- Stored format: scalar remote site/post ID.
- Read/write source: `classes/class-astra-portfolio-page.php:160-175`, `217-226`
  and `classes/class-astra-portfolio-update.php:190-231`.
- Notes: integration/import bookkeeping, not display content.

### `astra-blog-id`

- Purpose: legacy/multisite blog ID used only to construct an admin tools URL in
  the portfolio meta box.
- Stored format: source does not prove the writer or exact format.
- Read source: `classes/class-astra-portfolio-admin.php:297-323`.
- Write source: `UNVERIFIED_FROM_SOURCE`.
- Notes: not used by frontend listing output.

### `astra-site-widgets-data`

- Purpose: source does not establish frontend use; name suggests imported widget
  data.
- Stored format: registered as a single string exposed in REST.
- Read source: no read usage found in the audited plugin.
- Write source: no write usage found in the audited plugin.
- Notes: registration only at `classes/class-astra-portfolio-admin.php:560-571`;
  actual population is `UNVERIFIED_FROM_SOURCE`.

WordPress-native fields provide the title (`post_title`), description/content
(`post_content`, because `editor` is supported), and Featured Image
(`_thumbnail_id`). No dedicated subtitle, description, client, date/year, gallery,
or featured-state portfolio meta key was found. The strings
`astra-portfolio-image-url` and `astra-lightbox-image-url` are transient hidden
form fields derived from attachment IDs and are not saved as post meta by this
source.

## 6. Cover / thumbnail model

- The runtime listing cover is the attachment ID in
  `astra-portfolio-image-id`, resolved to a full URL and attachment alt/title by
  the REST field callbacks.
- The post type also supports WordPress Featured Image. Imported website
  screenshots are downloaded, attached with `set_post_thumbnail()`, and copied to
  `astra-portfolio-image-id`. The `1.0.1` migration likewise copied existing
  `_thumbnail_id` values to the custom key.
- REST rendering does **not** fall back from an empty
  `astra-portfolio-image-id` to `_thumbnail_id`; it returns no cover URL.
- Website, Image, Video, and Single Page items all use the same cover key in the
  listing. Image items separately use `astra-lightbox-image-id` for the opened
  full image.
- Imported Website covers can originate as remote `featured-image-url` values,
  but they are downloaded into the local media library before assignment. No
  on-demand remote screenshot service appears in frontend rendering.

Source: `classes/class-astra-portfolio-rest-api.php:199-233`,
`classes/class-astra-portfolio-page.php:217-258`, and
`classes/class-astra-portfolio-update.php:117-146`.

## 7. Gallery model

No per-portfolio gallery type, gallery meta key, ordered attachment array, or
mixed image/video gallery model was found. Although `gallery.enabled` is set in
`assets/js/shortcode.js:316-325`, Magnific Popup is initialized separately on
each `.site-single.image`, and each stock card renders only one image anchor.
That option therefore does not establish a stored multi-image portfolio gallery.
Per-item ordering, first-image semantics, and mixed gallery support are
`UNVERIFIED_FROM_SOURCE`/not implemented by this plugin model.

## 8. Video model

- Meta key: `astra-portfolio-video-url`.
- Expected value: a scalar URL entered in a plain text field; no oEmbed object or
  embed HTML is stored by this source.
- Render path: REST field `portfolio-video-url` -> card anchor -> Magnific Popup
  `type: 'iframe'` (`includes/tmpl-portfolio-list.php:81-98` and
  `assets/js/shortcode.js:377-408`).
- Provider support in the bundled iframe module explicitly recognizes
  `youtube.com` and `vimeo.com` URLs and rewrites them to autoplay embed URLs
  (`assets/vendor/js/magnific-popup.js:1585-1603`). Other direct URLs are passed
  through as the iframe source (`assets/vendor/js/magnific-popup.js:1627-1648`).
- No YouTube short-URL-specific code, WordPress oEmbed call, self-hosted
  `<video>` rendering, upload attachment ID, or stored iframe/embed HTML was found.
- Thumbnail behavior: the shared `astra-portfolio-image-id` cover is shown; the
  plugin does not derive a remote video thumbnail.

## 9. Website portfolio model

Website items use type `iframe` and external URL meta `astra-site-url`. Imported
Starter Template Website items may receive a locally downloaded screenshot in
both `_thumbnail_id` and `astra-portfolio-image-id`.

Click behavior is controlled by `astra-site-open-in-new-tab`: `1` creates an
external `_blank` link; otherwise the item is a non-link span handled by JS and
WordPress Thickbox to show the URL in an iframe. The optional
`astra-site-call-to-action` is inserted into the preview bar. The listing also
tracks `?portfolio=<post-slug>` with History API while a preview is open.

Website items are intentionally excluded from normal frontend WordPress queries
and their admin “View” action is removed by default. Their WordPress single
permalink is not the normal click target.

Source: `includes/tmpl-portfolio-list.php:121-145`,
`assets/js/shortcode.js:47-112`, and
`classes/class-astra-portfolio-admin.php:64-88`, `144-158`, `833-870`.

## 10. Single-page portfolio model

Type `page` is the real WordPress single experience:

- It uses the CPT permalink returned as REST field `link`.
- `astra-site-open-portfolio-in` chooses new tab, same tab, or iframe preview.
- It is the only built-in type not placed in the plugin's frontend query exclusion
  list and the only type that keeps the admin “View” row action.
- The post type supports `editor`. On the edit screen, the plugin removes editor
  support for non-`page` types, leaving it for Single Page items
  (`classes/class-astra-portfolio-admin.php:919-940`).
- The plugin ships no `single-astra-portfolio.php` and installs no
  `single_template`/`template_include` override. Single Page rendering therefore
  reaches the active theme's normal WordPress template hierarchy.
- No dedicated Elementor API integration is present. Whether Elementor is
  enabled for this CPT and whether existing posts contain Elementor data require
  runtime/database verification.

Other built-in types intentionally avoid public single navigation through the
global exclusion filter and card click behavior.

## 11. Template architecture

`Astra_Portfolio_Templates` loads templates from the following order:

1. Active/child theme: `astra-portfolio/<template-name>`
2. Active/child theme root: `<template-name>`
3. Plugin: `includes/<template-name>`

This is confirmed in
`classes/class-astra-portfolio-templates.php:77-80`, `130-191`. The exact
child-theme override directory is `astra-portfolio/`.

Relevant plugin templates:

- Listing shell, filters/search containers, load-more/spinner/empty states:
  `includes/shortcode.php`
- Card/item renderer: `includes/tmpl-portfolio-list.php`
- Category filter renderer: `includes/tmpl-portfolio-filters.php`
- Preview responsive controls: `includes/tmpl-responsive-view.php`
- No-results partial: `includes/tmpl-no-items-found.php`

There is no numbered-pagination template, standalone quick-view template, archive
template, or single portfolio template. Thickbox and Magnific Popup generate the
preview/modal UI.

Template integration filters/actions include `astra_portfolio_get_template`,
`astra_portfolio_locate_template`, `astra_portfolio_get_template_part`,
`astra_portfolio_before_template_part`, and
`astra_portfolio_after_template_part`.

## 12. Shortcodes / blocks / widgets

Frontend entry points are two aliases handled by
`Astra_Portfolio_Shortcode::page_templates()`:

- `[wp_portfolio]`
- `[astra-portfolio]`

Source: `classes/class-astra-portfolio-shortcode.php:45-50`, `155-253`.

Relevant attributes are:

- `categories`, `other-categories`, `tags`
- `columns`, `per-page`
- `show-search`, `show-categories`, `show-other-categories`
- `show-portfolio-on` (`click` produces Load More; otherwise scroll behavior)
- `category-show-all`, `other-category-show-all`
- `show-quick-view`, `quick-view-text`
- `grid-style`, `page-builder`

There are no shortcode attributes for sort/order, a portfolio-type filter, or
numbered pagination in `get_attributes()`
(`classes/class-astra-portfolio-shortcode.php:127-146`). The source contains no
registered Gutenberg block, WordPress widget, or Elementor widget.

## 13. Search / filter / load behavior

The initial shell is server-rendered by the shortcode; portfolio data and taxonomy
terms are fetched client-side from the public WordPress REST API at
`/wp-json/wp/v2/` (filterable by `astra_portfolio_api_site_uri`). This is not an
admin-AJAX listing flow.

| Behavior | Mechanism |
| --- | --- |
| Search | Debounced frontend input adds core REST `search`; server query/results are returned by `WP_REST_Posts_Controller`. |
| Categories | Frontend requests the taxonomy REST collection, then adds `astra-portfolio-categories=<term IDs>` to the post REST request. |
| Other Categories/page builder | Same pattern using `astra-portfolio-other-categories`. |
| Tags | Shortcode data adds `astra-portfolio-tags=<term IDs>` to the post REST request; no tag filter UI is generated by this source. |
| Sorting | No plugin listing sort control or REST `order`/`orderby` parameter construction found. |
| Pagination | REST `page` and `per_page`; no numbered UI. |
| Load More | When `show-portfolio-on` is `click`, a button increments `page` and appends the next REST response. |
| Infinite scroll | Otherwise a document scroll handler increments `page` near the grid end and appends the next REST response. |

`assets/js/astra-portfolio-api.js:11-66` wraps each REST response for internal JS
events as `args`, `items`, `items_count` (from `X-WP-Total`), and `next_page`.
Public read requests use the core REST endpoint and no plugin nonce. The AJAX
actions found elsewhere in the plugin are administrative import/settings jobs,
not frontend portfolio listing endpoints.

Source: `assets/js/shortcode.js:647-768`, `1027-1128` and
`assets/js/astra-portfolio-api.js:11-66`.

## 14. Elementor integration

- No Elementor widget, controls, dependency check, registration hook, Dynamic
  Tag, Theme Builder hook, or Elementor Pro requirement was found.
- The `page` type retains normal post editor support; whether Elementor editing is
  enabled for `astra-portfolio` is not established by this plugin source.
- Card links include `data-elementor-open-lightbox="no"`, which prevents
  Elementor's lightbox from competing with the plugin lightbox
  (`includes/tmpl-portfolio-list.php:61`, `84`, `105`, `125`).
- `elementor` appears as a built-in page-builder classification option for
  Starter Template imports (`classes/class-astra-portfolio-page.php:1454-1472`),
  not as a widget integration.
- Elementor Pro is not referenced by the audited source.

## 15. Comments support

No. The post type supports only `title`, `editor`, and `thumbnail`; `comments` is
not registered (`classes/class-astra-portfolio-admin.php:537-555`). New items are
also explicitly created with `comment_status => 'closed'`
(`classes/class-astra-portfolio-page.php:921-932`). Comments therefore appear
intentionally disabled. Enabling them later is technically possible through the
filterable post-type arguments and per-post comment status, but this audit does
not enable or test that path.

## 16. Likes / favorites / views

No reusable like/favorite system found in audited plugin source. No portfolio
view-counter meta, table, REST field, or API was found either. Preview-related
uses of the word “view” are UI preview/responsive-view controls, not engagement
counters.

## 17. Public hooks and filters useful to Fahar

### `astra_portfolio_post_type_args`

- Purpose: filters CPT registration arguments.
- Source: `classes/class-astra-portfolio-admin.php:537-556`.
- Potential Fahar use: defensive post-type capability/support integration without
  replacing registration.

### `astra_portfolio_categories_args`

- Purpose: filters primary category taxonomy registration arguments.
- Source: `classes/class-astra-portfolio-admin.php:592-604`.
- Potential Fahar use: adjust/query category exposure if a verified need arises.

### `astra_portfolio_exclude_portfolio_items`

- Purpose: controls exclusion of non-`page` types from normal WordPress queries,
  post navigation, and admin View actions.
- Source: `classes/class-astra-portfolio-admin.php:83`, `152-157`; compatibility
  classes use the same filter.
- Potential Fahar use: the future adapter must account for this global behavior
  when querying all four types.

### `astra_portfolio_api_site_uri`

- Purpose: filters the REST base used by the stock shortcode frontend.
- Source: `classes/class-astra-portfolio-shortcode.php:339-350`.
- Potential Fahar use: understand or diagnose installations with a custom REST
  base; not needed for direct WordPress queries.

### `astra_portfolio_shortcode_localize_vars`

- Purpose: filters localized stock-shortcode JS configuration.
- Source: `classes/class-astra-portfolio-shortcode.php:202-218`.
- Potential Fahar use: interoperability if the stock shortcode remains on legacy
  pages.

### `astra_portfolio_shortcode_top` / `astra_portfolio_shortcode_bottom`

- Purpose: output hooks around the stock listing shell.
- Source: `includes/shortcode.php:9`, `55`.
- Potential Fahar use: limited augmentation of legacy shortcode pages without
  editing plugin templates.

### `astra_portfolio_locate_template` / `astra_portfolio_get_template`

- Purpose: filter resolved plugin template paths.
- Source: `classes/class-astra-portfolio-templates.php:130-191`.
- Potential Fahar use: controlled legacy-template interoperability. Native
  child-theme overrides under `astra-portfolio/` are preferable when using the
  stock renderer.

### `astra_portfolio_filters_all_text`

- Purpose: filters the stock filter UI's “All” label.
- Source: `includes/tmpl-portfolio-filters.php:9`.
- Potential Fahar use: Persian localization of legacy shortcode output.

### `astra_portfolio_add_new_types` / `astra_portfolio_default_portfolio_type`

- Purpose: extend the type choices and select a valid default.
- Source: `classes/class-astra-portfolio-page.php:948-973`, `1412-1425`.
- Potential Fahar use: detect that runtime type values may be extended; the Fahar
  adapter should not silently assume the four built-ins are exhaustive on the
  live site.

## 18. Frontend asset dependencies

Assets are registered on `wp_enqueue_scripts`, then the listing assets are
enqueued only when either shortcode renders
(`classes/class-astra-portfolio-shortcode.php:45-118`, `195-221`).

| Handle/package | Role |
| --- | --- |
| `astra-portfolio-shortcode` | Main frontend behavior; depends on the items below |
| `astra-portfolio-api` | jQuery wrapper for WordPress REST requests |
| `astra-portfolio-shortcode` / `astra-portfolio-grid` styles | Listing and grid presentation |
| `jquery`, `wp-util` | DOM/AJAX and Underscore-style `wp.template()` rendering |
| `imagesloaded` | Detect cover image completion |
| `jquery-masonry` | WordPress-bundled Masonry; dependency added only when plugin setting `enable-masonry` is true |
| `astra-portfolio-lazyload` | Bundled jQuery Lazy plugin |
| `astra-portfolio-lightbox` | Bundled Magnific Popup JS/CSS for Image and Video types |
| `astra-portfolio-history` | Bundled History API helper for `?portfolio=` preview state |
| WordPress Thickbox via `add_thickbox()` | Website and optional Single Page iframe previews |

Registration occurs on all frontend requests, but listing scripts/styles and
Thickbox are enqueued/added by shortcode execution. No optimization or dequeueing
was performed.

## Source-derived integration contract

```text
Plugin: WP Portfolio 1.11.8 (slug/text domain: astra-portfolio)
Portfolio post type: astra-portfolio
Primary taxonomy: astra-portfolio-categories
Tag taxonomy: astra-portfolio-tags
Portfolio type source: post meta astra-portfolio-type; REST field portfolio-type; missing REST value falls back to iframe
Website type value: iframe
Image type value: image
Video type value: video
Single-page type value: page
Cover source: attachment ID in astra-portfolio-image-id (WP Featured Image is also supported/synchronized by import and migration, but is not a runtime REST fallback)
Gallery source: UNVERIFIED_FROM_SOURCE (no per-item gallery model found)
Video source: scalar URL in astra-portfolio-video-url, rendered by Magnific Popup iframe
External URL source: scalar URL in astra-site-url
Description source: WordPress post_content for page items; no dedicated description meta found
Single permalink support: yes, for page items via the WordPress REST link; other built-in types intentionally use modal/external targets and are excluded from normal frontend queries by default
Comments post-type support: no
Elementor integration: no dedicated widget/API integration; page keeps editor support; card links disable Elementor lightbox; live CPT enablement/content is unverified
Template override support: yes; child/active theme directory astra-portfolio/ (theme-root fallback also supported)
Current listing entry point: [wp_portfolio] and [astra-portfolio], both handled by Astra_Portfolio_Shortcode::page_templates()
Search/filter mechanism: frontend jQuery builds public core WordPress REST requests using search and taxonomy term-ID parameters
Load-more mechanism: core REST page/per_page requests appended by JS; click button or infinite-scroll mode
```

## What still requires real site/database verification

- Which built-in or filter-added `astra-portfolio-type` values actually exist.
- Which documented meta keys are populated, empty, or stored in legacy formats on
  existing items.
- Item counts, publish statuses, and actual use of each taxonomy and term.
- The effective post type and taxonomy rewrite slugs from saved plugin settings.
- The configured shortcode(s), attributes, and pages currently used for listings.
- Actual Featured Image/custom cover coverage and missing-image frequency.
- Whether any Image items lack `astra-lightbox-image-id` and rely on their cover.
- Actual video URL providers/formats and whether every URL embeds successfully.
- Actual Website URLs, iframe restrictions, and new-tab/preview settings.
- Whether Elementor is enabled for `astra-portfolio` and which Single Page items
  contain Elementor data.
- Existing `post_content`, comment statuses/comments, and single-permalink usage.
- Whether third-party code changes plugin contracts through the documented
  filters, template overrides, or REST-base filter.
