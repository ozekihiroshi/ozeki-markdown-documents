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

Inline AsciiMath: `asciimath:a/b`.

Inline LaTeX: `math:\sqrt{x}`.

~~~asciimath
sum_(i=1)^n i^3
~~~

~~~math
\frac{x + 1}{y}
~~~

~~~math
\notARealCommand{
~~~

~~~math
\href{javascript:alert(1)}{unsafe}
~~~
