jQuery(document).ready(function($) {
    const form = $('#csv-processor-form');
    const progressSection = $('.csv-processor-progress-section');
    const progressBar = $('.csv-processor-progress-bar-inner');
    const statusText = $('.csv-processor-status-text');
    const statusPercentage = $('.csv-processor-status-percentage');
    const resultsSection = $('.csv-processor-results');
    const resultsContent = $('.csv-processor-results-content');
    const pauseButton = $('.csv-processor-pause');
    const resumeButton = $('.csv-processor-resume');

    let isProcessing = false;
    let isPaused = false;
    let processingInterval;

    // Handle form submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'csv_processor_upload');
        formData.append('nonce', csvProcessor.nonce);

        // Add batch size
        const batchSize = $('#batch_size').val();
        formData.append('batch_size', batchSize);

        // Show progress section
        progressSection.show();
        statusText.text(csvProcessor.i18n.uploading);
        progressBar.css('width', '0%');
        statusPercentage.text('0%');

        // Upload file
        $.ajax({
            url: csvProcessor.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    startProcessing();
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError(csvProcessor.i18n.uploadError);
            }
        });
    });

    // Start processing
    function startProcessing() {
        isProcessing = true;
        isPaused = false;
        statusText.text(csvProcessor.i18n.processing);
        pauseButton.show();
        resumeButton.hide();

        processingInterval = setInterval(processNextBatch, 1000);
    }

    // Process next batch
    function processNextBatch() {
        if (isPaused) return;

        $.ajax({
            url: csvProcessor.ajaxUrl,
            type: 'POST',
            data: {
                action: 'csv_processor_process',
                nonce: csvProcessor.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateProgress(response.data);
                    
                    if (response.data.results.completed) {
                        completeProcessing(response.data);
                    }
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError(csvProcessor.i18n.processError);
            }
        });
    }

    // Update progress
    function updateProgress(data) {
        const state = data.state;
        const results = data.results;
        
        if (state && state.total_processed !== undefined && state.total_rows) {
            let percentage = Math.round((state.total_processed / state.total_rows) * 100);
            if (results.completed) {
                percentage = 100;
            } else if (percentage > 99) {
                percentage = 99; // Don't show 100% until complete
            }
            progressBar.css('width', percentage + '%');
            statusPercentage.text(percentage + '%');
        }

        // Update results section
        if (results.errors.length > 0) {
            showErrors(results.errors);
        }
    }

    // Complete processing
    function completeProcessing(data) {
        clearInterval(processingInterval);
        isProcessing = false;
        statusText.text(csvProcessor.i18n.complete);
        pauseButton.hide();
        resumeButton.hide();
        
        // Set progress bar to 100% for sure
        progressBar.css('width', '100%');
        statusPercentage.text('100%');

        // Show final results
        showResults(data.results);
    }

    // Show errors
    function showErrors(errors) {
        let errorHtml = '<div class="csv-processor-errors">';
        errorHtml += '<h3>' + csvProcessor.i18n.error + '</h3>';
        errorHtml += '<ul>';
        
        errors.forEach(function(error) {
            errorHtml += '<li>' + 
                'Row ' + error.row + ': ' + 
                error.message + 
                '</li>';
        });
        
        errorHtml += '</ul></div>';
        resultsContent.append(errorHtml);
        resultsSection.show();
    }

    // Show results
    function showResults(results) {
        let resultsHtml = '<div class="csv-processor-summary">';
        resultsHtml += '<p>' +
            'Processed ' + (results.total_processed || results.processed) + ' rows successfully' +
            '</p>';
        resultsHtml += '</div>';
        
        resultsContent.prepend(resultsHtml);
        resultsSection.show();
    }

    // Show error
    function showError(message) {
        clearInterval(processingInterval);
        isProcessing = false;
        statusText.text(csvProcessor.i18n.error + ': ' + message);
        pauseButton.hide();
        resumeButton.hide();
    }

    // Handle pause button
    pauseButton.on('click', function() {
        isPaused = true;
        $(this).hide();
        resumeButton.show();
        statusText.text(csvProcessor.i18n.paused);
    });

    // Handle resume button
    resumeButton.on('click', function() {
        isPaused = false;
        $(this).hide();
        pauseButton.show();
        statusText.text(csvProcessor.i18n.processing);
    });
}); 