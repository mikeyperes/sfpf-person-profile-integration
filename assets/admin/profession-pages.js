/* Creates and removes profession pages from the Pages tab. */
(function (config) {
    'use strict';
    var root = document.getElementById('sfpf-dashboard-tabs');
    if (!root) { return; }

    root.addEventListener('click', function (event) {
        var target = event.target;

        // Create or remove a profession's page.
        var profession = target.closest('[data-sfpf-profession]');
        if (profession) {
            var remove = profession.dataset.sfpfProfession === 'delete';
            if (remove && !window.confirm('Move this profession\'s page to the trash?')) { return; }
            var body = new URLSearchParams({index: profession.dataset.index, page: profession.dataset.page || 0});
            var professionStatus = profession.parentNode.querySelector('.sfpf-form-status');
            profession.disabled = true;
            config.post(remove ? config.actions.deleteProfession : config.actions.createProfession, body)
                .then(function (data) { config.status(professionStatus, remove ? (data.message || 'Done.') : 'Created: ' + (data.title || '')); })
                .catch(function (error) { config.status(professionStatus, error.message, true); profession.disabled = false; });
            return;
        }

    });
}(window.sfpfAdmin));
