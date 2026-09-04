# Ozeki Markdown Documents

Ozeki Markdown Documents is a WordPress plugin for managing Markdown as an
independent, reusable document asset.

The Markdown source remains the canonical content. HTML is generated output,
and Gutenberg is an optional placement layer rather than the document data
model.

## Planned first release

- Dedicated Markdown Document content type.
- UTF-8 `.md` import and export.
- Markdown source revisions.
- CommonMark and GitHub-Flavored Markdown rendering.
- Safe server-side HTML rendering and cache invalidation.
- Tables and fenced code blocks.
- Public document permalinks.
- Shortcode-based embedding by document ID.

Gutenberg source editing, Mermaid, AWS publishing, and Git synchronization are
outside the first release.

## Requirements

- WordPress 6.4 or later.
- PHP 8.1 or later.

## Status

The project is in the specification and initial architecture phase.

See [the specification](docs/specification.md) and
[architecture decisions](docs/decisions/0001-document-source-and-integration.md).

## License

GPL-2.0-or-later.
