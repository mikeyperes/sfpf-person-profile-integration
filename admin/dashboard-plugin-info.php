<?php
namespace sfpf_person_website;

/**
 * Plugin Info Dashboard Panel
 * Git version history, updates, and downloads
 * 
 * Based on working HWS Base Tools implementation
 */

defined('ABSPATH') || exit;

// Register AJAX handlers
add_action('wp_ajax_sfpf_download_plugin_zip', __NAMESPACE__ . '\\ajax_download_plugin_zip');
add_action('wp_ajax_sfpf_force_update_check', __NAMESPACE__ . '\\ajax_force_update_check');
add_action('wp_ajax_sfpf_direct_update_plugin', __NAMESPACE__ . '\\ajax_direct_update_plugin');
add_action('wp_ajax_sfpf_load_github_versions', __NAMESPACE__ . '\\ajax_load_github_versions');
add_action('wp_ajax_sfpf_download_specific_version', __NAMESPACE__ . '\\ajax_download_specific_version');

/**
 * AJAX: Load available versions (commits) from GitHub
 */
function ajax_load_github_versions() {
    if (!current_user_can('update_plugins')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    $github_repo = Config::$github_repo;
    
    // Fetch recent commits from GitHub API
    $commits_url = 'https://api.github.com/repos/' . $github_repo . '/commits?per_page=30';
    $response = wp_remote_get($commits_url, [
        'timeout' => 15,
        'headers' => [
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version'),
        ],
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error('Failed to fetch versions: ' . $response->get_error_message());
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $commits = json_decode($body, true);
    
    if (!is_array($commits)) {
        wp_send_json_error('Invalid response from GitHub');
        return;
    }
    
    // Format commits for dropdown
    $versions = [];
    foreach ($commits as $index => $commit) {
        if (!isset($commit['sha'], $commit['commit']['message'])) {
            continue;
        }
        
        $sha = $commit['sha'];
        $sha_short = substr($sha, 0, 7);
        $message = $commit['commit']['message'];
        $date = isset($commit['commit']['committer']['date']) 
            ? date('M j, Y', strtotime($commit['commit']['committer']['date']))
            : '';
        
        // Fetch actual plugin version from initialization.php at this commit
        $version_label = '';
        
        // Only fetch version for first 10 commits to avoid rate limits
        if ($index < 10) {
            $version_label = sfpf_get_version_from_commit($github_repo, $sha);
        }
        
        // Fallback: try to extract version from commit message
        if (!$version_label && preg_match('/v?(\d+\.\d+(?:\.\d+)?)/i', $message, $matches)) {
            $version_label = $matches[1];
        }
        
        // Get first line of commit message
        $message_first_line = strtok($message, "\n");
        if (strlen($message_first_line) > 40) {
            $message_first_line = substr($message_first_line, 0, 37) . '...';
        }
        
        // Build display name - include version if found
        if ($version_label) {
            $display_name = 'v' . $version_label . ' - ' . $message_first_line . ' (' . $date . ')';
        } else {
            $display_name = $sha_short . ' - ' . $message_first_line . ' (' . $date . ')';
        }
        
        $versions[] = [
            'name' => $display_name,
            'sha' => $sha,
            'version' => $version_label,
            'zip_url' => 'https://github.com/' . $github_repo . '/archive/' . $sha . '.zip',
        ];
    }
    
    wp_send_json_success([
        'versions' => $versions,
        'count' => count($versions),
    ]);
}

/**
 * Fetch plugin version from initialization.php at a specific commit
 */
function sfpf_get_version_from_commit($repo, $sha) {
    // Use GitHub raw content URL
    $raw_url = 'https://raw.githubusercontent.com/' . $repo . '/' . $sha . '/initialization.php';
    
    $response = wp_remote_get($raw_url, [
        'timeout' => 5,
        'headers' => [
            'User-Agent' => 'WordPress/' . get_bloginfo('version'),
        ],
    ]);
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return '';
    }
    
    $content = wp_remote_retrieve_body($response);
    
    // Try to extract version from plugin header: * Version: X.X.X
    if (preg_match('/\*\s*Version:\s*(\d+\.\d+(?:\.\d+)?)/i', $content, $matches)) {
        return $matches[1];
    }
    
    // Try constant: SFPF_PLUGIN_VERSION = 'X.X.X'
    if (preg_match('/SFPF_PLUGIN_VERSION[\'"\s,=]+[\'"](\d+\.\d+(?:\.\d+)?)[\'"]/', $content, $matches)) {
        return $matches[1];
    }
    
    return '';
}

/**
 * AJAX: Download a specific version from GitHub
 */
function ajax_download_specific_version() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    $version = isset($_POST['version']) ? sanitize_text_field($_POST['version']) : '';
    $sha = isset($_POST['sha']) ? sanitize_text_field($_POST['sha']) : '';
    
    if (empty($version)) {
        wp_send_json_error('No version specified');
        return;
    }
    
    $github_repo = Config::$github_repo;
    $correct_folder_name = Config::$plugin_folder_name;
    
    // Determine download URL
    if (!empty($sha)) {
        $download_url = 'https://github.com/' . $github_repo . '/archive/' . $sha . '.zip';
        if (preg_match('/v?(\d+\.\d+(?:\.\d+)?)/i', $version, $matches)) {
            $version_slug = 'v' . $matches[1];
        } else {
            $version_slug = substr($sha, 0, 7);
        }
    } else {
        $download_url = 'https://github.com/' . $github_repo . '/archive/refs/heads/main.zip';
        $version_slug = 'main';
    }
    
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/sfpf-temp-' . time();
    $temp_zip = $temp_dir . '/github-download.zip';
    $final_zip = $upload_dir['basedir'] . '/' . $correct_folder_name . '-' . $version_slug . '.zip';
    
    if (!wp_mkdir_p($temp_dir)) {
        wp_send_json_error('Could not create temp directory');
        return;
    }
    
    // Download from GitHub
    $response = wp_remote_get($download_url, [
        'timeout'  => 60,
        'stream'   => true,
        'filename' => $temp_zip,
    ]);
    
    if (is_wp_error($response)) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to download: ' . $response->get_error_message());
        return;
    }
    
    if (!file_exists($temp_zip)) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Download failed - file not created');
        return;
    }
    
    // Extract the zip
    $extract_dir = $temp_dir . '/extracted';
    wp_mkdir_p($extract_dir);
    
    $zip = new \ZipArchive();
    if ($zip->open($temp_zip) !== true) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to open downloaded zip');
        return;
    }
    
    $zip->extractTo($extract_dir);
    $zip->close();
    
    // Find extracted folder
    $extracted_folders = glob($extract_dir . '/*', GLOB_ONLYDIR);
    if (empty($extracted_folders)) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('No folder found in zip');
        return;
    }
    
    $wrong_folder = $extracted_folders[0];
    $correct_folder = $extract_dir . '/' . $correct_folder_name;
    
    // Rename folder
    if (basename($wrong_folder) !== $correct_folder_name) {
        rename($wrong_folder, $correct_folder);
    } else {
        $correct_folder = $wrong_folder;
    }
    
    // Create new zip with correct folder name
    $new_zip = new \ZipArchive();
    if ($new_zip->open($final_zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to create new zip');
        return;
    }
    
    sfpf_add_folder_to_zip($new_zip, $correct_folder, $correct_folder_name);
    $new_zip->close();
    
    // Cleanup
    sfpf_delete_directory($temp_dir);
    
    wp_send_json_success([
        'url' => $upload_dir['baseurl'] . '/' . basename($final_zip),
        'filename' => basename($final_zip),
    ]);
}

/**
 * AJAX: Force update check
 */
function ajax_force_update_check() {
    if (!current_user_can('update_plugins')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    // Clear transients
    $transient_key = 'sfpf_github_ver_' . md5(Config::$github_repo . Config::$github_branch);
    delete_site_transient($transient_key);
    delete_site_transient('update_plugins');
    
    // Fetch new version
    $new_version = sfpf_get_github_version(Config::$github_repo, Config::$github_branch);
    
    if ($new_version) {
        wp_send_json_success(['new_version' => $new_version]);
    } else {
        wp_send_json_error('Could not fetch version from GitHub');
    }
}

/**
 * AJAX: Direct update from GitHub
 */
function ajax_direct_update_plugin() {
    if (!current_user_can('update_plugins')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    $github_repo = Config::$github_repo;
    $github_branch = Config::$github_branch;
    $correct_folder_name = Config::$plugin_folder_name;
    
    $download_url = 'https://github.com/' . $github_repo . '/archive/refs/heads/' . $github_branch . '.zip';
    
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/sfpf-update-' . time();
    $temp_zip = $temp_dir . '/github-download.zip';
    
    if (!wp_mkdir_p($temp_dir)) {
        wp_send_json_error('Could not create temp directory');
        return;
    }
    
    // Download
    $response = wp_remote_get($download_url, [
        'timeout'  => 60,
        'stream'   => true,
        'filename' => $temp_zip,
    ]);
    
    if (is_wp_error($response) || !file_exists($temp_zip)) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to download from GitHub');
        return;
    }
    
    // Extract
    $extract_dir = $temp_dir . '/extracted';
    wp_mkdir_p($extract_dir);
    
    $zip = new \ZipArchive();
    if ($zip->open($temp_zip) !== true) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to open zip');
        return;
    }
    
    $zip->extractTo($extract_dir);
    $zip->close();
    
    // Find extracted folder
    $extracted_folders = glob($extract_dir . '/*', GLOB_ONLYDIR);
    if (empty($extracted_folders)) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('No folder found');
        return;
    }
    
    $source_folder = $extracted_folders[0];
    $plugin_dir = WP_PLUGIN_DIR . '/' . $correct_folder_name;
    
    // Backup and replace
    if (is_dir($plugin_dir)) {
        $backup_dir = $plugin_dir . '-backup-' . time();
        rename($plugin_dir, $backup_dir);
    }
    
    // Move new version into place
    if (!rename($source_folder, $plugin_dir)) {
        // Restore backup
        if (isset($backup_dir) && is_dir($backup_dir)) {
            rename($backup_dir, $plugin_dir);
        }
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to install update');
        return;
    }
    
    // Cleanup
    if (isset($backup_dir) && is_dir($backup_dir)) {
        sfpf_delete_directory($backup_dir);
    }
    sfpf_delete_directory($temp_dir);
    
    // Clear caches
    delete_site_transient('update_plugins');
    
    wp_send_json_success([
        'message' => 'Plugin updated successfully!',
        'reload' => true,
    ]);
}

/**
 * AJAX: Download plugin as ZIP
 */
function ajax_download_plugin_zip() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    $github_repo = Config::$github_repo;
    $github_branch = Config::$github_branch;
    $correct_folder_name = Config::$plugin_folder_name;
    
    $download_url = 'https://github.com/' . $github_repo . '/archive/refs/heads/' . $github_branch . '.zip';
    
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/sfpf-temp-' . time();
    $temp_zip = $temp_dir . '/github-download.zip';
    $final_zip = $upload_dir['basedir'] . '/' . $correct_folder_name . '.zip';
    
    if (!wp_mkdir_p($temp_dir)) {
        wp_send_json_error('Could not create temp directory');
        return;
    }
    
    // Download
    $response = wp_remote_get($download_url, [
        'timeout'  => 60,
        'stream'   => true,
        'filename' => $temp_zip,
    ]);
    
    if (is_wp_error($response) || !file_exists($temp_zip)) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to download from GitHub');
        return;
    }
    
    // Extract
    $extract_dir = $temp_dir . '/extracted';
    wp_mkdir_p($extract_dir);
    
    $zip = new \ZipArchive();
    if ($zip->open($temp_zip) !== true) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to open zip');
        return;
    }
    
    $zip->extractTo($extract_dir);
    $zip->close();
    
    // Find and rename folder
    $extracted_folders = glob($extract_dir . '/*', GLOB_ONLYDIR);
    if (empty($extracted_folders)) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('No folder found');
        return;
    }
    
    $wrong_folder = $extracted_folders[0];
    $correct_folder = $extract_dir . '/' . $correct_folder_name;
    
    if (basename($wrong_folder) !== $correct_folder_name) {
        rename($wrong_folder, $correct_folder);
    } else {
        $correct_folder = $wrong_folder;
    }
    
    // Create new zip
    $new_zip = new \ZipArchive();
    if ($new_zip->open($final_zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        sfpf_delete_directory($temp_dir);
        wp_send_json_error('Failed to create zip');
        return;
    }
    
    sfpf_add_folder_to_zip($new_zip, $correct_folder, $correct_folder_name);
    $new_zip->close();
    
    // Cleanup
    sfpf_delete_directory($temp_dir);
    
    wp_send_json_success([
        'url' => $upload_dir['baseurl'] . '/' . basename($final_zip),
    ]);
}

/**
 * Helper: Add folder to zip recursively
 */
function sfpf_add_folder_to_zip($zip, $folder, $base_path) {
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($folder),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $file_path = $file->getRealPath();
            $relative_path = $base_path . '/' . substr($file_path, strlen($folder) + 1);
            $zip->addFile($file_path, $relative_path);
        }
    }
}

/**
 * Helper: Delete directory recursively
 */
function sfpf_delete_directory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? sfpf_delete_directory($path) : unlink($path);
    }
    rmdir($dir);
}

/**
 * Get latest version from GitHub
 */
function sfpf_get_github_version($repo, $branch = 'main') {
    $transient_key = 'sfpf_github_ver_' . md5($repo . $branch);
    $cached = get_site_transient($transient_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $url = 'https://raw.githubusercontent.com/' . $repo . '/' . $branch . '/initialization.php';
    
    $response = wp_remote_get($url, [
        'timeout' => 10,
        'sslverify' => true,
    ]);
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    if (preg_match('/^[\s\*]*Version:\s*(.+)$/mi', $body, $matches)) {
        $version = trim($matches[1]);
        set_site_transient($transient_key, $version, 30 * MINUTE_IN_SECONDS);
        return $version;
    }
    
    return false;
}

/**
 * Display plugin info panel
 */
function sfpf_display_plugin_info() {
    $plugin_file = SFPF_PLUGIN_DIR . 'initialization.php';
    $plugin_data = get_plugin_data($plugin_file);
    
    $github_repo = Config::$github_repo;
    $github_branch = Config::$github_branch;
    $new_version = sfpf_get_github_version($github_repo, $github_branch) ?: 'Checking...';
    
    $update_available = $new_version !== 'Checking...' && version_compare($new_version, $plugin_data['Version'], '>');
    
    preg_match('/href=["\']([^"\']+)["\']/', $plugin_data['Author'], $matches);
    $author_url = $matches[1] ?? '#';
    $author_name = strip_tags($plugin_data['Author']);
    ?>
    
    <div class="sfpf-card">
        <div class="sfpf-card-header">
            <span class="dashicons dashicons-info" style="color:#2563eb;"></span>
            <h3><?php echo esc_html($plugin_data['Name']); ?> - Plugin Info</h3>
        </div>
        
        <div style="margin-bottom:15px;">
            <strong>Plugin Name:</strong> <?php echo esc_html($plugin_data['Name']); ?>
        </div>
        <div style="margin-bottom:15px;">
            <strong>Plugin Slug:</strong> <?php echo esc_html(Config::$plugin_folder_name); ?>
        </div>
        
        <!-- Version Info -->
        <div style="margin-bottom:15px;padding:15px;background:<?php echo $update_available ? '#fef2f2' : '#f0fdf4'; ?>;border:1px solid <?php echo $update_available ? '#dc2626' : '#16a34a'; ?>;border-radius:6px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <strong>Current Version:</strong> 
                    <span style="font-size:16px;font-weight:bold;"><?php echo esc_html($plugin_data['Version']); ?></span>
                </div>
                <div>
                    <strong>Latest Version:</strong> 
                    <span id="sfpf-latest-version" style="font-size:16px;font-weight:bold;color:<?php echo $update_available ? '#dc2626' : '#16a34a'; ?>;">
                        <?php echo esc_html($new_version); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($update_available): ?>
            <p style="margin:10px 0 0;color:#dc2626;font-weight:bold;">
                ⚠️ Update available! v<?php echo esc_html($plugin_data['Version']); ?> → v<?php echo esc_html($new_version); ?>
            </p>
            <?php else: ?>
            <p style="margin:10px 0 0;color:#16a34a;">
                ✅ You are running the latest version
            </p>
            <?php endif; ?>
        </div>
        
        <!-- Update Actions -->
        <div style="margin-bottom:20px;padding:15px;background:#f0f6fc;border:1px solid #c3c4c7;border-radius:6px;">
            <strong>🔄 Update Actions</strong>
            <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="button" id="sfpf-force-update-check" class="button button-secondary">
                    🔍 Force Update Check
                </button>
                <button type="button" id="sfpf-direct-update" class="button button-primary" <?php echo $update_available ? '' : 'disabled'; ?>>
                    ⬆️ Update Now from GitHub
                </button>
                <a href="<?php echo admin_url('update-core.php?force-check=1'); ?>" class="button button-secondary" target="_blank">
                    📋 WP Update Page
                </a>
            </div>
            <div id="sfpf-update-status" style="margin-top:10px;"></div>
            <p style="font-size:11px;color:#666;margin:10px 0 0;">
                <strong>Force Update Check:</strong> Clears all caches and checks GitHub for new version.<br>
                <strong>Update Now:</strong> Directly downloads from GitHub and installs (folder name handled correctly).
            </p>
        </div>
        
        <!-- Download Plugin -->
        <div style="margin-bottom:15px;padding:15px;background:#f9f9f9;border-radius:4px;">
            <strong>📦 Download Plugin ZIP:</strong>
            <p style="font-size:12px;color:#666;margin:5px 0 10px;">
                Downloads from GitHub with correct folder name (no -main suffix).
            </p>
            <button type="button" id="sfpf-download-plugin-zip" class="button button-secondary" data-folder="<?php echo esc_attr(Config::$plugin_folder_name); ?>">
                ⬇️ Download <?php echo esc_html(Config::$plugin_folder_name); ?>.zip
            </button>
            <span id="sfpf-download-status" style="margin-left:10px;"></span>
        </div>
        
        <!-- Version History -->
        <div style="margin-bottom:15px;padding:15px;background:#fff8e5;border:1px solid #dba617;border-radius:6px;">
            <strong>📜 Version History (Download Older Versions)</strong>
            <p style="font-size:12px;color:#666;margin:5px 0 10px;">
                Select a version tag from GitHub to download. Useful for rollbacks.
            </p>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <select id="sfpf-version-select" style="min-width:200px;">
                    <option value="">-- Click "Load Versions" --</option>
                </select>
                <button type="button" id="sfpf-load-versions" class="button button-secondary">
                    🔄 Load Versions
                </button>
                <button type="button" id="sfpf-download-version" class="button button-secondary" disabled>
                    ⬇️ Download Selected Version
                </button>
            </div>
            <div id="sfpf-version-status" style="margin-top:10px;"></div>
        </div>
        
        <!-- Upload Local ZIP -->
        <div style="margin-bottom:15px;padding:15px;background:#f0fdf4;border:1px solid #16a34a;border-radius:6px;">
            <strong>📤 Upload Local Plugin ZIP</strong>
            <p style="font-size:12px;color:#666;margin:5px 0 10px;">
                Upload a local ZIP file to update the plugin. The folder name will be corrected automatically.
            </p>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="file" id="sfpf-local-zip-file" accept=".zip" style="flex:1;max-width:300px;">
                <button type="button" id="sfpf-upload-local-zip" class="button button-primary">
                    📤 Upload & Install
                </button>
            </div>
            <div id="sfpf-upload-status" style="margin-top:10px;"></div>
        </div>
        
        <div style="margin-bottom:15px;">
            <strong>GitHub URL:</strong> <a href="https://github.com/<?php echo esc_attr($github_repo); ?>" target="_blank">https://github.com/<?php echo esc_html($github_repo); ?></a>
        </div>
        <div style="margin-bottom:15px;">
            <strong>Author:</strong> 
            <a href="<?php echo esc_url($author_url); ?>" target="_blank"><?php echo esc_html($author_name); ?></a>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var versionData = {};
        
        // Force Update Check
        $('#sfpf-force-update-check').on('click', function() {
            var $btn = $(this);
            var $status = $('#sfpf-update-status');
            
            $btn.prop('disabled', true).text('🔄 Checking...');
            $status.html('<span style="color:#666;">Clearing caches and checking GitHub...</span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: { action: 'sfpf_force_update_check' },
                success: function(response) {
                    if (response.success) {
                        $('#sfpf-latest-version').text(response.data.new_version);
                        $status.html('<span style="color:green;">✅ Check complete. Latest version: ' + response.data.new_version + '</span>');
                        
                        var currentVer = '<?php echo esc_js($plugin_data['Version']); ?>';
                        if (response.data.new_version && response.data.new_version !== currentVer) {
                            $('#sfpf-direct-update').prop('disabled', false);
                            $status.append(' <strong style="color:#d63638;">- Update available!</strong>');
                        }
                    } else {
                        $status.html('<span style="color:red;">❌ ' + response.data + '</span>');
                    }
                    $btn.prop('disabled', false).text('🔍 Force Update Check');
                },
                error: function() {
                    $status.html('<span style="color:red;">❌ AJAX Error</span>');
                    $btn.prop('disabled', false).text('🔍 Force Update Check');
                }
            });
        });
        
        // Direct Update
        $('#sfpf-direct-update').on('click', function() {
            if (!confirm('This will download the latest version from GitHub and update the plugin. Continue?')) {
                return;
            }
            
            var $btn = $(this);
            var $status = $('#sfpf-update-status');
            
            $btn.prop('disabled', true).text('⏳ Downloading & Installing...');
            $status.html('<span style="color:#666;">Downloading from GitHub...</span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                timeout: 120000,
                data: { action: 'sfpf_direct_update_plugin' },
                success: function(response) {
                    if (response.success) {
                        $status.html('<span style="color:green;">✅ ' + response.data.message + '</span>');
                        if (response.data.reload) {
                            $status.append('<br><span style="color:#666;">Reloading page in 2 seconds...</span>');
                            setTimeout(function() { location.reload(); }, 2000);
                        }
                    } else {
                        $status.html('<span style="color:red;">❌ ' + response.data + '</span>');
                        $btn.prop('disabled', false).text('⬆️ Update Now from GitHub');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color:red;">❌ Error: ' + error + '</span>');
                    $btn.prop('disabled', false).text('⬆️ Update Now from GitHub');
                }
            });
        });
        
        // Download ZIP
        $('#sfpf-download-plugin-zip').on('click', function() {
            var $btn = $(this);
            var $status = $('#sfpf-download-status');
            var folderName = $btn.data('folder');
            
            $btn.prop('disabled', true).text('⏳ Preparing...');
            $status.text('');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: { action: 'sfpf_download_plugin_zip' },
                success: function(response) {
                    if (response.success) {
                        $status.html('<a href="' + response.data.url + '" target="_blank" style="color:green;">✅ Download Ready</a>');
                        window.location.href = response.data.url;
                    } else {
                        $status.html('<span style="color:red;">❌ ' + response.data + '</span>');
                    }
                    $btn.prop('disabled', false).text('⬇️ Download ' + folderName + '.zip');
                },
                error: function() {
                    $status.html('<span style="color:red;">❌ AJAX Error</span>');
                    $btn.prop('disabled', false).text('⬇️ Download ' + folderName + '.zip');
                }
            });
        });
        
        // Load Versions
        $('#sfpf-load-versions').on('click', function() {
            var $btn = $(this);
            var $select = $('#sfpf-version-select');
            var $status = $('#sfpf-version-status');
            
            $btn.prop('disabled', true).text('🔄 Loading...');
            $status.text('');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: { action: 'sfpf_load_github_versions' },
                success: function(response) {
                    if (response.success) {
                        $select.empty();
                        versionData = {};
                        $select.append('<option value="">-- Select Version (' + response.data.count + ' commits) --</option>');
                        $.each(response.data.versions, function(i, ver) {
                            versionData[ver.name] = ver.sha;
                            $select.append('<option value="' + ver.name + '">' + ver.name + '</option>');
                        });
                        $status.html('<span style="color:green;">✅ Loaded ' + response.data.count + ' commits</span>');
                        $('#sfpf-download-version').prop('disabled', false);
                    } else {
                        $status.html('<span style="color:red;">❌ ' + response.data + '</span>');
                    }
                    $btn.prop('disabled', false).text('🔄 Load Versions');
                },
                error: function() {
                    $status.html('<span style="color:red;">❌ AJAX Error</span>');
                    $btn.prop('disabled', false).text('🔄 Load Versions');
                }
            });
        });
        
        // Download Selected Version
        $('#sfpf-download-version').on('click', function() {
            var $btn = $(this);
            var $select = $('#sfpf-version-select');
            var $status = $('#sfpf-version-status');
            var version = $select.val();
            var sha = versionData[version] || '';
            
            if (!version) {
                $status.html('<span style="color:orange;">⚠️ Please select a version first</span>');
                return;
            }
            
            $btn.prop('disabled', true).text('⏳ Preparing...');
            $status.html('<span style="color:#666;">Downloading from GitHub...</span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                timeout: 60000,
                data: { 
                    action: 'sfpf_download_specific_version',
                    version: version,
                    sha: sha
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<a href="' + response.data.url + '" target="_blank" style="color:green;">✅ ' + response.data.filename + ' ready - Click to download</a>');
                        window.location.href = response.data.url;
                    } else {
                        $status.html('<span style="color:red;">❌ ' + response.data + '</span>');
                    }
                    $btn.prop('disabled', false).text('⬇️ Download Selected Version');
                },
                error: function() {
                    $status.html('<span style="color:red;">❌ AJAX Error</span>');
                    $btn.prop('disabled', false).text('⬇️ Download Selected Version');
                }
            });
        });
    });
    </script>
    <?php
}
