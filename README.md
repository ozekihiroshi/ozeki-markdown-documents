# Ozeki Markdown Documents

Ozeki Markdown Documents is a WordPress plugin for managing Markdown as an
independent, reusable document asset.

The Markdown source remains the canonical content. HTML is generated output,
and Gutenberg is an optional placement layer rather than the document data
model.

## Implemented first-release foundation

- Dedicated Markdown Document content type.
- UTF-8 `.md` import and export.
- Markdown source revisions.
- CommonMark and GitHub-Flavored Markdown rendering.
- Safe server-side HTML rendering and cache invalidation.
- Split Markdown editing and server-rendered live preview.
- Mermaid diagrams in editing preview and published documents.
- LaTeX and beginner-friendly AsciiMath formulas with LaTeX copy controls.
- Tables and fenced code blocks.
- Public document permalinks.
- Shortcode-based embedding by document ID.

Gutenberg source editing, AWS publishing, and Git synchronization are
outside the first release.

## Mermaid diagrams

Use a fenced Mermaid block:

    ~~~mermaid
    flowchart LR
        Source[Markdown] --> Preview[Preview]
        Source --> Public[Published document]
    ~~~

Mermaid 11.17.2 is bundled locally. Diagram rendering uses strict security,
disables HTML labels, limits diagram text and edge counts, and leaves the
original code block visible when rendering fails. Pages without Mermaid
diagrams do not load the Mermaid browser asset.

## Mathematics

LaTeX is the standard interchange format. Use a prefixed code span for an
inline formula or a fenced math block for a displayed formula:

    Inline: `math:\frac{a}{b}`

    ~~~math
    \frac{x + 1}{y}
    ~~~

AsciiMath provides a simpler input form without introducing a separate custom
language:

    Inline: `asciimath:a/b`

    ~~~asciimath
    sum_(i=1)^n i^3
    ~~~

The Markdown source remains unchanged. AsciiMath is converted to derived
LaTeX only for preview, rendering, and the Copy LaTeX button. The button is
revealed on formula hover or keyboard focus and remains available on touch
devices. Mixed text and formula selection keeps the browser's normal copy
behavior.

KaTeX 0.18.5 and asciimath-parser 0.6.11 are bundled locally. Rendering uses
untrusted strict mode, size and expansion limits, and preserves the original
code when conversion or rendering fails.

## Requirements

- WordPress 6.4 or later.
- PHP 8.1 or later.

## Status

The first vertical slice is running in an isolated WordPress environment. It
currently includes the dedicated editor, exact UTF-8 Markdown import/export,
source revisions and restore, safe CommonMark/GFM rendering, rendered-cache
invalidation, split live preview, Mermaid diagrams, LaTeX and AsciiMath
formulas, public permalinks, and shortcode references.

The automated manual tests cover exact .md HTTP round trips, source revision
restore, unsafe HTML and link handling, generated cache reuse, and shortcode
rendering. Release packaging and the full WordPress compatibility matrix remain
before the first public release.

Browser assets require Node.js 20 or later to rebuild:

    npm ci
    npm run build:assets

See [the specification](docs/specification.md) and
[architecture decisions](docs/decisions/0001-document-source-and-integration.md).

## License

GPL-2.0-or-later.
