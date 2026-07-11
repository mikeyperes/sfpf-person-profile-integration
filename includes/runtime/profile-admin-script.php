<?php

declare( strict_types=1 );

namespace sfpf_person_website;

/**
 * Profile-screen URL sanitation and article import controls.
 *
 * Callback names remain in the legacy namespace for template and hook compatibility.
 */

defined( 'ABSPATH' ) || exit;

// =============================================================================

/**
 * Add jQuery handler for sanitize URLs button and articles bulk import on user profile pages
 */
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, ['profile', 'user-edit'])) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Old sanitize URLs button (for sameas textarea)
        $(document).on('click', '.sfpf-sanitize-urls', function(e) {
            e.preventDefault();
            var targetKey = $(this).data('target');
            var $textarea = $('[name="acf[' + targetKey + ']"]');
            if (!$textarea.length) {
                $textarea = $('#acf-' + targetKey + ' textarea');
            }
            if (!$textarea.length) return;

            var raw = $textarea.val();
            if (!raw.trim()) return;

            var parts = raw.split(/[\n\r,\s]+/);
            var cleaned = [];
            parts.forEach(function(part) {
                part = part.trim();
                if (!part) return;
                part = part.replace(/[,;]+$/, '');
                if (!part.match(/\S+\.\S+/)) return;
                part = part.replace(/^https?:\/\//i, '');
                part = part.replace(/^www\./i, '');
                if (!part) return;
                cleaned.push('https://' + part);
            });

            $textarea.val(cleaned.join("\n"));

            var $btn = $(this);
            var origText = $btn.text();
            $btn.text('Cleaned ' + cleaned.length + ' URLs').prop('disabled', true);
            setTimeout(function() { $btn.text(origText).prop('disabled', false); }, 2000);
        });

        // Articles bulk import handler
        $(document).on('click', '#sfpf-process-articles', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $input = $('#sfpf-articles-bulk-input');
            var $report = $('#sfpf-articles-report');
            var $header = $('#sfpf-articles-report-header');
            var $body = $('#sfpf-articles-report-body');
            var $footer = $('#sfpf-articles-report-footer');
            var $spinner = $('#sfpf-articles-spinner');
            var raw = $input.val();

            if (!raw.trim()) {
                $report.css('display', 'flex');
                $header.html('<span style="color:#fbbf24;font-weight:600;">⚠ No input</span>');
                $body.html('<span style="color:#94a3b8;">Paste URLs or HTML above.</span>');
                $footer.empty();
                return;
            }

            $btn.prop('disabled', true).text('Processing...');
            $spinner.css({display: 'inline-block', visibility: 'visible'}).addClass('is-active');
            $report.css('display', 'flex');
            $header.html('<span style="color:#94a3b8;">⏳ Processing...</span>');
            $body.html('<span style="color:#94a3b8;">Sanitizing URLs, checking duplicates, fetching titles...<br>This may take a moment for many URLs.</span>');
            $footer.empty();

            var userId = $('input[name="user_id"]').val() || $('input[name="checkuser_id"]').val() || '0';

            $.post(ajaxurl, {
                action: 'sfpf_process_articles',
                nonce: '<?php echo wp_create_nonce('sfpf_ajax'); ?>',
                urls: raw,
                user_id: userId
            }, function(response) {
                $spinner.css({display: 'none', visibility: 'hidden'}).removeClass('is-active');
                $btn.prop('disabled', false).text('⚡ Process & Import');

                if (response.success) {
                    var d = response.data;

                    // ── Inject articles into ACF repeater ──
                    if (d.articles && d.articles.length > 0) {
                        var $repeater = $('[data-key="field_sfpf_articles"]').find('.acf-repeater');

                        $.each(d.articles, function(i, article) {
                            $repeater.find('> .acf-actions .acf-repeater-add-row').trigger('click');
                            var $row = $repeater.find('tbody > tr.acf-row:not(.acf-clone)').last();
                            $row.find('[data-key="field_sfpf_article_title"] input').val(article.title || '');
                            $row.find('[data-key="field_sfpf_article_source"] input').val(article.source || '');
                            $row.find('[data-key="field_sfpf_article_url"] input').val(article.url || '');
                            $row.find('input').trigger('change');
                        });
                    }

                    // Header
                    $header.html('<div style="color:#4ade80;font-weight:700;font-size:14px;">✅ Import complete — ' + d.imported + ' articles added (' + d.total + ' total)</div>');

                    // Body — scrollable report
                    var bodyHtml = d.report.replace(/\n/g, '<br>');
                    if (d.original_input) {
                        bodyHtml += '<details style="margin-top:16px;border-top:1px solid #334155;padding-top:12px;">';
                        bodyHtml += '<summary style="cursor:pointer;color:#94a3b8;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Original Input</summary>';
                        bodyHtml += '<pre style="margin-top:8px;padding:10px;background:#0f172a;border-radius:4px;color:#64748b;font-size:11px;white-space:pre-wrap;word-break:break-all;max-height:150px;overflow-y:auto;">' + $('<span>').text(d.original_input).html() + '</pre>';
                        bodyHtml += '</details>';
                    }
                    $body.html(bodyHtml);

                    // Footer
                    if (d.imported > 0) {
                        $footer.html('<div style="color:#93c5fd;font-size:13px;">💾 <strong>' + d.imported + ' articles</strong> added to repeater. <strong>Save/Update the profile</strong> to persist.</div>');
                    } else {
                        $footer.html('<div style="color:#94a3b8;font-size:12px;">No new articles to add.</div>');
                    }

                    $input.val('');
                } else {
                    $header.html('<span style="color:#f87171;font-weight:600;">❌ Error</span>');
                    $body.html('<div style="color:#f87171;">' + (response.data || 'Unknown error') + '</div>');
                    $footer.empty();
                }
            }).fail(function(xhr) {
                $spinner.css({display: 'none', visibility: 'hidden'}).removeClass('is-active');
                $btn.prop('disabled', false).text('⚡ Process & Import');
                $header.html('<span style="color:#f87171;font-weight:600;">❌ AJAX Failed</span>');
                $body.html('<div style="color:#f87171;">Request failed. Check your connection.</div>');
                $footer.empty();
            });
        });

        // Remove All Articles handler
        $(document).on('click', '#sfpf-remove-all-articles', function(e) {
            e.preventDefault();

            var $repeater = $('[data-key="field_sfpf_articles"]').find('.acf-repeater');
            var $rows = $repeater.find('tbody > tr.acf-row:not(.acf-clone)');
            var count = $rows.length;

            if (count === 0) {
                alert('No articles to remove.');
                return;
            }

            if (!confirm('Remove all ' + count + ' articles from the repeater?\n\nThis won\'t be permanent until you save the profile.')) {
                return;
            }

            // Remove rows from last to first to avoid index issues
            $($rows.get().reverse()).each(function() {
                $(this).find('.acf-row-handle .acf-icon.-minus').trigger('click');
            });

            // Show confirmation in report area
            var $report = $('#sfpf-articles-report');
            var $header = $('#sfpf-articles-report-header');
            var $body = $('#sfpf-articles-report-body');
            var $footer = $('#sfpf-articles-report-footer');
            $report.css('display', 'flex');
            $header.html('<div style="color:#fbbf24;font-weight:700;font-size:14px;">🗑 Removed ' + count + ' articles</div>');
            $body.html('<span style="color:#94a3b8;">All rows cleared from the repeater.</span>');
            $footer.html('<div style="color:#f59e0b;font-size:13px;">⚠️ <strong>Save/Update the profile</strong> to make this permanent.</div>');
        });

        // KGID dynamic URL display
        function updateKgidLink() {
            var $field = $('[data-name="knowledge_graph_id"] input[type="text"]');
            var $display = $('#sfpf-kgid-link-display');
            if (!$field.length || !$display.length) return;
            var val = $field.val().trim();
            if (val) {
                var fullUrl = 'https://www.google.com/search?kgmid=' + encodeURIComponent(val);
                $display.html('<a href="' + fullUrl + '" target="_blank" style="color:#2563eb;word-break:break-all;">' + fullUrl + '</a> — opens Knowledge Panel in browser');
            } else {
                $display.html('Enter a KGMID above to see the full Knowledge Panel URL.');
            }
        }
        updateKgidLink();
        $(document).on('input change', '[data-name="knowledge_graph_id"] input[type="text"]', updateKgidLink);
    });
    </script>
    <?php
});
