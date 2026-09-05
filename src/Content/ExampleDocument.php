<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Content;

final class ExampleDocument
{
    public const MARKDOWN = 'markdown';
    public const MERMAID = 'mermaid';
    public const MATH = 'math';

    /**
     * @return array{title: string, filename: string, source: string}
     */
    public static function guide(string $guide): array
    {
        if ($guide === self::MERMAID) {
            return [
                'title' => __('Mermaid Diagram Examples', 'ozeki-markdown-documents'),
                'filename' => 'ozeki-mermaid-examples.md',
                'source' => self::mermaidSource(),
            ];
        }

        if ($guide === self::MATH) {
            return [
                'title' => __('AsciiMath and LaTeX Examples', 'ozeki-markdown-documents'),
                'filename' => 'ozeki-math-examples.md',
                'source' => self::mathSource(),
            ];
        }

        return [
            'title' => __('Interactive Markdown Example', 'ozeki-markdown-documents'),
            'filename' => 'ozeki-markdown-guide.md',
            'source' => self::source(),
        ];
    }

    public static function source(): string
    {
        return <<<'MARKDOWN'
# A Well-Structured Markdown Example

This document introduces Markdown in a disciplined order, from basic text to diagrams and formulas. Change the Markdown on the left and observe the preview on the right.

## 1. Paragraphs and Emphasis

Insert one blank line to start a new paragraph.

Use **bold**, *italic*, ~~strikethrough~~, and `inline code` where they improve clarity.

## 2. Headings

Headings begin with `#`. Use `#` for the document title, `##` for sections, and `###` for subsections.

### A Subsection Example

Use heading levels in order so that long documents remain easy to navigate.

## 3. Lists and Procedures

- First bullet point
- Second bullet point
  - Indent by two spaces for a nested item

1. Complete the first step
2. Continue with the next step
3. Confirm the result

## 4. Quotations and Links

> A quotation begins with `>`.
>
> Several quoted lines can form one block.

Write a link such as the [WordPress website](https://wordpress.org/).

Images use `![Alternative text](https://example.com/image.png)`. In a real document, use the URL of an image from your media library.

## 5. Tables

| Item | Markdown | Purpose |
| --- | --- | --- |
| Heading | `# Heading` | Document structure |
| Emphasis | `**Important**` | Highlight a term |
| Code | `` `code` `` | Commands and identifiers |

## 6. Task Lists

- [x] Write the Markdown
- [x] Check the preview
- [ ] Review before publishing

## 7. Code Blocks

Add a language name to identify the kind of code.

```php
<?php

echo 'Hello, Markdown!';
```

## 8. Mermaid Diagrams

Write the diagram inside a `mermaid` fenced code block.

```mermaid
flowchart LR
    Write[Write Markdown] --> Preview[Check the preview]
    Preview --> Publish[Publish or reuse]
```

## 9. AsciiMath Formulas

Beginner-friendly AsciiMath uses short, readable notation. Write an inline formula as `asciimath:a/b`.

```asciimath
sum_(i=1)^n i^2 = (n(n+1)(2n+1))/6
```

## 10. LaTeX Formulas

LaTeX is the standard interchange format for formulas. Write an inline formula as `math:\frac{a}{b}`.

```math
\int_{0}^{1} x^2\,dx = \frac{1}{3}
```

Hover over a formula or focus it with the keyboard to reveal the Copy LaTeX button.

## 11. Reusing a Document

A published Markdown document has its own URL. Reference the same document from a post or page with this shortcode:

```text
[ozeki_markdown_document id="123"]
```

Replace `123` with the ID of the published Markdown document. You can export the canonical source as a `.md` file at any time.
MARKDOWN;
    }

    private static function mermaidSource(): string
    {
        return <<<'MARKDOWN'
# Practical Mermaid Diagram Examples

Mermaid generates diagrams from readable text. Change any example and observe the result in the preview.

## 1. Flowchart

Show processes, decisions, and loops. `TD` runs from top to bottom; `LR` runs from left to right.

```mermaid
flowchart TD
    Start([Start]) --> Input[Enter data]
    Input --> Check{Is it correct?}
    Check -->|Yes| Save[(Save)]
    Check -->|No| Input
    Save --> End([Complete])
```

## 2. Architecture with Subgraphs

Group related components to clarify a system architecture.

```mermaid
flowchart LR
    subgraph Browser
        Editor[Edit Markdown]
        Preview[Preview]
    end
    subgraph WordPress[WordPress]
        Parser[Safe rendering]
        Store[(Store Markdown)]
    end
    Editor --> Parser --> Preview
    Editor --> Store
```

## 3. Sequence Diagram

Show interactions between a user and system in time order.

```mermaid
sequenceDiagram
    actor User
    participant WP as WordPress
    participant DB as Database
    User->>WP: Save Markdown
    WP->>DB: Store canonical source
    DB-->>WP: Saved
    WP-->>User: Display preview
```

## 4. State Diagram

Show how a document or job moves between states.

```mermaid
stateDiagram-v2
    [*] --> Draft
    Draft --> Review: Request review
    Review --> Draft: Revise
    Review --> Published: Approve
    Published --> Archived: End publication
    Archived --> [*]
```

## 5. Class Diagram

Describe object attributes, operations, and relationships.

```mermaid
classDiagram
    class Document {
        +int id
        +string title
        +render()
        +export()
    }
    class Revision {
        +string source
        +datetime createdAt
    }
    Document "1" --> "many" Revision : preserves
```

## 6. Entity Relationship Diagram

Describe database entities and their relationships.

```mermaid
erDiagram
    USER ||--o{ DOCUMENT : creates
    DOCUMENT ||--o{ REVISION : has
    USER {
        int id PK
        string name
    }
    DOCUMENT {
        int id PK
        string title
    }
    REVISION {
        int id PK
        text markdown
    }
```

## 7. Gantt Chart

Show a schedule with durations and dependencies.

```mermaid
gantt
    title Publication Plan
    dateFormat YYYY-MM-DD
    section Implementation
    Foundation       :done, build, 2026-09-01, 3d
    Guide            :active, guide, after build, 2d
    section Quality
    Automated tests  :test, after guide, 2d
    Release          :release, after test, 1d
```

## 8. Pie Chart

Summarize proportions of a whole.

```mermaid
pie showData
    title Document Work
    "Writing" : 50
    "Review" : 30
    "Revision" : 20
```

## Safe Usage

- Keep each diagram focused instead of placing too much information in one figure.
- External JavaScript and click handlers are not available.
- If the syntax is invalid, the original Mermaid code remains visible.
- Diagram source remains readable in Markdown viewers without Mermaid support.
MARKDOWN;
    }

    private static function mathSource(): string
    {
        return <<<'MARKDOWN'
# Practical AsciiMath and LaTeX Examples

AsciiMath is approachable to type, while LaTeX precisely expresses a wide range of mathematics. Hover over a formula to copy its LaTeX representation.

## 1. Arithmetic, Fractions, and Powers

AsciiMath provides short notation that resembles handwritten mathematics.

```asciimath
(a+b)/c + x^2 - y_1
```

The equivalent LaTeX is:

```math
\frac{a+b}{c} + x^2 - y_1
```

## 2. Square Roots and Parentheses

```asciimath
sqrt(x^2+y^2) = r
```

```math
\sqrt{x^2+y^2}=r
```

## 3. Sums and Products

```asciimath
sum_(i=1)^n i^2 = (n(n+1)(2n+1))/6
```

```math
\sum_{i=1}^{n} i^2 = \frac{n(n+1)(2n+1)}{6}
```

## 4. Limits

```asciimath
lim_(x->0) (sin x)/x = 1
```

```math
\lim_{x \to 0}\frac{\sin x}{x}=1
```

## 5. Derivatives and Integrals

```asciimath
d/dx x^n = n x^(n-1)
```

```math
\frac{d}{dx}x^n=nx^{n-1}
```

```asciimath
int_a^b f(x) dx
```

```math
\int_{a}^{b} f(x)\,\mathrm{d}x
```

## 6. Vectors

```asciimath
vec(v) = (v_1;v_2;v_3), norm(vec(v)) = sqrt(v_1^2+v_2^2+v_3^2)
```

```math
\vec{v}=\begin{pmatrix}v_1\\v_2\\v_3\end{pmatrix},
\qquad \lVert\vec{v}\rVert=\sqrt{v_1^2+v_2^2+v_3^2}
```

## 7. Matrices

In AsciiMath, `,` separates columns and `;` separates rows.

```asciimath
A = [a,b;c,d], det(A) = ad-bc
```

```math
A=\begin{bmatrix}
a & b \\
c & d
\end{bmatrix},
\qquad \det(A)=ad-bc
```

## 8. Sets and Logic

```asciimath
A nn B = {x | x in A ^^ x in B}, A sube B
```

```math
A \cap B=\{x \mid x\in A \wedge x\in B\},
\qquad A\subseteq B
```

## 9. Greek Letters

```asciimath
alpha, beta, gamma, Delta, theta, lambda, mu, pi, sigma, Omega
```

```math
\alpha,\ \beta,\ \gamma,\ \Delta,\ \theta,\ \lambda,\ \mu,\ \pi,\ \sigma,\ \Omega
```

## 10. Multi-line Equations

In AsciiMath, a blank line starts a new equation line and `&` aligns the equals signs.

```asciimath
(a+b)^2 &= (a+b)(a+b)

&= a^2+2ab+b^2
```

```math
\begin{aligned}
(a+b)^2
  &= (a+b)(a+b) \\
  &= a^2+2ab+b^2
\end{aligned}
```

## 11. Piecewise Definitions

```asciimath
abs(x) = {x if x >= 0; -x if x < 0:}
```

```math
|x|=\begin{cases}
x & \text{if }x\ge 0 \\
-x & \text{if }x<0
\end{cases}
```

## 12. Inline Formulas

Inside a sentence, prefix an inline code span: `asciimath:E=mc^2` or `math:\frac{-b\pm\sqrt{b^2-4ac}}{2a}`.

## Choosing a Format

- Start with AsciiMath when quick input matters.
- Use LaTeX when exchanging formulas with AI tools, papers, or other systems.
- The preview is derived output and never rewrites the stored Markdown source.
- Selecting both ordinary text and a formula keeps the browser's standard copy behavior.
MARKDOWN;
    }
}
