# Fahar Live Brand Source

This file records **verified** visual facts from the live `https://fahar.ir/` site for Codex UI work.

Do not add a value here from memory, search snippets, third-party screenshots, or assumptions. A value is verified only when the live site (or a user-supplied screenshot/export from it) is directly inspected.

Until a field is verified, the implemented values in `assets/css/tokens.css` remain the safe baseline. Unverified does not mean wrong; it means "do not claim this was observed from the live site."

## Audit protocol

For a live audit, capture at minimum:

- homepage at desktop (~1440px wide)
- homepage at mobile (~390px wide)
- one service/content-heavy page
- one portfolio/project-oriented page if available
- header/navigation states
- at least one primary and secondary action
- form/input treatment if present
- card/media treatment if present

Record the URL and viewport for every observation.

Use browser/devtools/computed styles where possible; do not estimate a hex value or font from eyesight when it can be measured.

## Evidence table

Fill only verified rows.

Evidence reviewed on 2026-08-11:

- `screencapture-fahar-ir-2026-08-11-14_43_29.png`: `https://fahar.ir/` homepage, desktop full-page capture, 1704 px exported raster width; browser viewport and device-pixel ratio were not supplied.
- `screencapture-fahar-ir-2026-08-11-14_44_29.png`: `https://fahar.ir/` homepage, mobile full-page capture, 505 px exported raster width; browser viewport and device-pixel ratio were not supplied.
- The captures verify appearance and responsive composition only. They do not verify computed CSS values, font files, interaction states, or motion.

| Area | Verified live value/pattern | Evidence (URL + viewport/state) | Theme mapping |
|---|---|---|---|
| Page background | Near-black background is continuous across the page; some sections use a subtly different dark band for separation. Exact color is unverified. | Both homepage captures, default state | `--fahar-color-bg` remains the implemented baseline |
| Primary surface | Cards and controls use dark surfaces that remain visibly distinct from the page background. Exact color is unverified. | Both homepage captures, service cards, filters, article cards, and controls | `--fahar-color-surface` remains the implemented baseline |
| Elevated surface | Contained panels are separated mainly by surface contrast and outline, not a bright elevation effect. Exact color is unverified. | Both homepage captures, cards and desktop video filter panel | `--fahar-color-surface-elevated` remains the implemented baseline |
| Primary text | Main copy and titles use a high-contrast near-white treatment. Exact color is unverified. | Both homepage captures | `--fahar-color-text` remains the implemented baseline |
| Secondary text | Supporting copy and metadata use a visibly dimmer neutral treatment. Exact color is unverified. | Both homepage captures, card copy, captions, footer | `--fahar-color-text-secondary` remains the implemented baseline |
| Brand accent | Warm metallic gold is used selectively for the logo, hero heading, selected/prominent action, section accents, and decorative brand marks. Exact color is unverified. | Both homepage captures, default state | `--fahar-color-accent` remains the implemented baseline |
| Accent hover/active | unverified in static captures | — | semantic interaction tokens unchanged |
| Border treatment | Cards, fields, and neutral controls use thin, low-contrast light-on-dark outlines. | Both homepage captures, default state | existing border token roles are consistent; values unchanged |
| Primary Persian font | Persian RTL typography is visible; the font family/fallback cannot be identified reliably from the captures. | Both homepage captures | font tokens unchanged |
| Base body size/leading | Compact body copy with visibly open line spacing; exact size and leading are unverified. | Both homepage captures | body typography tokens unchanged |
| Heading scale/weight | Section headings are clearly stronger/larger than body copy; the hero heading is gold and prominent without oversized display typography. Exact values are unverified. | Both homepage captures | heading typography tokens unchanged |
| Control height | Controls are compact and consistent in apparent height within each group; exact height is unverified. | Both homepage captures, header actions and chips | shared control sizing unchanged |
| Button radius | Actions use rounded rectangles; compact filters/tags use pill geometry. Exact radii are unverified. | Both homepage captures, default state | existing medium and pill radius roles are consistent; values unchanged |
| Field radius | Search/input-like controls use rounded dark containers with subtle outlines. Exact radius is unverified. | Desktop header search and video filter panel; mobile compact field/control row | existing field radius contract is consistent; values unchanged |
| Card/media radius | Cards and contained media use restrained rounded corners rather than sharp or highly rounded geometry. Exact radius is unverified. | Both homepage captures, service, portfolio, video, and article cards | existing card/surface radius roles are consistent; values unchanged |
| Header height/density | Desktop uses a compact floating header split into logo/navigation and a separate action/search group; mobile reduces this to sparse icon/logo controls. Exact dimensions are unverified. | Desktop and mobile homepage captures, top of page | header dimension tokens unchanged |
| Page horizontal gutter | Desktop content sits in a centered bounded column with large outer margins; mobile uses narrow, consistent side gutters. Exact values are unverified. | Both homepage captures | page padding tokens unchanged |
| Section spacing rhythm | Major homepage sections are separated by generous vertical whitespace; spacing within card/control groups is compact. Exact values are unverified. | Both homepage captures | spacing scale unchanged |
| Shadow/elevation | No shadow value can be isolated reliably from the near-black background; outlines and surface contrast are visibly the primary separators. | Both homepage captures | shadow tokens unchanged |
| Motion/transition character | unverified in static captures | — | motion tokens unchanged |

## Pattern observations

Record behavior separately from raw token values.

### Header / navigation

- Desktop navigation is low-chrome and floating: a compact rounded brand/menu group is visually separate from a compact action/search group.
- The mobile header removes the full text menu from view and retains sparse icon/logo controls.
- Only default states are visible; sticky behavior, menus, focus, hover, pressed, and selected states are unverified.

### Buttons / controls

- A gold-filled compact action establishes the strongest action emphasis in the desktop header.
- Neutral actions use dark rounded surfaces with subtle outlines.
- Filters and taxonomy controls are compact pills that wrap into rows when needed.
- Exact dimensions and all interaction states are unverified.

### Typography hierarchy

- The page is Persian and RTL throughout the captured content.
- Gold is reserved for selected high-level headings/brand accents; primary content headings remain near-white.
- Body copy and metadata step down through neutral, lower-contrast tones.
- Font family, exact sizes, weights, and line-height values are unverified.

### Cards / media

- Service cards use an asymmetric desktop composition with media-led dark panels; mobile recomposes them into a single vertical sequence.
- Portfolio thumbnails use a regular multi-column desktop row and a compact two-column mobile grid.
- Video/project media is grouped as a larger desktop mosaic with a separate filter panel; mobile stacks the media and moves compact controls below it.
- Article cards use image-first dark panels with outlined boundaries and compact gold metadata/actions.

### Forms

- Search/input-like elements are dark, rounded, compact, and paired with an icon.
- The captures do not expose labels, validation, disabled/error states, focus treatment, or exact field values.

### Mobile behavior

- Desktop side-by-side and asymmetric sections recompose into stacked mobile content rather than scaling down unchanged.
- The mobile service area is single-column; portfolio work remains a compact two-column grid.
- Desktop-only text navigation and the video-side filter panel are not preserved in their desktop form on mobile.
- The captures do not verify breakpoint values, off-canvas behavior, or mobile interaction states.

## Applying audit results

After evidence is captured:

1. Compare verified live values with existing semantic tokens.
2. Change a global token only when the live Fahar identity clearly calls for it.
3. Prefer semantic token changes over local selector patches when the value is truly global.
4. Do not force marketing-page geometry onto portfolio-app components when the role differs; preserve the same visual language through shared color, type, radius, spacing, and interaction rules.
5. Update `components.md` or `patterns.md` when the audit reveals a reusable component/pattern contract.
6. Run `python tools/check-ui-system.py` on every changed CSS file.
7. Keep the audit/token task separate from Explore/Single visual polish so regressions are reviewable.
