# ADR 0003: LaTeX and AsciiMath formulas

## Status

Accepted for implementation.

## Context

The editor should support standard LaTeX interchange and approachable formula
entry without making authors learn LaTeX first. Exact Markdown import/export,
portable fallback behavior, Moodle compatibility, and safe rendering remain
requirements.

## Decision

1. Canonical Markdown is the only source of truth. Derived LaTeX and rendered
   output are disposable.
2. LaTeX is the standard interchange and clipboard format.
3. AsciiMath is the beginner input format. No EasyMath regex language is
   introduced.
4. Inline formulas use prefixed Markdown code spans: `math:` for LaTeX and
   `asciimath:` for AsciiMath.
5. Display formulas use fenced `math`, `latex`, or `asciimath` code blocks.
6. Ordinary Markdown viewers degrade safely to readable code.
7. CommonMark renders escaped code. A fixed local browser asset converts
   AsciiMath to LaTeX and renders both formats with KaTeX.
8. Each rendered formula has an explicit Copy LaTeX button. It is revealed on
   pointer hover or keyboard focus, remains available on touch devices, and
   does not intercept mixed-selection copy behavior.
9. Rendering is local, conditional, untrusted, strictly limited, and leaves
   the original code visible after failure.
10. Shared WordPress/Moodle work should reuse syntax, conversion fixtures, and
    security rules. Platform-specific asset and filter integration remains in
    adapters to avoid Moodle MathJax double processing.

## Consequences

- Authors can paste AI-produced LaTeX directly.
- Beginners can use compact AsciiMath such as `a/b` and `sqrt(x)`.
- AsciiMath conversion version changes invalidate rendered output but never
  rewrite the Markdown source.
- Converter accuracy, license, dependency audit, and adversarial inputs are
  release gates.
