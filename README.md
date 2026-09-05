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
- Interactive Markdown, Mermaid, and math guide pages with ordered examples.
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

The built-in guides present editable, non-persistent practice areas beside
the safe live preview. The Markdown sample proceeds from paragraphs and
headings through publishing and reuse. Dedicated libraries cover common
Mermaid diagram types and AsciiMath/LaTeX notation from fractions through
calculus and matrices. An explicit action can create a new draft from any
sample; the guides never overwrite or delete an existing document.

- WordPress 6.4 or later.
- PHP 8.1 or later.

## Status

Version 0.1.0 is prepared as the first public release. Automated tests cover
exact `.md` HTTP round trips, source revision restore, unsafe HTML and URL
handling, invalid Mermaid and formula fallback, generated cache reuse,
shortcode rendering, locally served browser dependencies, and the bundled
Japanese interface translation.

GitHub Actions runs PHP lint and Composer audit on PHP 8.1 through 8.4, audits
and reproducibly rebuilds browser assets with Node.js 24, builds the scoped
release ZIP, runs Plugin Check against the extracted ZIP only, and tests the
ZIP on the supported WordPress/PHP compatibility matrix.

Browser assets require Node.js 20 or later to rebuild:

    npm ci
    npm run build:assets

Build the distributable ZIP with PHP 8.2 or later:

    ./build-release.sh

See [the specification](docs/specification.md) and
[architecture decisions](docs/decisions/0001-document-source-and-integration.md).

## Security and privacy

Raw HTML is escaped, unsafe link protocols are rejected, and rendered output
passes through the WordPress HTML allow-list. Mermaid uses strict security with
HTML labels disabled. KaTeX runs with trust disabled, strict error handling,
and bounded expansion and output size. Invalid diagrams and formulas remain
visible as source.

The plugin does not collect telemetry, contact analytics services, or transmit
document content. Mermaid, KaTeX, AsciiMath, CSS, JavaScript, and fonts are
served from the plugin; no browser CDN is used.

## Stored data and uninstall

The dedicated public `ozmd_document` post type stores authored documents. Canonical
Markdown, render-cache data, and imported filename metadata are stored as post
meta, and WordPress revisions preserve source history. Deactivation and
uninstall intentionally preserve documents, revisions, and associated post
metadata so an accidental plugin removal does not destroy authored content.

## Internationalization

Interface strings use the `ozeki-markdown-documents` text domain. The release
contains a POT template and a Japanese PO/MO translation. Editable example
documents remain English portable source so Markdown, Mermaid, AsciiMath, and
LaTeX code is not changed by interface translation.

## Third-party software

League CommonMark is distributed under BSD-3-Clause. Mermaid, KaTeX, and
asciimath-parser are distributed under MIT-compatible licenses. Their license
files and `third-party-notices.txt` are included in the release ZIP. Lock files,
human-readable integration source, and asset build tools are retained in this
repository.

## License

Ozeki Markdown Documents is licensed under GPL-2.0-or-later. See `LICENSE`.
