<?php
namespace CSVProcessor;

class AJAX_Handler {
    /**
     * Processor instance
     *
     * @var Processor
     */
    private $processor;

    /**
     * Constructor
     *
     * @param Processor $processor Processor instance
     */
    public function __construct(Processor $processor) {
        $this->processor = $processor;
    }

    /**
     * Handle file upload
     */
    public function handle_upload() {
        try {
            $this->verify_nonce();
            $this->verify_capabilities();

            if (!isset($_FILES['csv_file'])) {
                throw new \Exception(__('No file uploaded', 'csv-processor'));
            }

            $file = $_FILES['csv_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception($this->get_upload_error_message($file['error']));
            }

            // Validate file type
            $file_type = wp_check_filetype($file['name']);
            if ($file_type['ext'] !== 'csv') {
                throw new \Exception(__('Invalid file type. Please upload a CSV file.', 'csv-processor'));
            }

            // Move file to uploads directory
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['path'] . '/' . $file['name'];
            
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                throw new \Exception(__('Failed to move uploaded file', 'csv-processor'));
            }

            // Validate file
            $this->processor->validate_file($file_path);

            $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 50;
            $this->processor->set_batch_size($batch_size);

            $state = $this->processor->get_state();
            $state['batch_size'] = $batch_size;
            $this->processor->update_state($state);

            // Start processing
            $results = $this->processor->process_file($file_path);

            wp_send_json_success([
                'message' => __('File uploaded and processing started', 'csv-processor'),
                'results' => $results,
                'state' => $this->processor->get_state()
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle processing request
     */
    public function handle_process() {
        try {
            $this->verify_nonce();
            $this->verify_capabilities();

            $state = $this->processor->get_state();
            if (empty($state['file_path'])) {
                throw new \Exception(__('No file to process', 'csv-processor'));
            }

            if (isset($state['batch_size'])) {
                $this->processor->set_batch_size($state['batch_size']);
            }

            $results = $this->processor->process_file(
                $state['file_path'],
                $state['current_row'] ?? 0
            );

            wp_send_json_success([
                'results' => $results,
                'state' => $this->processor->get_state()
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle status request
     */
    public function handle_get_status() {
        try {
            $this->verify_nonce();
            $this->verify_capabilities();

            $state = $this->processor->get_state();
            wp_send_json_success([
                'state' => $state
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verify nonce
     *
     * @throws \Exception If nonce verification fails
     */
    private function verify_nonce() {
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'csv_processor_nonce')) {
            throw new \Exception(__('Security check failed', 'csv-processor'));
        }
    }

    /**
     * Verify user capabilities
     *
     * @throws \Exception If user lacks required capabilities
     */
    private function verify_capabilities() {
        if (!current_user_can('manage_options')) {
            throw new \Exception(__('You do not have permission to perform this action', 'csv-processor'));
        }
    }

    /**
     * Get upload error message
     *
     * @param int $error_code PHP upload error code
     * @return string Error message
     */
    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'csv-processor');
            case UPLOAD_ERR_FORM_SIZE:
                return __('The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form', 'csv-processor');
            case UPLOAD_ERR_PARTIAL:
                return __('The uploaded file was only partially uploaded', 'csv-processor');
            case UPLOAD_ERR_NO_FILE:
                return __('No file was uploaded', 'csv-processor');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('Missing a temporary folder', 'csv-processor');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Failed to write file to disk', 'csv-processor');
            case UPLOAD_ERR_EXTENSION:
                return __('A PHP extension stopped the file upload', 'csv-processor');
            default:
                return __('Unknown upload error', 'csv-processor');
        }
    }
} 