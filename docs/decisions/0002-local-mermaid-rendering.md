# ADR 0002: Local Mermaid rendering

- Status: Accepted
- Date: 2026-09-04

## Context

Markdown documents need diagrams-as-code in both the editing preview and the
published document. Loading executable JavaScript from a CDN would create an
unnecessary external dependency and would conflict with WordPress.org plugin
guidance.

## Decision

1. Mermaid fences remain unchanged in the canonical Markdown.
2. The server renderer emits an ordinary code element with the
   language-mermaid class.
3. Mermaid 11.17.2 is pinned and bundled locally under assets/vendor.
4. package.json, package-lock.json, build tooling, the upstream license, and
   source links remain available for review and reproduction.
5. Browser rendering uses securityLevel strict, startOnLoad false, HTML labels
   disabled, and explicit text and edge limits.
6. Mermaid JavaScript assets load only when rendered document HTML contains a
   Mermaid fence. The dedicated editor loads them because a fence may be typed
   before the document is saved. The small, class-scoped document stylesheet
   is enqueued before the page head is printed.
7. A rendering failure leaves the original fenced code visible.
8. Generated SVG is disposable output and is never written into the Markdown
   source or WordPress database.

## Consequences

- Documents stay portable to other Markdown systems.
- Editing preview and published rendering use the same Mermaid version.
- Sites do not contact a third-party CDN to render diagrams.
- Diagram pages download a large browser dependency; ordinary pages do not.
- Mermaid dependency updates require npm audit, browser regression tests, and
  a rebuilt local asset.
