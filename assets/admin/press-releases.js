/* Press release source and profile controls. */
(function (config) {
    'use strict';
    var root = document.getElementById('sfpf-dashboard-tabs');
    if (!root) { return; }

    function pressPage(form, button, body) {
        var search = body.get('origin') + ':' + body.get('profile_name');
        var page = form.dataset.pressSearch === search ? Number(form.dataset.pressPage || 1) : 1;
        if (button.dataset.sfpfPressPage === 'previous') { page = Math.max(1, page - 1); }
        if (button.dataset.sfpfPressPage === 'next') { page += 1; }
        return button.dataset.sfpfPressPage ? page : 1;
    }

    function updatePressProfiles(form, data, body) {
        var select = form.querySelector('[data-sfpf-press-profiles]');
        var selected = select.value;
        form.dataset.pressPage = String(data.page);
        form.dataset.pressTotal = String(data.total_pages);
        form.dataset.pressSearch = body.get('origin') + ':' + body.get('profile_name');
        select.replaceChildren(new Option('Choose a profile', '0'));
        data.profiles.forEach(function (profile) {
            var option = new Option(profile.name, String(profile.id));
            option.selected = String(profile.id) === selected;
            select.add(option);
        });
        var choice = select.selectedOptions[0];
        form.querySelector('[name="profile_label"]').value = choice && select.value !== '0' ? choice.textContent : '';
    }

    function pressButtons(form) {
        var page = Number(form.dataset.pressPage || 1);
        var total = Number(form.dataset.pressTotal || 0);
        form.querySelector('[data-sfpf-press-page="previous"]').disabled = page <= 1;
        form.querySelector('[data-sfpf-press-page="next"]').disabled = page >= total;
    }

    function checkPressConnection(button) {
        var form = button.closest('form');
        var body = new FormData(form);
        var output = button.parentNode.querySelector('.sfpf-form-status');
        var requestId = Number(form.dataset.pressRequest || 0) + 1;
        form.dataset.pressRequest = String(requestId);
        body.set('mode', button.dataset.sfpfPressCheck);
        body.set('profile_page', String(pressPage(form, button, body)));
        button.disabled = true;
        config.status(output, 'Checking…');
        config.post(config.actions.pressCheck, body)
            .then(function (data) {
                if (Number(form.dataset.pressRequest) !== requestId) { return; }
                if (Array.isArray(data.profiles)) { updatePressProfiles(form, data, body); }
                config.status(output, data.message || 'Connected.');
                if (data.profile) {
                    var link = document.createElement('a');
                    link.href = data.profile.url;
                    link.textContent = ' Open profile';
                    link.target = '_blank';
                    link.rel = 'noopener';
                    output.appendChild(link);
                }
            })
            .catch(function (error) {
                if (Number(form.dataset.pressRequest) === requestId) { config.status(output, error.message, true); }
            })
            .finally(function () {
                button.disabled = false;
                pressButtons(form);
            });
    }

    root.addEventListener('change', function (event) {
        var select = event.target.closest('[data-sfpf-press-profiles]');
        if (select) {
            var option = select.selectedOptions[0];
            select.form.querySelector('[name="profile_label"]').value = option && select.value !== '0' ? option.textContent : '';
        }
    });

    root.addEventListener('input', function (event) {
        var form = event.target.closest('form[data-sfpf-action="sfpf_save_press_releases"]');
        if (!form) { return; }
        form.dataset.pressRequest = String(Number(form.dataset.pressRequest || 0) + 1);
        if (event.target.name === 'origin' || event.target.name === 'profile_name') {
            delete form.dataset.pressSearch;
            delete form.dataset.pressPage;
            delete form.dataset.pressTotal;
            pressButtons(form);
        }
    });

    root.addEventListener('click', function (event) {
        var button = event.target.closest('[data-sfpf-press-check]');
        if (button) {
            checkPressConnection(button);
        }
    });
}(window.sfpfAdmin));
