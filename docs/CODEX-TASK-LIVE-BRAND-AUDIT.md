# Codex Task — Fahar Live Brand Audit

## Goal
Visually audit live `https://fahar.ir/` and convert only verified brand/UI observations into the Fahar design-system source of truth.

This is an audit/design-system task, **not** an Explore or Single redesign.

## Mandatory skills
Before making UI/UX decisions:

1. Load and apply **UI UX Pro Max**.
2. Load and apply the repo-local **`fahar-ui-system`** skill from `.agents/skills/fahar-ui-system/`.
3. Read `.agents/skills/fahar-ui-system/references/brand-source.md`.

If either required skill is unavailable, stop and report it.

## Live-source requirement
Use a browser-capable environment to directly inspect `https://fahar.ir/`.

If the live site cannot be visually loaded:
- do not infer visual values from search results, cached text, memory, or third-party screenshots;
- do not change tokens/components;
- report the blocker and stop.

## Capture scope
Inspect at minimum:

- homepage desktop around 1440px
- homepage mobile around 390px
- one service/content-heavy page
- one portfolio/project-oriented page if available
- desktop and mobile navigation
- primary/secondary actions
- input/form treatment if present
- cards/media treatment if present

Capture screenshots for evidence and inspect computed styles where possible.

## Extract only verified facts
Document:

- background/surface hierarchy
- text colors
- brand accent and interaction states
- border treatment
- Persian font family/fallback
- body typography
- heading scale/weight
- shared control heights
- button/input/card radii
- page gutter and section spacing rhythm
- header dimensions/density
- shadows/elevation
- motion character
- mobile composition differences
- reusable button/control/card/navigation patterns

Do not estimate exact values when they can be measured.

## Files allowed in this task
Primary:

- `.agents/skills/fahar-ui-system/references/brand-source.md`
- `assets/css/tokens.css`
- `.agents/skills/fahar-ui-system/references/components.md`
- `.agents/skills/fahar-ui-system/references/patterns.md`
- `assets/css/components.css` only if a verified global primitive contract needs correction

Do **not** redesign:

- Explore
- Portfolio Card
- Single Portfolio
- Mobile Bottom Nav

in this task.

## Decision rule
For each observed difference:

1. Is it directly verified on `fahar.ir`?
2. Is it a real brand/design-system property rather than page-specific decoration?
3. Should it be represented as a semantic token, component contract, or composition pattern?
4. Would changing the global value improve brand consistency without forcing marketing-specific geometry onto app UI?

Only then modify the system.

## Validation
For changed CSS:

```bash
python tools/check-ui-system.py <changed-css-files>
```

Also run:

```bash
git diff --check
git diff
```

Review that no unrelated screen styling changed.

## Deliverable
Keep the final report short:

### Verified brand findings
Only observations actually seen/measured from the live site.

### Design-system changes
Tokens/contracts changed and why.

### Files changed
Exact list.

### Validation
Checks actually run.

### Next
Recommend exactly one next visual task, normally Mobile Bottom Navigation polish or Explore full-width app-shell depending on the audit findings.

Do not commit or push unless explicitly requested.
