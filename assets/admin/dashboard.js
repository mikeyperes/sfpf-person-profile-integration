/* SFPF Person Profile settings page. Tabs load by AJAX, so every handler is delegated from the document. */
(function (config) {
    'use strict';

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
        status(output, 'Checking…');
        post(config.actions.pressCheck, body)
            .then(function (data) {
                if (Number(form.dataset.pressRequest) !== requestId) { return; }
                if (Array.isArray(data.profiles)) { updatePressProfiles(form, data, body); }
                status(output, data.message || 'Connected.');
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
                if (Number(form.dataset.pressRequest) === requestId) { status(output, error.message, true); }
            })
            .finally(function () {
                button.disabled = false;
                pressButtons(form);
            });
    }

    // Forms that save through admin-ajax.
    document.addEventListener('submit', function (event) {
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

    document.addEventListener('change', function (event) {
        var select = event.target.closest('[data-sfpf-press-profiles]');
        if (select) {
            var option = select.selectedOptions[0];
            select.form.querySelector('[name="profile_label"]').value = option && select.value !== '0' ? option.textContent : '';
        }
    });

    document.addEventListener('input', function (event) {
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

    document.addEventListener('click', function (event) {
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

        // Press-release checks never save the form.
        var press = target.closest('[data-sfpf-press-check]');
        if (press) {
            checkPressConnection(press);
            return;
        }

        // Scan the live pages for schema.
        var scan = target.closest('[data-sfpf-scan]');
        if (scan) {
            var card = scan.closest('.hpc-card') || document;
            var output = card.querySelector('[data-sfpf-scan-output]');
            var scanStatus = scan.parentNode.querySelector('.sfpf-form-status');
            scan.disabled = true;
            status(scanStatus, 'Loading the pages…');
            post(config.actions.scan, new URLSearchParams())
                .then(function (data) { output.innerHTML = data.html || ''; status(scanStatus, ''); })
                .catch(function (error) { status(scanStatus, error.message, true); })
                .finally(function () { scan.disabled = false; });
            return;
        }

        // Create or remove a profession's page.
        var profession = target.closest('[data-sfpf-profession]');
        if (profession) {
            var remove = profession.dataset.sfpfProfession === 'delete';
            if (remove && !window.confirm('Move this profession\'s page to the trash?')) { return; }
            var body = new URLSearchParams({index: profession.dataset.index, page: profession.dataset.page || 0});
            var professionStatus = profession.parentNode.querySelector('.sfpf-form-status');
            profession.disabled = true;
            post(remove ? config.actions.deleteProfession : config.actions.createProfession, body)
                .then(function (data) { status(professionStatus, remove ? (data.message || 'Done.') : 'Created: ' + (data.title || '')); })
                .catch(function (error) { status(professionStatus, error.message, true); profession.disabled = false; });
            return;
        }

        // FAQ set editor: add a question to a set, or a new set.
        var addQuestion = target.closest('[data-sfpf-add-question]');
        if (addQuestion) {
            var set = addQuestion.closest('.sfpf-faq-set');
            var rows = set.querySelector('.sfpf-faq-rows');
            var last = rows.lastElementChild;
            var next = last.cloneNode(true);
            var index = rows.children.length;
            next.querySelectorAll('input,textarea').forEach(function (field) {
                field.name = field.name.replace(/\[items\]\[\d+\]/, '[items][' + index + ']');
                field.value = '';
            });
            rows.appendChild(next);
            return;
        }
        var addSet = target.closest('[data-sfpf-add-set]');
        if (addSet) {
            var sets = addSet.closest('form').querySelector('[data-sfpf-faq-sets]');
            var template = sets.lastElementChild.cloneNode(true);
            var setIndex = sets.children.length;
            template.dataset.index = setIndex;
            template.querySelector('legend').textContent = 'New FAQ set';
            template.querySelectorAll('.sfpf-faq-row:not(:first-child)').forEach(function (row) { row.remove(); });
            template.querySelectorAll('.sfpf-shortcode').forEach(function (node) { node.remove(); });
            template.querySelectorAll('input,textarea').forEach(function (field) {
                field.name = field.name.replace(/^sets\[\d+\]/, 'sets[' + setIndex + ']').replace(/\[items\]\[\d+\]/, '[items][0]');
                if (field.type === 'radio') { field.checked = false; field.value = ''; } else { field.value = ''; }
            });
            sets.appendChild(template);
        }
    });
}(window.sfpfAdmin || {}));
