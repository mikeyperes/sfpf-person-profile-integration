/* Shared settings form and shortcode controls for the SFPF dashboard. */
(function (config) {
    'use strict';
    var root = document.getElementById('sfpf-dashboard-tabs');
    if (!root) { return; }

    function post(action, body) {
        body.set('action', action);
        body.set('nonce', config.nonce);
        return fetch(config.ajaxUrl, {method: 'POST', credentials: 'same-origin', body: body})
            .then(function (response) { return response.json(); })
            .then(function (json) {
                if (!json || !json.success) {
                    throw new Error((json && json.data && json.data.message) || 'The request failed.');
                }
                return json.data || {};
            });
    }

    function status(element, text, bad) {
        if (!element) { return; }
        element.textContent = text || '';
        element.classList.toggle('is-bad', !!bad);
    }

    config.post = post;
    config.status = status;

    // Forms that save through admin-ajax.
    root.addEventListener('submit', function (event) {
        var form = event.target.closest('form.sfpf-ajax-form');
        if (!form) { return; }
        event.preventDefault();
        var button = form.querySelector('button[type=submit]');
        var out = form.querySelector('.sfpf-form-status');
        button.disabled = true;
        status(out, 'Saving…');
        post(form.dataset.sfpfAction, new FormData(form))
            .then(function (data) { status(out, data.message || 'Saved.'); })
            .catch(function (error) { status(out, error.message, true); })
            .finally(function () { button.disabled = false; });
    });

    root.addEventListener('click', function (event) {
        var target = event.target;

        // Copy a shortcode.
        var code = target.closest('.sfpf-shortcode');
        if (code && navigator.clipboard) {
            navigator.clipboard.writeText(code.textContent).then(function () {
                code.classList.add('is-copied');
                setTimeout(function () { code.classList.remove('is-copied'); }, 1200);
            });
            return;
        }

    });
}(window.sfpfAdmin));
