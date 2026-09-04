# Ozeki Markdown Documents Specification

## 1. Purpose

Ozeki Markdown Documents lets WordPress users import, author, publish, reuse,
and export Markdown without converting the Markdown source into Gutenberg
child blocks.

The defining model is:

```text
Markdown source = canonical document
Rendered HTML   = disposable generated output
Gutenberg       = optional reference and placement layer
```

## 2. Product distinction

The plugin is not primarily another Markdown block. A Markdown document is a
first-class WordPress object with its own ID, status, author, revisions,
permalink, source hash, import filename, and rendered cache.

One document can be displayed at its own permalink and referenced from
multiple posts or pages without copying its Markdown source into each post.

## 3. Scope of version 0.1.0

Version 0.1.0 includes:

- custom post type `ozmd_document`;
- a Markdown-specific editing screen;
- UTF-8 `.md` and `.markdown` import;
- deterministic `.md` export;
- Markdown source revisions;
- SHA-256 source hashing;
- CommonMark and GitHub-Flavored Markdown rendering;
- tables and fenced code blocks;
- split Markdown editing and server-rendered live preview;
- Mermaid fenced diagrams in preview and published documents;
- raw HTML disabled by default;
- final WordPress HTML allow-list filtering;
- generated HTML caching;
- public document permalinks;
- shortcode embedding by document ID;
- capability and nonce checks for all mutations;
- uninstall behavior that does not silently delete authored documents.

## 4. Excluded from version 0.1.0

- Gutenberg-based Markdown source editing;
- Gutenberg reference block;
- executable diagram callbacks, links, or arbitrary JavaScript;
- syntax-highlighting JavaScript bundles;
- Git synchronization;
- S3 or CloudFront publishing;
- automatic AWS credential handling;
- LMS, quiz, membership, payment, or AI features.

## 5. Data model

### 5.1 Document object

WordPress custom post type:

```text
ozmd_document
```

Native post fields provide:

- document ID;
- title;
- slug and permalink;
- author;
- draft, private, and published status;
- created and modified timestamps.

The post type supports revisions. It is single-site first and exportable.

### 5.2 Metadata

All keys use the project-specific `ozmd` prefix.

```text
_ozmd_source
    Canonical Markdown source.

_ozmd_source_sha256
    SHA-256 of the canonical source bytes.

_ozmd_original_filename
    Sanitized original import filename, without a path.

_ozmd_rendered_html
    Disposable sanitized HTML cache.

_ozmd_render_hash
    Hash covering source, rendering settings, parser version, and renderer
    schema version.

_ozmd_toc
    Reserved for a later structured table-of-contents cache.

_ozmd_render_settings
    Reserved for validated per-document rendering settings.
```

`_ozmd_source` is registered as post metadata with revision support. It is not
made public through the generic REST post-meta response in version 0.1.0.

### 5.3 Source format

- The accepted source is valid UTF-8 text.
- NUL bytes are rejected.
- Import accepts `.md` and `.markdown` files.
- YAML front matter is preserved as source text but is not interpreted in
  version 0.1.0.
- Rendering never overwrites the Markdown source.
- The canonical source SHA-256 is recalculated after every accepted change.

Import followed immediately by export must preserve the accepted canonical
source. Editing may normalize browser line endings; this behavior must be
documented and covered by tests before release.

## 6. Import and export

### 6.1 Import

Import must:

- require an authenticated user who can create documents;
- verify a nonce;
- enforce a 5 MiB size limit;
- validate the extension and UTF-8 source;
- reject NUL bytes and malformed uploads;
- sanitize the filename independently from the Markdown body;
- create a draft by default;
- avoid persisting an additional copy in the public uploads directory.

### 6.2 Export

Export must:

- require permission to read non-public documents;
- use `text/markdown; charset=UTF-8`;
- use a safe `Content-Disposition` filename;
- stream only the Markdown source;
- never append rendered HTML or Gutenberg serialization.

## 7. Rendering and security

The rendering pipeline is:

```text
canonical Markdown
  -> configured CommonMark/GFM parser
  -> raw HTML disabled or escaped
  -> unsafe URL rejection
  -> WordPress-specific allowed HTML filtering
  -> sanitized rendered cache
  -> escaped wrapper attributes at output
```

The implementation must not treat a Markdown parser as an HTML sanitizer.
Scripts, event-handler attributes, unsafe URL schemes, iframes, and arbitrary
SVG are not allowed by default.

Mermaid source remains a fenced code block in the canonical Markdown and in
the server-rendered HTML. A locally bundled, fixed Mermaid version converts
that code to SVG in the browser only when a document contains a Mermaid fence.
It uses strict security, disables HTML labels and click behavior, limits text
and edge counts, and preserves the original code block if rendering fails.
No executable code is loaded from a CDN.

Rendering failures must leave the Markdown source intact and must not publish
partially generated HTML.

## 8. Cache policy

Rendered HTML is reused only when `_ozmd_render_hash` matches a hash of:

- Markdown source;
- normalized rendering settings;
- parser package version;
- renderer schema version;
- plugin rendering compatibility version.

A missing or stale cache is regenerated from the Markdown source. Cache data
is never accepted as the source of truth.

## 9. Frontend behavior

Published documents have their own permalink and render inside a scoped
document wrapper compatible with classic and block themes.

Version 0.1.0 provides a shortcode that stores only a document reference:

```text
[ozeki_markdown_document id="123"]
```

Public rendering of a reference is allowed only when the referenced document
is published. Authorized preview behavior is handled separately.

## 10. Gutenberg policy

Gutenberg is not the source editor in version 0.1.0.

A later dynamic block may store only:

```json
{"documentId": 123}
```

It must not duplicate the Markdown source or generated HTML in block
attributes. Removing or rearranging a reference block must not modify the
document.

## 11. Backup and portability

Because canonical source, metadata, and revisions are stored in WordPress,
normal database backup protects the documents.

Direct S3 export of individual `.md` objects may be added later as an optional
integration. The core plugin must not depend on the existing S3 backup plugin
or on AWS credentials.

## 12. Compatibility targets

- WordPress 6.4 or later;
- PHP 8.1 or later;
- single-site WordPress in version 0.1.0;
- classic and block themes;
- no required frontend JavaScript for ordinary Markdown rendering.

## 13. Release gates

Before a public release:

- Plugin Check passes against the generated release ZIP;
- PHP and WordPress compatibility checks pass;
- import/export round-trip tests pass;
- revision restore tests pass;
- XSS and unsafe-link fixtures pass;
- valid Mermaid SVG, invalid-diagram fallback, and conditional asset-loading
  tests pass;
- cache invalidation tests pass;
- fresh ZIP install, activation, deactivation, reactivation, and uninstall are
  verified in the isolated ZIP-test environment;
- authored Markdown documents survive deactivation and plugin update;
- uninstall behavior is explicit and documented.

## 14. Planned later phases

### Version 0.2.x

- heading anchors and table of contents;
- admin preview;
- Gutenberg dynamic reference block;
- richer `.md` import/export workflow.

### Version 0.3.x and later

- syntax highlighting;
- callouts;
- Mermaid with strict asset and security boundaries;
- Markdown bundles containing referenced local assets;
- optional Git synchronization;
- optional S3 Markdown export integration.
