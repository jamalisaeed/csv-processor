<?php
namespace CSVProcessor;

class Admin {
    /**
     * Add menu page to WordPress admin
     */
    public function add_menu_page() {
        add_menu_page(
            __('CSV Processor', 'csv-processor'),
            __('CSV Processor', 'csv-processor'),
            'manage_options',
            'csv-processor',
            [$this, 'render_admin_page'],
            'dashicons-media-spreadsheet',
            30
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ('toplevel_page_csv-processor' !== $hook) {
            return;
        }

        // Enqueue admin styles
        wp_enqueue_style(
            'csv-processor-admin',
            CSV_PROCESSOR_PLUGIN_URL . 'admin/css/csv-processor-admin.css',
            [],
            CSV_PROCESSOR_VERSION
        );

        // Enqueue admin scripts
        wp_enqueue_script(
            'csv-processor-admin',
            CSV_PROCESSOR_PLUGIN_URL . 'admin/js/csv-processor-admin.js',
            ['jquery'],
            CSV_PROCESSOR_VERSION,
            true
        );

        // Localize script
        wp_localize_script('csv-processor-admin', 'csvProcessor', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('csv_processor_nonce'),
            'i18n' => [
                'uploading' => __('Uploading...', 'csv-processor'),
                'processing' => __('Processing...', 'csv-processor'),
                'complete' => __('Complete!', 'csv-processor'),
                'error' => __('Error:', 'csv-processor'),
                'uploadError' => __('Error uploading file', 'csv-processor'),
                'processError' => __('Error processing file', 'csv-processor'),
            ]
        ]);
    }

    /**
     * Render the admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('CSV Processor', 'csv-processor'); ?></h1>
            
            <div class="csv-processor-container">
                <div class="csv-processor-upload-section">
                    <h2><?php echo esc_html__('Upload CSV File', 'csv-processor'); ?></h2>
                    <form id="csv-processor-form" enctype="multipart/form-data">
                        <div class="csv-processor-file-upload">
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <input type="number" name="batch_size" id="batch_size" min="1" value="50" style="width:100px;" required>
                            <label for="batch_size" style="margin-left:5px;">Batch Size</label>
                            <button type="submit" class="button button-primary">
                                <?php echo esc_html__('Upload and Process', 'csv-processor'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="csv-processor-progress-section" style="display: none;">
                    <h2><?php echo esc_html__('Processing Status', 'csv-processor'); ?></h2>
                    <div class="csv-processor-progress-bar">
                        <div class="csv-processor-progress-bar-inner"></div>
                    </div>
                    <div class="csv-processor-status">
                        <span class="csv-processor-status-text"></span>
                        <span class="csv-processor-status-percentage"></span>
                    </div>
                    <div class="csv-processor-actions">
                        <button type="button" class="button csv-processor-pause" style="display: none;">
                            <?php echo esc_html__('Pause', 'csv-processor'); ?>
                        </button>
                        <button type="button" class="button csv-processor-resume" style="display: none;">
                            <?php echo esc_html__('Resume', 'csv-processor'); ?>
                        </button>
                    </div>
                </div>

                <div class="csv-processor-results" style="display: none;">
                    <h2><?php echo esc_html__('Processing Results', 'csv-processor'); ?></h2>
                    <div class="csv-processor-results-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
} 