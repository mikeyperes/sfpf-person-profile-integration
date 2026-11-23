/* Adds sets and questions in the FAQ editor. */
(function (config) {
    'use strict';
    var root = document.getElementById('sfpf-dashboard-tabs');
    if (!root) { return; }

    root.addEventListener('click', function (event) {
        var target = event.target;

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
}(window.sfpfAdmin));
