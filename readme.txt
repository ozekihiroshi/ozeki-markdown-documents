=== Ozeki Markdown Documents ===
Contributors: ozekihiroshi
Tags: markdown, documentation, mermaid, latex, asciimath
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage Markdown as reusable WordPress documents with live preview, import/export, Mermaid diagrams, and LaTeX or AsciiMath formulas.

== Description ==

Ozeki Markdown Documents keeps Markdown as the canonical document source.
Rendered HTML is generated output that can be safely rebuilt at any time.

Each Markdown document is an independent WordPress object with its own title,
status, author, revisions, permalink, and stable numeric ID. A published
document can be displayed directly or reused from another post or page with a
shortcode.

Features include:

* A dedicated Markdown document editor.
* Side-by-side editing and safe live preview.
* Exact UTF-8 `.md` and `.markdown` import and export.
* WordPress revisions for the canonical Markdown source.
* CommonMark and GitHub-Flavored Markdown rendering.
* Tables, task lists, fenced code blocks, and strikethrough.
* Locally bundled Mermaid diagram rendering.
* Locally bundled KaTeX rendering for LaTeX and beginner-friendly AsciiMath.
* An explicit Copy LaTeX control without changing mixed-selection copying.
* Interactive Markdown, Mermaid, and mathematics example libraries.
* Public document permalinks and shortcode reuse.

The rendering pipeline escapes raw HTML, rejects unsafe links, applies the
WordPress HTML allow-list, and keeps invalid diagram or formula source visible
instead of executing it. Browser libraries are bundled locally; the plugin
does not require a CDN or send document content to a third-party service.

This plugin does not replace the Gutenberg editor. It provides a separate,
portable Markdown document model that can be referenced where needed.

Source code and reproducible build instructions are maintained at:

https://github.com/ozekihiroshi/ozeki-markdown-documents

== Installation ==

1. Upload the plugin ZIP from Plugins > Add New > Upload Plugin, or install it from the WordPress Plugin Directory.
2. Activate Ozeki Markdown Documents.
3. Open Markdown Documents > Markdown Guide.
4. Create a blank document or import a UTF-8 `.md` file.
5. Publish the document after checking the live preview.

To reuse a published document in a post or page, replace `123` with its ID:

`[ozeki_markdown_document id="123"]`

== Frequently Asked Questions ==

= Is the Markdown source preserved? =

Yes. Markdown remains the canonical source. Rendered HTML, diagrams, and
formula output are derived and can be regenerated.

= Does uninstall delete my documents? =

No. Authored Markdown documents are intentionally preserved on uninstall.

= Does the plugin load diagram or math libraries from a CDN? =

No. Pinned Mermaid, KaTeX, and AsciiMath assets are bundled locally. Pages
without diagrams or formulas do not load the corresponding browser assets.

= Can I import and export normal Markdown files? =

Yes. Imports accept UTF-8 `.md` and `.markdown` files up to 5 MiB and create a
new draft. Export returns only the canonical Markdown source.

= How do I write formulas? =

Use an inline code span such as `asciimath:a/b` or
`math:\frac{a}{b}`. For displayed formulas, use an `asciimath`, `math`, or
`latex` fenced code block. The Math Examples page provides editable examples.

= How do I write Mermaid diagrams? =

Use a fenced code block whose language is `mermaid`. The Mermaid Examples page
contains flowchart, sequence, state, class, ER, Gantt, and pie examples.

== Changelog ==

= 0.1.0 =

* First public release.
* Added canonical Markdown documents, revisions, import/export, permalinks, and shortcode reuse.
* Added safe side-by-side live preview with CommonMark and GitHub-Flavored Markdown.
* Added locally bundled Mermaid, LaTeX, and AsciiMath rendering.
* Added interactive Markdown, Mermaid, and mathematics guides.

== Upgrade Notice ==

= 0.1.0 =

First public release.
