#!/usr/bin/env python3
"""Small Fahar UI-system guard for changed CSS files.

Usage:
    python tools/check-ui-system.py assets/css/foo.css [...]

The checker is intentionally narrow: it catches common design-system drift
without pretending to be a full CSS parser/linter.
"""

from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TOKENS = ROOT / "assets/css/tokens.css"
COMPONENTS = ROOT / "assets/css/components.css"

COLOR_RE = re.compile(r"(?<![-\w])(?:#[0-9a-fA-F]{3,8}\b|rgba?\s*\(|hsla?\s*\()")
DIRECTIONAL_RE = re.compile(r"\b(?:margin|padding|border|inset)-(?:left|right)\s*:")
CANONICAL_PRIMITIVES = (
    ".fahar-button",
    ".fahar-field",
    ".fahar-input",
    ".fahar-textarea",
    ".fahar-surface",
    ".fahar-divider",
)


def rel(path: Path) -> str:
    try:
        return str(path.resolve().relative_to(ROOT))
    except ValueError:
        return str(path)


def check(path: Path) -> list[str]:
    errors: list[str] = []
    text = path.read_text(encoding="utf-8")
    path_rel = rel(path)

    # Raw palette values belong in tokens.css. CSS keywords/currentColor/transparent
    # are intentionally outside this regex and remain valid component choices.
    if path.resolve() != TOKENS.resolve():
        for number, line in enumerate(text.splitlines(), 1):
            if COLOR_RE.search(line) and "ui-system-allow-raw-color" not in line:
                errors.append(
                    f"{path_rel}:{number}: raw color; use a semantic --fahar-* token "
                    "or document an exceptional line with ui-system-allow-raw-color"
                )

    # Canonical primitives may only be defined in components.css. Selectors in
    # comments do not count; this deliberately errs on the simple/reviewable side.
    if path.resolve() != COMPONENTS.resolve():
        for number, line in enumerate(text.splitlines(), 1):
            stripped = line.strip()
            if stripped.startswith("/*") or stripped.startswith("*"):
                continue
            for selector in CANONICAL_PRIMITIVES:
                if selector in line and "{" in line and "ui-system-allow-component-override" not in line:
                    errors.append(
                        f"{path_rel}:{number}: canonical primitive {selector} must not be redefined here"
                    )

    # Flag obvious physical inline-side properties. Width/position left/right are
    # not blanket-banned because some geometry is intentionally physical.
    for number, line in enumerate(text.splitlines(), 1):
        if DIRECTIONAL_RE.search(line) and "ui-system-allow-physical-side" not in line:
            errors.append(
                f"{path_rel}:{number}: prefer logical inline properties for RTL/LTR compatibility"
            )

    return errors


def main(argv: list[str]) -> int:
    if len(argv) < 2:
        print("Usage: python tools/check-ui-system.py <changed.css> [...]")
        return 2

    errors: list[str] = []
    for arg in argv[1:]:
        path = Path(arg)
        if not path.is_absolute():
            path = ROOT / path
        if path.suffix.lower() != ".css":
            continue
        if not path.exists():
            errors.append(f"{rel(path)}: file not found")
            continue
        errors.extend(check(path))

    if errors:
        print("Fahar UI system check failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print("Fahar UI system check passed.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
