# Portable Markdown document

日本語を含む UTF-8 Markdown source.

| Capability | Result |
| --- | --- |
| Import | Exact source retained |
| Export | Exact source returned |

<script>This must never execute in rendered output.</script>

~~~mermaid
flowchart LR
    Source[Markdown source] --> Preview[Live preview]
    Source --> Public[Published document]
    click Source "javascript:alert('blocked')"
~~~

~~~mermaid
this is intentionally invalid Mermaid syntax
~~~
