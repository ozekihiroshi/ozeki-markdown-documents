import katex from 'katex';
import { AsciiMath } from 'asciimath-parser';

(function (global) {
    'use strict';

    const MAX_FORMULAS = 100;
    const MAX_SOURCE_LENGTH = 10000;
    const asciiMath = new AsciiMath({ display: false });

    function config() {
        return global.ozmdMathConfig || {
            katexCssUrl: '',
            labels: {
                copy: 'Copy LaTeX',
                copied: 'Copied',
                copyFailed: 'Copy failed'
            }
        };
    }

    function ensureStylesheet() {
        const url = config().katexCssUrl;
        if (!url) {
            return Promise.reject(new Error('KaTeX stylesheet URL is missing.'));
        }

        const existing = document.querySelector('link[data-ozmd-katex-css]');
        if (existing) {
            if (existing.sheet) {
                return Promise.resolve();
            }

            return new Promise(function (resolve, reject) {
                existing.addEventListener('load', resolve, { once: true });
                existing.addEventListener('error', reject, { once: true });
            });
        }

        return new Promise(function (resolve, reject) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            link.dataset.ozmdKatexCss = 'true';
            link.addEventListener('load', resolve, { once: true });
            link.addEventListener('error', reject, { once: true });
            document.head.appendChild(link);
        });
    }

    function describe(code) {
        const pre = code.parentElement;
        if (pre && pre.tagName === 'PRE') {
            if (code.classList.contains('language-asciimath')) {
                return {
                    code: code,
                    display: true,
                    format: 'asciimath',
                    source: code.textContent || ''
                };
            }

            if (
                code.classList.contains('language-math') ||
                code.classList.contains('language-latex')
            ) {
                return {
                    code: code,
                    display: true,
                    format: 'latex',
                    source: code.textContent || ''
                };
            }

            return null;
        }

        const value = code.textContent || '';
        const candidates = [
            ['asciimath:', 'asciimath'],
            ['latex:', 'latex'],
            ['math:', 'latex']
        ];

        for (const candidate of candidates) {
            if (value.startsWith(candidate[0])) {
                return {
                    code: code,
                    display: false,
                    format: candidate[1],
                    source: value.slice(candidate[0].length).trim()
                };
            }
        }

        return null;
    }

    function copyText(value) {
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            return navigator.clipboard.writeText(value);
        }

        return new Promise(function (resolve, reject) {
            const textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                if (!document.execCommand('copy')) {
                    throw new Error('Copy command was rejected.');
                }
                resolve();
            } catch (error) {
                reject(error);
            } finally {
                textarea.remove();
            }
        });
    }

    function copyButton(latex) {
        const labels = config().labels;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ozmd-math-copy';
        button.textContent = labels.copy;
        button.setAttribute('aria-label', labels.copy);

        button.addEventListener('click', function () {
            copyText(latex)
                .then(function () {
                    button.textContent = labels.copied;
                })
                .catch(function () {
                    button.textContent = labels.copyFailed;
                })
                .finally(function () {
                    global.setTimeout(function () {
                        button.textContent = labels.copy;
                    }, 1500);
                });
        });

        return button;
    }

    function renderFormula(descriptor) {
        const source = descriptor.source;
        if (!source || source.length > MAX_SOURCE_LENGTH) {
            descriptor.code.classList.add('ozmd-math-error');
            return;
        }

        try {
            const latex = descriptor.format === 'asciimath'
                ? asciiMath.toTex(source, { display: descriptor.display })
                : source;
            const output = document.createElement(
                descriptor.display ? 'div' : 'span'
            );

            katex.render(latex, output, {
                displayMode: descriptor.display,
                output: 'htmlAndMathml',
                throwOnError: true,
                strict: 'error',
                trust: false,
                maxSize: 20,
                maxExpand: 1000,
                globalGroup: false,
                macros: {}
            });

            const wrapper = document.createElement(
                descriptor.display ? 'figure' : 'span'
            );
            wrapper.className = descriptor.display
                ? 'ozmd-math ozmd-math-display'
                : 'ozmd-math ozmd-math-inline';
            wrapper.dataset.ozmdMathFormat = descriptor.format;
            wrapper.appendChild(output);
            wrapper.appendChild(copyButton(latex));

            const replacement = descriptor.display
                ? descriptor.code.parentElement
                : descriptor.code;
            replacement.replaceWith(wrapper);
        } catch (error) {
            descriptor.code.classList.add('ozmd-math-error');
            descriptor.code.dataset.ozmdMathState = 'error';
        }
    }

    function render(root) {
        const scope = root && typeof root.querySelectorAll === 'function'
            ? root
            : document;
        const formulas = Array.from(scope.querySelectorAll('code'))
            .filter(function (code) {
                return !code.dataset.ozmdMathState;
            })
            .map(describe)
            .filter(Boolean)
            .slice(0, MAX_FORMULAS);

        if (formulas.length === 0) {
            return Promise.resolve();
        }

        formulas.forEach(function (formula) {
            formula.code.dataset.ozmdMathState = 'pending';
        });

        return ensureStylesheet()
            .then(function () {
                formulas.forEach(renderFormula);
            })
            .catch(function () {
                formulas.forEach(function (formula) {
                    formula.code.classList.add('ozmd-math-error');
                    formula.code.dataset.ozmdMathState = 'error';
                });
            });
    }

    global.ozmdRenderMath = render;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            render(document);
        });
    } else {
        render(document);
    }
}(window));
