<?php
namespace sfpf_person_website;

/**
 * Logging Functions
 * 
 * Activity logging for debugging and tracking.
 * 
 * @package sfpf_person_website
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Write to activity log
 * 
 * @param string $message Log message
 * @param string $type Log type (info, warning, error)
 */
function write_log($message, $type = 'info') {
    $log = get_option('sfpf_activity_log', []);
    
    // Keep only last 100 entries
    if (count($log) >= 100) {
        $log = array_slice($log, -99);
    }
    
    $log[] = [
        'timestamp' => current_time('mysql'),
        'message' => $message,
        'type' => $type,
        'user' => get_current_user_id(),
    ];
    
    update_option('sfpf_activity_log', $log);
}

/**
 * Get activity log
 * 
 * @param int $limit Number of entries to return
 * @return array Log entries
 */
function get_activity_log($limit = 50) {
    $log = get_option('sfpf_activity_log', []);
    return array_slice(array_reverse($log), 0, $limit);
}

/**
 * Clear activity log
 */
function clear_activity_log() {
    delete_option('sfpf_activity_log');
}

/**
 * Format log entry for display
 * 
 * @param array $entry Log entry
 * @return string Formatted HTML
 */
function format_log_entry($entry) {
    $type_colors = [
        'info' => '#3b82f6',
        'warning' => '#f59e0b',
        'error' => '#ef4444',
        'success' => '#22c55e',
    ];
    
    $color = $type_colors[$entry['type']] ?? '#6b7280';
    
    $user = $entry['user'] ? get_userdata($entry['user']) : null;
    $username = $user ? $user->display_name : 'System';
    
    return sprintf(
        '<div style="padding:8px 0;border-bottom:1px solid #f3f4f6;">
            <span style="color:#9ca3af;font-size:11px;">%s</span>
            <span style="color:%s;font-weight:500;margin-left:10px;">%s</span>
            <span style="color:#6b7280;margin-left:10px;">%s</span>
            <span style="color:#9ca3af;font-size:11px;float:right;">by %s</span>
        </div>',
        esc_html($entry['timestamp']),
        esc_attr($color),
        esc_html(ucfirst($entry['type'])),
        esc_html($entry['message']),
        esc_html($username)
    );
}
