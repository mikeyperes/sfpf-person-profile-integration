/* Person profile fields on the WordPress user profile screens. */
(function ($, config) {
    'use strict';

    // Fields for another entity type are hidden, never removed, so saving never clears them.
    function entityType() {
        var checked = $('input[name="acf[field_sfpf_entity_type]"]:checked');
        if (checked.length) { return checked.val(); }
        var selected = $('[data-key="field_sfpf_entity_type"] .acf-button-group label.selected input');
        return selected.length ? selected.val() : 'none';
    }
    function updateEntityFields() {
        var type = entityType();
        $('.acf-field.sfpf-entity-person').toggleClass('sfpf-entity-hidden', type !== 'person');
        $('.acf-field.sfpf-entity-organization').toggleClass('sfpf-entity-hidden', type !== 'organization');
        $('.acf-field.sfpf-entity-person-or-org').toggleClass('sfpf-entity-hidden', type !== 'person' && type !== 'organization');
    }

    // The Knowledge Panel link for the Knowledge Graph ID being typed.
    function updateKnowledgePanelLink() {
        var input = $('[data-name="knowledge_graph_id"] input[type="text"]');
        var out = $('#sfpf-kgid-link-display');
        if (!input.length || !out.length) { return; }
        var match = String(input.val() || '').match(/[?&]kgmid=([^&\s#]+)/i);
        var id = decodeURIComponent(match ? match[1] : String(input.val() || '').trim());
        if (/^(g|m)\//.test(id)) { id = '/' + id; }
        if (!/^\/(g|m)\/[A-Za-z0-9_-]+$/.test(id)) { out.text(id ? config.i18n.invalidKgid : ''); return; }
        var url = 'https://www.google.com/search?kgmid=' + encodeURIComponent(id) + '&hl=en-US';
        out.empty().append($('<a target="_blank" rel="noopener">').attr('href', url).text(url));
    }

    // Article import: the server cleans the pasted links and fetches their titles; rows are added to the form to review and save.
    function report(state, header, body, footer) {
        $('#sfpf-articles-report').addClass('is-open');
        $('#sfpf-articles-report-header').html($('<span>').addClass('sfpf-report-' + state).text(header));
        $('#sfpf-articles-report-body').text(body || '');
        $('#sfpf-articles-report-footer').text(footer || '');
    }
    function addArticleRows(articles) {
        var repeater = $('[data-key="field_sfpf_articles"]').find('.acf-repeater').first();
        $.each(articles, function (index, article) {
            repeater.find('> .acf-actions .acf-repeater-add-row').trigger('click');
            var row = repeater.find('tbody > tr.acf-row:not(.acf-clone)').last();
            row.find('[data-key="field_sfpf_article_title"] input').val(article.title || '');
            row.find('[data-key="field_sfpf_article_source"] input').val(article.source || '');
            row.find('[data-key="field_sfpf_article_url"] input').val(article.url || '').trigger('change');
        });
    }
    function importArticles(event) {
        event.preventDefault();
        var button = $(this), input = $('#sfpf-articles-bulk-input'), spinner = $('#sfpf-articles-spinner');
        if (!String(input.val() || '').trim()) { report('warn', config.i18n.noInput, config.i18n.pasteFirst, ''); return; }
        button.prop('disabled', true); spinner.addClass('is-active').css({display: 'inline-block', visibility: 'visible'});
        report('muted', config.i18n.working, config.i18n.workingBody, '');
        $.post(config.ajaxUrl, {
            action: config.importAction,
            nonce: config.nonce,
            urls: input.val(),
            user_id: $('input[name="user_id"]').val() || $('input[name="checkuser_id"]').val() || 0
        }).done(function (response) {
            if (!response || !response.success) {
                report('bad', config.i18n.failed, (response && response.data && response.data.message) || '', '');
                return;
            }
            var data = response.data;
            addArticleRows(data.articles || []);
            report('ok', data.summary, data.report, data.imported > 0 ? config.i18n.saveToKeep : config.i18n.nothingNew);
            input.val('');
        }).fail(function () {
            report('bad', config.i18n.failed, config.i18n.requestFailed, '');
        }).always(function () {
            button.prop('disabled', false); spinner.removeClass('is-active').css({display: 'none', visibility: 'hidden'});
        });
    }
    function removeAllArticles(event) {
        event.preventDefault();
        var rows = $('[data-key="field_sfpf_articles"]').find('.acf-repeater').first().find('tbody > tr.acf-row:not(.acf-clone)');
        if (!rows.length || !window.confirm(config.i18n.confirmRemove.replace('%d', rows.length))) { return; }
        $(rows.get().reverse()).each(function () { $(this).find('.acf-row-handle .acf-icon.-minus').trigger('click'); });
        report('warn', config.i18n.removed.replace('%d', rows.length), '', config.i18n.saveToKeep);
    }

    $(function () {
        setTimeout(updateEntityFields, 100);
        $(document).on('change', 'input[name="acf[field_sfpf_entity_type]"]', updateEntityFields);
        $(document).on('click', '[data-key="field_sfpf_entity_type"] .acf-button-group label', function () { setTimeout(updateEntityFields, 50); });
        updateKnowledgePanelLink();
        $(document).on('input change', '[data-name="knowledge_graph_id"] input[type="text"]', updateKnowledgePanelLink);
        $(document).on('click', '#sfpf-process-articles', importArticles);
        $(document).on('click', '#sfpf-remove-all-articles', removeAllArticles);
    });
}(jQuery, window.sfpfProfileScreen || {}));
