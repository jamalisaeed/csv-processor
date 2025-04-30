<?php
namespace CSVProcessor;

class CSV_Processor {
    /**
     * Admin interface instance
     *
     * @var Admin
     */
    private $admin;

    /**
     * Processor instance
     *
     * @var Processor
     */
    private $processor;

    /**
     * AJAX handler instance
     *
     * @var AJAX_Handler
     */
    private $ajax_handler;

    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize components
        $this->admin = new Admin();
        $this->processor = new Processor();
        $this->ajax_handler = new AJAX_Handler($this->processor);

        // Hook into WordPress
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', [$this->admin, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this->admin, 'enqueue_scripts']);

        // AJAX hooks
        add_action('wp_ajax_csv_processor_upload', [$this->ajax_handler, 'handle_upload']);
        add_action('wp_ajax_csv_processor_process', [$this->ajax_handler, 'handle_process']);
        add_action('wp_ajax_csv_processor_get_status', [$this->ajax_handler, 'handle_get_status']);
    }
} 