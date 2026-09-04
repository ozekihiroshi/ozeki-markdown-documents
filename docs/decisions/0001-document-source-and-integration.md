# ADR 0001: Markdown document source and WordPress integration

- Status: Accepted
- Date: 2026-09-04

## Context

Existing WordPress Markdown plugins can retain Markdown inside Gutenberg block
attributes. This project needs a stronger document model: Markdown must be an
independent asset that can be imported, exported, revised, published at its own
URL, and reused from multiple WordPress locations.

## Decision

1. A Markdown document is represented by the `ozmd_document` custom post type.
2. The canonical Markdown source is stored in revision-enabled, plugin-prefixed
   post metadata.
3. Rendered HTML is a disposable cache and never a source of truth.
4. Version 0.1.0 uses a Markdown-specific editor and does not use Gutenberg as
   the source editor.
5. Initial embedding uses a shortcode containing only the document ID.
6. A later Gutenberg dynamic block may also contain only the document ID.
7. `.md` import and export are core features, not optional future additions.
8. Database backup is sufficient to protect canonical documents. Direct S3
   Markdown export remains an optional future integration.

## Consequences

### Positive

- Markdown is not fragmented across Gutenberg blocks.
- One document can be reused without copying its source.
- Parser and cache changes cannot destroy the source.
- Database backup includes documents and revisions.
- Gutenberg can be added without becoming the data model.

### Costs

- A dedicated editor and import/export permission model are required.
- Generic WordPress search does not automatically search protected source meta;
  search integration must be designed deliberately.
- Revision support for registered post meta establishes WordPress 6.4 as the
  minimum version.
- Exact line-ending behavior must be defined and tested across import, browser
  editing, revisions, and export.
