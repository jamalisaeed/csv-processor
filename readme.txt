=== CSV Processor ===
Contributors: yourname
Tags: csv, import, processing, batch processing
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A professional WordPress plugin for processing CSV files with progress tracking and AJAX support.

== Description ==

CSV Processor is a powerful WordPress plugin that allows you to process large CSV files efficiently. It provides a user-friendly interface for uploading and processing CSV files with the following features:

* Clean and intuitive admin interface
* Progress tracking with visual feedback
* Batch processing to prevent memory issues
* Pause/Resume functionality
* Error handling and reporting
* Responsive design
* State management for interrupted processes

== Installation ==

1. Upload the `csv-processor` folder to the `/wp-content/plugins/` directory
2. Navigate to the plugin directory in your terminal
3. Run the installation script:
   ```bash
   ./install.sh
   ```
   This will install Composer dependencies and set up the plugin.
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Go to the 'CSV Processor' menu in your WordPress admin panel
6. Upload and process your CSV files

== Requirements ==

* PHP 7.4 or higher
* Composer (for installation)
* WordPress 5.0 or higher

== Frequently Asked Questions ==

= How large CSV files can I process? =

The plugin is designed to handle large files by processing them in batches. The default batch size is 50 rows, but you can modify this in the code if needed.

= What happens if the process is interrupted? =

The plugin saves the processing state and allows you to resume from where it left off. You can also manually pause and resume the process.

= How do I customize the CSV processing? =

You can hook into the `csv_processor_process_row` action to implement your custom processing logic. See the documentation for more details.

== Changelog ==

= 1.0.0 =
* Initial release
* Added PSR-4 autoloading support
* Added Composer integration

== Upgrade Notice ==

= 1.0.0 =
Initial release with PSR-4 autoloading support

== Customization ==

To customize the CSV processing logic, you can hook into the `csv_processor_process_row` action. Here's an example:

```php
add_action('csv_processor_process_row', function($data) {
    // Your custom processing logic here
    // $data contains the current row's data as an array
});
```

== Security ==

The plugin implements several security measures:

* Nonce verification for all AJAX requests
* Capability checks for admin actions
* File type validation
* Secure file handling
* Input sanitization

== Support ==

For support, please visit [your support URL] or email [your support email]. 