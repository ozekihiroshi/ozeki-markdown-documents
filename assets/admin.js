(function () {
    'use strict';

    var textarea = document.getElementById('ozmd-source');
    var preview = document.getElementById('ozmd-preview');
    var status = document.getElementById('ozmd-preview-status');

    if (!textarea || !preview || !status || typeof ozmdPreview === 'undefined') {
        return;
    }

    var timer = null;
    var request = null;

    function setStatus(message, isError) {
        status.textContent = message;
        status.classList.toggle('is-error', Boolean(isError));
    }

    function updatePreview() {
        if (request) {
            request.abort();
        }

        var controller = new AbortController();
        request = controller;
        preview.setAttribute('aria-busy', 'true');
        setStatus(ozmdPreview.labels.loading, false);

        var body = new URLSearchParams();
        body.set('action', ozmdPreview.action);
        body.set('nonce', ozmdPreview.nonce);
        body.set('post_id', String(ozmdPreview.postId || 0));
        body.set('source', textarea.value);

        fetch(ozmdPreview.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString(),
            signal: controller.signal
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (!result.success || !result.data || typeof result.data.html !== 'string') {
                    throw new Error(
                        result.data && result.data.message
                            ? result.data.message
                            : ozmdPreview.labels.error
                    );
                }

                preview.innerHTML = result.data.html;
                if (typeof window.ozmdRenderMermaid === 'function') {
                    window.ozmdRenderMermaid(preview);
                }
                if (typeof window.ozmdRenderMath === 'function') {
                    window.ozmdRenderMath(preview);
                }
                setStatus(ozmdPreview.labels.ready, false);
            })
            .catch(function (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                preview.textContent = '';
                setStatus(error.message || ozmdPreview.labels.error, true);
            })
            .finally(function () {
                if (request === controller) {
                    preview.setAttribute('aria-busy', 'false');
                }
            });
    }

    textarea.addEventListener('input', function () {
        window.clearTimeout(timer);
        timer = window.setTimeout(updatePreview, 500);
    });

    updatePreview();
}());
