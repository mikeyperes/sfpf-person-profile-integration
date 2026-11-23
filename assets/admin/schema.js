/* Runs the schema scan from the Schema tab. */
(function (config) {
    'use strict';
    var root = document.getElementById('sfpf-dashboard-tabs');
    if (!root) { return; }

    root.addEventListener('click', function (event) {
        var target = event.target;

        // Scan the live pages for schema.
        var scan = target.closest('[data-sfpf-scan]');
        if (scan) {
            var card = scan.closest('.hpc-card') || document;
            var output = card.querySelector('[data-sfpf-scan-output]');
            var scanStatus = scan.parentNode.querySelector('.sfpf-form-status');
            scan.disabled = true;
            config.status(scanStatus, 'Loading the pages…');
            config.post(config.actions.scan, new URLSearchParams())
                .then(function (data) { output.innerHTML = data.html || ''; config.status(scanStatus, ''); })
                .catch(function (error) { config.status(scanStatus, error.message, true); })
                .finally(function () { scan.disabled = false; });
            return;
        }

    });
}(window.sfpfAdmin));
