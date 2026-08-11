# Portfolio integration adapter

The Fahar adapter isolates future theme code from WP Portfolio 1.11.8 internals.
Its evidence is the source audit in [PORTFOLIO-AUDIT.md](PORTFOLIO-AUDIT.md).
It is read-only: there are no writes, migrations, remote requests, or media/UI
rendering. Portfolio listings use a scoped `WP_Query` wrapper so provider query
behavior remains isolated inside the adapter.

## Confirmed provider contract

- Post type: `astra-portfolio`
- Normalized types: `iframe` -> `website`, `image` -> `image`, `video` ->
  `video`, `page` -> `single-page`
- Meta mapping: type `astra-portfolio-type`; cover attachment
  `astra-portfolio-image-id`; Image portfolio attachment
  `astra-lightbox-image-id`; Website URL `astra-site-url`; Website new-tab
  preference `astra-site-open-in-new-tab`; Single Page open mode
  `astra-site-open-portfolio-in`; CTA `astra-site-call-to-action`; video source
  `astra-portfolio-video-url`
- Taxonomies: `astra-portfolio-categories`,
  `astra-portfolio-other-categories`, `astra-portfolio-tags`
- Description: unformatted `post_content` for `single-page` items
- Single permalink: supported only by normalized `single-page` items

Provider identifiers and meta keys are centralized and filterable in
`inc/portfolio.php`; templates should use the public helpers below.

## Public helper API

- `fahar_theme_get_portfolio_post_type(): string`
- `fahar_theme_is_portfolio_post( $post = null ): bool`
- `fahar_theme_get_portfolio_type( $post = null ): string`
- `fahar_theme_get_portfolio_types(): string[]`
- `fahar_theme_get_portfolio_type_query_args( $types ): array`
- `fahar_theme_query_portfolios( $query_args ): WP_Query`
- `fahar_theme_get_portfolio_cover_id( $post = null ): int`
- `fahar_theme_get_portfolio_cover_url( $post = null, $size = 'large' ): string`
- `fahar_theme_get_portfolio_lightbox_image_id( $post = null ): int`
- `fahar_theme_get_portfolio_lightbox_image_url( $post = null, $size = 'full' ): string`
- `fahar_theme_get_portfolio_external_url( $post = null ): string`
- `fahar_theme_get_portfolio_video_source( $post = null ): array`
- `fahar_theme_get_portfolio_destination( $post = null ): array`
- `fahar_theme_get_portfolio_description( $post = null ): string`
- `fahar_theme_get_portfolio_description_content( $post = null ): string`
- `fahar_theme_get_portfolio_taxonomies(): string[]`
- `fahar_theme_get_portfolio_terms( $post = null, $taxonomy = null ): WP_Term[]`
- `fahar_theme_get_related_portfolios( $post = null, $limit = 6 ): WP_Post[]`
- `fahar_theme_portfolio_has_single_permalink( $post = null ): bool`

Existing Explore page helpers remain available for routing/navigation
compatibility. `fahar_theme_is_portfolio_item()` remains a compatibility wrapper
around the new post helper.

When the plugin/post type is unavailable, post-backed helpers return `false`,
`0`, `''`, `[]`, or the video `none` structure. The adapter never calls plugin
classes or includes plugin files, so plugin absence cannot cause a hard failure.

## Cover and gallery rules

The cover ID prefers the verified `astra-portfolio-image-id` attachment meta,
then falls back locally to the normal WordPress featured image ID. Both values
must resolve to local attachments. The adapter performs no remote lookup and
does not mutate portfolio data.

Image portfolio media is read from `astra-lightbox-image-id`. The Single
Portfolio renderer adds that attachment after the cover and deduplicates equal
IDs. A distinct lightbox attachment renders at its full registered image size
with WordPress responsive image markup.

The plugin audit found no Astra-specific gallery meta contract. The universal
Single Portfolio collection therefore extracts Gutenberg and Classic Editor
images, galleries, supported video/embed content, and media attachments without
inventing an Astra field. Extracted media is suppressed from the rendered
description and appears only in the authoritative media slider.

## Video source contract

The video helper always returns:

```php
array(
	'type'     => 'none|url|embed|unknown',
	'provider' => 'none|youtube|vimeo|aparat|self-hosted|unknown',
	'value'    => mixed,
)
```

- Absolute HTTP(S) values become `url`. Hostname matching detects YouTube
  (`youtube.com`, `youtu.be`), Vimeo (`vimeo.com`), and Aparat (`aparat.com`).
- Direct URLs ending in a WordPress-supported video extension become provider
  `self-hosted`; other valid URL hosts remain `unknown`.
- Raw iframe/media embed HTML becomes `embed`. The original untrusted string is
  preserved without rendering or allowlisting. Provider detection uses only an
  iframe `src` when present.
- Non-empty legacy values that are neither valid URLs nor recognizable media
  embed HTML become `unknown` and are preserved.
- Empty values return `type: none`, `provider: none`, and an empty value. A
  verified video value can participate in the universal media collection even
  when another normalized portfolio type supplies the primary presentation.

Attachment video IDs are not normalized as `attachment`: the audit explicitly
did not verify attachment IDs as a storage shape for
`astra-portfolio-video-url`. Numeric legacy values remain `unknown` until Task
06B proves their meaning.

Raw embed data must never be echoed by callers. The adapter does not call oEmbed,
fetch remote metadata, load players, or produce `<iframe>`/`<video>` markup.

## Card destination contract

`fahar_theme_get_portfolio_destination()` returns `url`, `kind`,
`is_internal`, and `opens_new_tab` keys:

- Website uses the validated `astra-site-url` and honors the explicit
  `astra-site-open-in-new-tab` preference.
- Image uses the full `astra-lightbox-image-id` URL, falling back to the repaired
  cover URL when no valid lightbox attachment exists.
- Video uses the validated `astra-portfolio-video-url`. For legacy iframe data,
  only its extracted HTTP(S) `src` becomes the link; raw HTML is never output.
- Single Page uses its normal internal WordPress permalink.

When the required source is absent or invalid, the destination is the stable
`none` structure with an empty URL. The adapter never invents a permalink for
provider-excluded Image/Video items and never fabricates a media URL.

## Deferred verification and rendering

Task 06B still needs to verify actual type/meta population, legacy video shapes,
cover completeness, runtime-added types, taxonomy usage, and third-party filter
changes on the real site.

## Media rendering contract

`fahar_theme_get_portfolio_media_items()` is the authoritative Single Portfolio
collection. Its order is WordPress Featured Image, content-authored media in
authoring order, verified Astra cover/lightbox/video media, then remaining
WordPress attachments. Without a Featured Image, the best verified Astra cover
leads. Attachment IDs and canonical URLs are deduplicated.

The request-scoped content transformation parses stored `post_content` without
modifying it. It exposes the extracted collection to the slider and renders only
remaining non-media rich content through the normal WordPress content pipeline
and a safe post-HTML allowlist.

The renderer supports responsive WordPress attachment images, sanitized HTTP(S)
content images, WordPress video attachments, YouTube, Vimeo, Aparat, and direct
URLs with WordPress-recognized video extensions. Provider
iframe URLs are rebuilt from locally parsed IDs using fixed player hosts; stored
iframe HTML and its attributes are never passed through. Unknown or malformed
sources render nothing. No remote metadata, provider API, player SDK, or autoplay
is used.

## Explore filter contract

Explore filtering uses GET parameters with normalized Fahar values:

- `portfolio_category=<slug>`: one verified visitor-facing category
- `portfolio_tag=<slug>`: one tag associated with a published portfolio in the
  selected category
- `portfolio_search=<query>`: the native WordPress portfolio search value

Category and tag selection use immediate, server-rendered GET navigation. A tag
is accepted only while its category is valid, so changing or clearing a category
also clears an incoherent tag. Search is preserved across category and tag
navigation, and filter reset preserves the current search phrase.

The visitor-facing category/tag taxonomy mapping and contextual published-item
tag lookup remain inside the Fahar portfolio adapter. The provider's secondary
classification taxonomy is never exposed by Explore controls. Unknown slugs are
discarded before the query is built. `fahar_theme_query_portfolios()` scopes the
provider's normal-query exclusion override to Fahar listing queries.

## Related portfolio query contract

`fahar_theme_get_related_portfolios()` returns up to 6 published `WP_Post`
objects by default, with a hard maximum of 12. It excludes the current item and
duplicates, then fills results through configured verified taxonomies in order,
the current normalized portfolio type, and finally newest portfolio items.
Every stage requests only the remaining result count and uses deterministic
date/ID descending order. The final trusted result can be adjusted through
`fahar_theme_related_portfolios`, with current post and limit context; returned
values are revalidated and bounded afterward. Presentation is intentionally
deferred.

## Likes integration boundary

Portfolio Like functionality is owned by the future `fahar-elementor-core`
companion plugin, not this presentation adapter. The chosen logged-in-only v1
identity, storage, REST, security, fallback, and migration contracts are defined
in [LIKES-ARCHITECTURE.md](LIKES-ARCHITECTURE.md). No Like persistence or
endpoint is implemented in the Child Theme.
