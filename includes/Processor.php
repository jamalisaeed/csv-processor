<?php
namespace CSVProcessor;

class Processor {
    /**
     * Process state transient name
     */
    const STATE_TRANSIENT = 'csv_processor_state';

    /**
     * Number of rows to process per batch
     */
    const BATCH_SIZE = 50;

    private $batch_size = 50;

    public function set_batch_size($size) {
        $this->batch_size = max(1, intval($size));
    }

    /**
     * Process a CSV file
     *
     * @param string $file_path Path to the CSV file
     * @param int $start_row Starting row number
     * @return array Processing results
     */
    public function process_file($file_path, $start_row = 0) {
        if (!file_exists($file_path)) {
            throw new \Exception(__('File not found', 'csv-processor'));
        }

        $state = $this->get_state();
        $results = [
            'processed' => 0,
            'errors' => [],
            'completed' => false
        ];

        try {
            $handle = fopen($file_path, 'r');
            if ($handle === false) {
                throw new \Exception(__('Could not open file', 'csv-processor'));
            }

            // Skip header row
            if ($start_row === 0) {
                fgetcsv($handle);
            }

            // Skip to start row
            for ($i = 0; $i < $start_row; $i++) {
                fgetcsv($handle);
            }

            // Process rows in batches
            $row_count = 0;
            while (($data = fgetcsv($handle)) !== false && $row_count < $this->batch_size) {
                $row_index = $start_row + $row_count + 1; // +1 for 1-based index (excluding header)
                try {
                    $this->process_row($data, $row_index);
                    $results['processed']++;
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'row' => $row_index,
                        'message' => $e->getMessage()
                    ];
                }
                $row_count++;
            }

            // Check if we've reached the end of the file
            $next_row = fgetcsv($handle);
            if ($next_row === false) {
                $results['completed'] = true;
                // Set total_processed to total_rows for the final response
                $final_total = $state['total_rows'] ?? 0;
                $results['total_processed'] = $final_total;
                $results['total_rows'] = $final_total;
                // Update state one last time so AJAX gets the final numbers
                $this->update_state([
                    'file_path' => $file_path,
                    'current_row' => $final_total,
                    'total_processed' => $final_total,
                    'total_rows' => $final_total,
                    'batch_size' => $this->batch_size
                ]);
            } else {
                // Update state for next batch
                $this->update_state([
                    'file_path' => $file_path,
                    'current_row' => $start_row + $row_count,
                    'total_processed' => ($state['total_processed'] ?? 0) + $row_count,
                    'total_rows' => $state['total_rows'] ?? 0,
                    'batch_size' => $this->batch_size
                ]);
            }

            fclose($handle);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $results;
    }

    /**
     * Process a single row of CSV data
     *
     * @param array $data Row data
     * @param int $row_index Row index
     * @throws \Exception If processing fails
     */
    private function process_row($data, $row_index = null) {
        // TODO: Implement your custom row processing logic here
        // This is where you'll add your specific CSV processing requirements
        
        // Example validation
        if (empty($data)) {
            throw new \Exception(__('Empty row data', 'csv-processor'));
        }

        // Example processing
        // You can modify this method to implement your specific requirements
        do_action('csv_processor_process_row', $data, $row_index);
    }

    /**
     * Get current processing state
     *
     * @return array State data
     */
    public function get_state() {
        return get_transient(self::STATE_TRANSIENT) ?: [];
    }

    /**
     * Update processing state
     *
     * @param array $state New state data
     */
    public function update_state($state) {
        set_transient(self::STATE_TRANSIENT, $state, HOUR_IN_SECONDS);
    }

    /**
     * Clear processing state
     */
    public function clear_state() {
        delete_transient(self::STATE_TRANSIENT);
    }

    /**
     * Validate CSV file
     *
     * @param string $file_path Path to the CSV file
     * @return bool True if valid
     * @throws \Exception If validation fails
     */
    public function validate_file($file_path) {
        if (!file_exists($file_path)) {
            throw new \Exception(__('File not found', 'csv-processor'));
        }

        $handle = fopen($file_path, 'r');
        if ($handle === false) {
            throw new \Exception(__('Could not open file', 'csv-processor'));
        }

        // Check if file is readable
        if (!is_readable($file_path)) {
            throw new \Exception(__('File is not readable', 'csv-processor'));
        }

        // Check if file is empty
        if (filesize($file_path) === 0) {
            throw new \Exception(__('File is empty', 'csv-processor'));
        }

        // Check if file has valid CSV format
        $header = fgetcsv($handle);
        if ($header === false) {
            throw new \Exception(__('Invalid CSV format', 'csv-processor'));
        }

        // Count total rows (excluding header)
        $row_count = 0;
        while (fgetcsv($handle) !== false) {
            $row_count++;
        }
        fclose($handle);

        // Store total_rows in state
        $state = $this->get_state();
        $state['total_rows'] = $row_count;
        $this->update_state($state);

        return true;
    }
} 