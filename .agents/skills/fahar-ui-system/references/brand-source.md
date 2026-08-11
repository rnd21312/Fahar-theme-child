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

| Area | Verified live value/pattern | Evidence (URL + viewport/state) | Theme mapping |
|---|---|---|---|
| Page background | pending | — | `--fahar-color-bg` |
| Primary surface | pending | — | `--fahar-color-surface` |
| Elevated surface | pending | — | `--fahar-color-surface-elevated` |
| Primary text | pending | — | `--fahar-color-text` |
| Secondary text | pending | — | `--fahar-color-text-secondary` |
| Brand accent | pending | — | `--fahar-color-accent` |
| Accent hover/active | pending | — | semantic interaction tokens |
| Border treatment | pending | — | border tokens |
| Primary Persian font | pending | — | font tokens |
| Base body size/leading | pending | — | body typography tokens |
| Heading scale/weight | pending | — | heading typography tokens |
| Control height | pending | — | shared control sizing |
| Button radius | pending | — | radius/component contract |
| Field radius | pending | — | radius/component contract |
| Card/media radius | pending | — | radius/component contract |
| Header height/density | pending | — | header dimension tokens |
| Page horizontal gutter | pending | — | page padding tokens |
| Section spacing rhythm | pending | — | spacing scale/composition |
| Shadow/elevation | pending | — | shadow tokens |
| Motion/transition character | pending | — | motion tokens |

## Pattern observations

Record behavior separately from raw token values.

### Header / navigation
Pending direct visual inspection.

### Buttons / controls
Pending direct visual inspection.

### Typography hierarchy
Pending direct visual inspection.

### Cards / media
Pending direct visual inspection.

### Forms
Pending direct visual inspection.

### Mobile behavior
Pending direct visual inspection.

## Applying audit results

After evidence is captured:

1. Compare verified live values with existing semantic tokens.
2. Change a global token only when the live Fahar identity clearly calls for it.
3. Prefer semantic token changes over local selector patches when the value is truly global.
4. Do not force marketing-page geometry onto portfolio-app components when the role differs; preserve the same visual language through shared color, type, radius, spacing, and interaction rules.
5. Update `components.md` or `patterns.md` when the audit reveals a reusable component/pattern contract.
6. Run `python tools/check-ui-system.py` on every changed CSS file.
7. Keep the audit/token task separate from Explore/Single visual polish so regressions are reviewable.
