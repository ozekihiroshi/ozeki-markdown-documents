import { mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { spawnSync } from 'node:child_process';

if (process.argv.length !== 4) {
    console.error('Usage: node test-mermaid-browser.mjs BROWSER_PATH URL');
    process.exit(2);
}

const browserPath = process.argv[2];
const url = process.argv[3];
const profile = mkdtempSync(join(tmpdir(), 'ozmd-browser-'));
const result = spawnSync(
    browserPath,
    [
        '--headless=new',
        '--disable-gpu',
        '--no-first-run',
        '--user-data-dir=' + profile,
        '--virtual-time-budget=15000',
        '--dump-dom',
        url
    ],
    {
        encoding: 'utf8',
        maxBuffer: 16 * 1024 * 1024,
        timeout: 30000
    }
);

if (result.error) {
    throw result.error;
}

if (result.status !== 0) {
    console.error(result.stderr);
    throw new Error('Headless browser exited with status ' + result.status);
}

const dom = result.stdout;
const externalLibraryAssets = Array.from(
    dom.matchAll(/(?:src|href)="([^"]+)"/gi),
    (match) => match[1]
).filter((value) => {
    if (!/(?:mermaid|katex|asciimath|jsdelivr|unpkg)/i.test(value)) {
        return false;
    }

    try {
        const assetUrl = new URL(value, url);
        return !['localhost', '127.0.0.1'].includes(assetUrl.hostname);
    } catch {
        return true;
    }
});
const assertions = {
    documentWrapper: dom.includes('ozmd-document'),
    frontendStylesheet: dom.includes('assets/frontend.css'),
    localMermaidAsset: dom.includes('assets/vendor/mermaid/mermaid.min.js'),
    localRenderer: dom.includes('assets/mermaid-render.js'),
    renderedFigure: dom.includes('class="ozmd-mermaid"'),
    renderedSvg: dom.includes('<svg'),
    invalidDiagramFallback: dom.includes('ozmd-mermaid-error'),
    invalidSourceRetained: dom.includes('language-mermaid'),
    unsafeMermaidLinkAbsent: !dom.includes('href="javascript:alert'),
    localMathRenderer: dom.includes('assets/math-render.js'),
    localKatexStylesheet: dom.includes('assets/vendor/katex/katex.min.css'),
    asciiMathRendered: dom.includes('data-ozmd-math-format="asciimath"'),
    latexRendered: dom.includes('data-ozmd-math-format="latex"'),
    katexOutputPresent: dom.includes('class="katex"'),
    copyLatexButtonPresent: dom.includes('class="ozmd-math-copy"'),
    invalidMathFallback: dom.includes('ozmd-math-error'),
    unsafeMathLinkAbsent: !dom.includes('href="javascript:'),
    rawScriptAbsent: !dom.includes('<script>This must never execute'),
    externalLibraryAssetsAbsent: externalLibraryAssets.length === 0
};

for (const [name, passed] of Object.entries(assertions)) {
    console.log(name + '=' + (passed ? 'yes' : 'no'));
}

if (Object.values(assertions).includes(false)) {
    externalLibraryAssets.forEach((asset) => console.error('external_asset=' + asset));
    process.exit(1);
}

console.log('result=mermaid_svg_rendered');
