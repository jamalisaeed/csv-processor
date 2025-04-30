<?php
/**
 * Plugin Name: CSV Processor
 * Plugin URI: https://yourwebsite.com/csv-processor
 * Description: A professional WordPress plugin for processing CSV files with progress tracking and AJAX support.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: csv-processor
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('CSV_PROCESSOR_VERSION', '1.0.0');
define('CSV_PROCESSOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CSV_PROCESSOR_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CSV_PROCESSOR_PLUGIN_DIR . 'vendor/autoload.php';


// Initialize the plugin
function csv_processor_init() {
    // Load text domain for internationalization
    load_plugin_textdomain('csv-processor', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize main plugin class
    $plugin = new CSVProcessor\CSV_Processor();
    $plugin->init();
}
add_action('plugins_loaded', 'csv_processor_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create necessary database tables or options
    add_option('csv_processor_version', CSV_PROCESSOR_VERSION);
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up transients
    delete_transient('csv_processor_state');
}); 