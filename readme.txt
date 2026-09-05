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

== Quick Start ==

1. Open Markdown Documents > Add New Markdown Document.
2. Enter a title and write Markdown in the left pane.
3. Check the safe rendered output in the right pane.
4. Save a draft or publish the document.
5. Use its permalink directly, or embed a published document in another post
   or page with `[ozeki_markdown_document id="123"]`.

The Markdown Guide, Mermaid Examples, and Math Examples pages provide editable
practice areas. Changes made in a guide are not saved. The explicit create
button creates a new draft and never overwrites an existing document.

== Supported Markdown and Extensions ==

The server-side renderer supports CommonMark and GitHub-Flavored Markdown,
including headings, lists, links, tables, task lists, fenced code blocks, and
strikethrough.

Use a fenced `mermaid` block for diagrams:

`~~~mermaid`
`flowchart LR`
`    Source[Markdown] --> Preview[Preview]`
`~~~`

Use a prefixed inline code span or fenced block for formulas:

* Inline AsciiMath: `` `asciimath:a/b` ``
* Inline LaTeX: `` `math:\frac{a}{b}` ``
* Display AsciiMath: an `asciimath` fenced block
* Display LaTeX: a `math` or `latex` fenced block

AsciiMath is converted only for derived display output. The stored Markdown is
not rewritten. Hover over or focus a rendered formula to reveal Copy LaTeX.
Copying a mixed selection of ordinary text and formulas retains normal browser
copy behavior.

== Security and Privacy ==

Raw HTML is escaped. Unsafe link protocols are rejected, and generated HTML is
filtered through the WordPress HTML allow-list. Mermaid runs with strict
security settings and HTML labels disabled. KaTeX runs with trust disabled,
strict error handling, and size and expansion limits. Invalid diagrams and
formulas remain readable as source instead of being executed.

The plugin does not collect telemetry, track users, contact an analytics
service, or send document content to a third party. Mermaid, KaTeX, AsciiMath,
JavaScript, CSS, and fonts are bundled locally; no CDN is required. Ordinary
WordPress administrators remain responsible for deciding who may edit or view
their site content.

== Stored Data and Uninstall ==

Documents are stored as the dedicated public `ozmd_document` custom post type. The
canonical Markdown source and generated render cache are stored as post meta.
WordPress revisions preserve the Markdown source. Imported original filenames
are retained only to suggest an export filename.

Deactivation and uninstall intentionally leave documents, revisions, and
associated post metadata unchanged. This prevents an accidental plugin removal
from destroying authored content. Delete documents through WordPress when
permanent removal is desired.

== Third-party Libraries ==

The release includes League CommonMark under BSD-3-Clause and Mermaid, KaTeX,
and asciimath-parser under MIT-compatible licenses. License files and
`third-party-notices.txt` are included in the plugin. Human-readable integration
source, lock files, and reproducible build tools are maintained in the public
GitHub repository linked above.

== Current Scope ==

This release uses a dedicated Markdown document editor; Gutenberg is available
for placing shortcodes but is not the canonical Markdown editor. The plugin
does not provide cloud synchronization, collaborative editing, Git publishing,
or remote AI processing. Imported files must be valid UTF-8 `.md` or
`.markdown` files no larger than 5 MiB.

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

No. Authored Markdown documents, revisions, and associated post metadata are
intentionally preserved. Delete documents through WordPress when permanent
removal is desired.

= Does the plugin load diagram or math libraries from a CDN? =

No. Pinned Mermaid, KaTeX, and AsciiMath assets are bundled locally. Pages
without diagrams or formulas do not load the corresponding browser assets.

= Does the plugin send Markdown or usage data to another service? =

No. It contains no telemetry or analytics and does not send document content
to an external service. Browser rendering dependencies are served locally.

= Is raw HTML supported? =

Raw HTML is displayed as escaped text. This is intentional: Markdown remains
portable while the rendered result follows a restrictive safety boundary.

= Who can create and import Markdown documents? =

Users need the standard WordPress capability to edit posts. Export and editing
actions also check access to the specific document and use WordPress nonces for
state-changing requests.

= Is the interface translated? =

Yes. All interface strings are translation-ready, and a Japanese translation
is included. Markdown, Mermaid, and formula source in the editable example
library remains portable source code.

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
