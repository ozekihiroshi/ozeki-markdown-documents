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
# Markdownを美しく書くためのサンプル

この文書は、基本から図・数式までを順番に試せる見本です。左側のMarkdownを変更すると、右側のプレビューへ反映されます。

## 1. 段落と文字の装飾

空行を1つ入れると、新しい段落になります。

**太字**、*斜体*、~~取り消し線~~、`インラインコード`を利用できます。

## 2. 見出し

見出しは`#`から始めます。`#`が大見出し、`##`が中見出し、`###`が小見出しです。

### 小見出しの例

見出しを順番に使うと、長い文書でも構造が分かりやすくなります。

## 3. 箇条書きと手順

- 箇条書きの第1項目
- 箇条書きの第2項目
  - 2つ空白を入れると入れ子にできます

1. 最初の手順
2. 次の手順
3. 最後の確認

## 4. 引用とリンク

> 引用は`>`から始めます。
>
> 複数行の引用も1つのまとまりとして表示できます。

[WordPress公式サイト](https://wordpress.org/)のようにリンクを書けます。

画像は `![代替テキスト](https://example.com/image.png)` の形式です。実際にはメディアライブラリの画像URLを指定してください。

## 5. 表

| 項目 | Markdownでの表現 | 用途 |
| --- | --- | --- |
| 見出し | `# 見出し` | 文書の構造 |
| 強調 | `**重要**` | 重要語の明示 |
| コード | `` `code` `` | コマンドや変数名 |

## 6. タスクリスト

- [x] Markdownを書く
- [x] プレビューで確認する
- [ ] 公開前に読み直す

## 7. コードブロック

言語名を付けると、コードの種類を明示できます。

```php
<?php

echo 'Hello, Markdown!';
```

## 8. Mermaidによる図

`mermaid`のコードフェンス内に図を記述します。

```mermaid
flowchart LR
    Write[Markdownを書く] --> Preview[プレビューで確認]
    Preview --> Publish[公開または再利用]
```

## 9. AsciiMathによる数式

初心者向けのAsciiMathは、短く読みやすい記法です。インライン数式は `asciimath:a/b` のように書きます。

```asciimath
sum_(i=1)^n i^2 = (n(n+1)(2n+1))/6
```

## 10. LaTeXによる数式

LaTeXは数式交換の標準形式です。インライン数式は `math:\frac{a}{b}` のように書きます。

```math
\int_{0}^{1} x^2\,dx = \frac{1}{3}
```

数式へマウスを重ねるかキーボードでフォーカスすると、LaTeXをコピーするボタンが現れます。

## 11. 文書の再利用

保存したMarkdown文書は、その文書自身の公開URLで表示できます。別の投稿や固定ページでは、次のショートコードで同じ文書を参照できます。

```text
[ozeki_markdown_document id="123"]
```

`123`は、公開したMarkdown文書のIDへ置き換えてください。Markdown原文は`.md`ファイルとしていつでもエクスポートできます。
MARKDOWN;
    }

    private static function mermaidSource(): string
    {
        return <<<'MARKDOWN'
# Mermaid図表現の実践例

Mermaidはテキストから図を生成します。各例のコードを書き換え、右側のプレビューで変化を確認できます。

## 1. フローチャート

処理の流れ、判断、繰り返しを表現します。`TD`は上から下、`LR`は左から右です。

```mermaid
flowchart TD
    Start([開始]) --> Input[データを入力]
    Input --> Check{内容は正しい?}
    Check -->|はい| Save[(保存)]
    Check -->|いいえ| Input
    Save --> End([完了])
```

## 2. サブグラフを使った構成図

関連する要素をグループ化すると、システム構成を整理できます。

```mermaid
flowchart LR
    subgraph Browser[ブラウザー]
        Editor[Markdown編集]
        Preview[プレビュー]
    end
    subgraph WordPress[WordPress]
        Parser[安全な変換]
        Store[(Markdown保存)]
    end
    Editor --> Parser --> Preview
    Editor --> Store
```

## 3. シーケンス図

利用者とシステムの間で、処理が進む時間順序を表現します。

```mermaid
sequenceDiagram
    actor User as 利用者
    participant WP as WordPress
    participant DB as データベース
    User->>WP: Markdownを保存
    WP->>DB: 原文を記録
    DB-->>WP: 保存完了
    WP-->>User: プレビューを表示
```

## 4. 状態遷移図

文書やジョブの状態がどのように変化するかを示します。

```mermaid
stateDiagram-v2
    [*] --> Draft
    Draft --> Review: 確認を依頼
    Review --> Draft: 修正する
    Review --> Published: 承認する
    Published --> Archived: 公開を終了
    Archived --> [*]
```

## 5. クラス図

オブジェクトの属性、操作、関係を整理します。

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

## 6. ER図

データベースのエンティティと関連を表現します。

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

## 7. ガントチャート

作業期間と依存関係を含む計画を表現します。

```mermaid
gantt
    title 公開までの計画
    dateFormat YYYY-MM-DD
    section 実装
    基本機能       :done, build, 2026-09-01, 3d
    ガイド整備     :active, guide, after build, 2d
    section 品質確認
    自動テスト     :test, after guide, 2d
    公開準備       :release, after test, 1d
```

## 8. 円グラフ

全体に対する割合を簡潔に示します。

```mermaid
pie showData
    title 文書作成に使う時間
    "執筆" : 50
    "確認" : 30
    "修正" : 20
```

## 安全に使うために

- 図は内容を絞り、1つの図へ情報を詰め込みすぎないようにします。
- 外部JavaScriptやクリック処理は使用できません。
- 構文エラーがある場合は、元のMermaidコードが表示されたままになります。
- 図の原文はMarkdown内に残るため、未対応のビューアーでも読み取れます。
MARKDOWN;
    }

    private static function mathSource(): string
    {
        return <<<'MARKDOWN'
# AsciiMathとLaTeXの数式実践例

AsciiMathは入力しやすく、LaTeXは幅広い数式を厳密に表現できます。数式へマウスを重ねると、LaTeXをコピーできます。

## 1. 四則演算・分数・累乗

AsciiMathでは、紙に書く感覚に近い短い記法を使えます。

```asciimath
(a+b)/c + x^2 - y_1
```

同じ考え方をLaTeXで記述すると次のようになります。

```math
\frac{a+b}{c} + x^2 - y_1
```

## 2. 平方根と括弧

```asciimath
sqrt(x^2+y^2) = r
```

```math
\sqrt{x^2+y^2}=r
```

## 3. 総和と積

```asciimath
sum_(i=1)^n i^2 = (n(n+1)(2n+1))/6
```

```math
\sum_{i=1}^{n} i^2 = \frac{n(n+1)(2n+1)}{6}
```

## 4. 極限

```asciimath
lim_(x->0) (sin x)/x = 1
```

```math
\lim_{x \to 0}\frac{\sin x}{x}=1
```

## 5. 微分と積分

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

## 6. ベクトル

```asciimath
vec(v) = (v_1;v_2;v_3), norm(vec(v)) = sqrt(v_1^2+v_2^2+v_3^2)
```

```math
\vec{v}=\begin{pmatrix}v_1\\v_2\\v_3\end{pmatrix},
\qquad \lVert\vec{v}\rVert=\sqrt{v_1^2+v_2^2+v_3^2}
```

## 7. 行列

AsciiMathでは、`,`で列を、`;`で行を区切ります。

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

## 8. 集合と論理

```asciimath
A nn B = {x | x in A ^^ x in B}, A sube B
```

```math
A \cap B=\{x \mid x\in A \wedge x\in B\},
\qquad A\subseteq B
```

## 9. ギリシャ文字

```asciimath
alpha, beta, gamma, Delta, theta, lambda, mu, pi, sigma, Omega
```

```math
\alpha,\ \beta,\ \gamma,\ \Delta,\ \theta,\ \lambda,\ \mu,\ \pi,\ \sigma,\ \Omega
```

## 10. 複数行の式

AsciiMathでは空行で改行し、`&`で等号の位置を揃えられます。

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

## 11. 場合分け

```asciimath
abs(x) = {x if x >= 0; -x if x < 0:}
```

```math
|x|=\begin{cases}
x & \text{if }x\ge 0 \\
-x & \text{if }x<0
\end{cases}
```

## 12. インライン数式

文章の途中では `asciimath:E=mc^2` や `math:\frac{-b\pm\sqrt{b^2-4ac}}{2a}` のように、コード記法へ接頭辞を付けます。

## 使い分け

- すばやく入力する場合はAsciiMathから始めます。
- AI、論文、他システムと交換する場合はLaTeXが適しています。
- プレビューは生成結果であり、保存されるMarkdown原文を書き換えません。
- 数式の一部と通常文をまとめて選択した場合は、ブラウザー標準のコピー動作を維持します。
MARKDOWN;
    }
}
