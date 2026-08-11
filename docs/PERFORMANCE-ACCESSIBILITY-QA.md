# Performance & Accessibility QA

## Areas reviewed

- Conditional asset loading, script delivery, and duplicate enqueue risk
- Explore Masonry observers, image loading, and Back-to-Explore restoration
- Single Portfolio images, embeds, native video, slider, and description enhancement
- Landmarks, headings, forms, cards, comments, navigation, focus, and touch targets
- Reduced motion, no-JavaScript behavior, RTL logical properties, mobile clearance, and Elementor regressions

## Fixes made

- Restored a keyboard-visible skip link and aligned both Fahar-owned main landmarks with its `#content` target.
- Removed the unused global `app.js` request; feature scripts remain conditionally loaded and deferred.
- Stopped forcing every card image to lazy-load so WordPress can apply its native loading and fetch-priority heuristics while retaining responsive image dimensions and sources.
- Exposed slider position updates as a polite status so assistive technology can understand the current item.

## Verified behavior

- Explore and Single Portfolio CSS/JS remain limited to their respective views; shared card CSS loads only on those views.
- Masonry batches layout work with `requestAnimationFrame`, scopes observers to its grid/cards, and retains CSS Grid without JavaScript.
- Back restoration has no permanent scroll listener or polling and performs at most its initial restoration plus one load correction.
- Primary Single image remains eager with high fetch priority, while approved embeds remain lazy. Native video uses controls and metadata preload without autoplay.
- Media wrappers reserve responsive space for embeds, and WordPress attachment images retain intrinsic dimensions and responsive `srcset` output.
- Native controls, labels, headings, focus rings, touch-target sizing, reduced-motion rules, and RTL logical properties remain intact.
- Search/filter GET forms, cards, media scrolling, full description, Back link, comments, and navigation retain no-JavaScript behavior.

## Runtime tests & automated tools

- Static source review and targeted searches were performed.
- JavaScript syntax was checked with Node.js.
- Contrast ratios for core text, muted text, Desert Gold, and accent foreground combinations were calculated from theme tokens.
- PHP lint, PHPCS, browser testing, Lighthouse, axe, and runtime console/log inspection were unavailable.

## Known limitations

- No runnable WordPress/browser environment was available, so responsive rendering, keyboard traversal, screen-reader announcements, runtime errors, CLS, and LCP were not measured interactively.

## Deferred

Task 26 — Mobile Bottom Navigation Glass Island Polish remains a separate visual-polish task and was not implemented here.
