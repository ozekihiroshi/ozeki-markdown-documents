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
- Tables and fenced code blocks.
- Public document permalinks.
- Shortcode-based embedding by document ID.

Gutenberg source editing, Mermaid, AWS publishing, and Git synchronization are
outside the first release.

## Requirements

- WordPress 6.4 or later.
- PHP 8.1 or later.

## Status

The first vertical slice is running in an isolated WordPress environment. It
currently includes the dedicated editor, exact UTF-8 Markdown import/export,
source revisions and restore, safe CommonMark/GFM rendering, rendered-cache
invalidation, split live preview, public permalinks, and shortcode references.

The automated manual tests cover exact .md HTTP round trips, source revision
restore, unsafe HTML and link handling, generated cache reuse, and shortcode
rendering. Release packaging and the full WordPress compatibility matrix remain
before the first public release.

See [the specification](docs/specification.md) and
[architecture decisions](docs/decisions/0001-document-source-and-integration.md).

## License

GPL-2.0-or-later.
