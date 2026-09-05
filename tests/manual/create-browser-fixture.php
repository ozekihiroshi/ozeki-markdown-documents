<?php

use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\MarkdownSource;

if (! defined('ABSPATH')) {
    fwrite(STDERR, "Run this file with wp eval-file.\n");
    exit(1);
}

$source = <<<'MARKDOWN'
# Browser Security Regression

<script>This must never execute</script>

[Unsafe Markdown URL](javascript:alert(1))

```mermaid
flowchart LR
    A[Markdown] --> B[Safe diagram]
```

```mermaid
flowchart TD
    Broken[
```

```mermaid
flowchart LR
    A[Unsafe action] --> B[Still readable]
    click A "javascript:alert(1)"
```

Inline AsciiMath: `asciimath:a/b`.

```asciimath
sum_(i=1)^n i^2
```

```math
\frac{x+1}{y}
```

```math
\href{javascript:alert(1)}{unsafe}
```

```math
\frac{unclosed
```
MARKDOWN;

$postId = wp_insert_post(
    [
        'post_type' => DocumentPostType::POST_TYPE,
        'post_status' => 'publish',
        'post_title' => 'Browser Security Regression',
    ],
    true
);

if (is_wp_error($postId)) {
    throw new RuntimeException($postId->get_error_message());
}

(new MarkdownSource())->store($postId, $source);

echo 'post_id=' . $postId . PHP_EOL;
echo 'url=' . get_permalink($postId) . PHP_EOL;
