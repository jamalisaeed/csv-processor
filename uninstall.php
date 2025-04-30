<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('csv_processor_version');

// Delete transients
delete_transient('csv_processor_state');

// Delete uploaded files
$upload_dir = wp_upload_dir();
$csv_files = glob($upload_dir['path'] . '/*.csv');

if ($csv_files) {
    foreach ($csv_files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
} 