<?php
/**
 * Plugin Name: Test plugin
 * Description: Plugin description
 * Version: 1.0.0
 */
use tangible\updater;

add_action('plugins_loaded', function() {
  updater\register([
    'name' => 'test-plugin',
    'file' => __FILE__,

    'cloud_id'       => 123,
    'updater_url'    => 'http://localhost:4004/update',    // Update server URL (Optional)
    'activation_url' => 'http://localhost:4004/activate',  // License activation URL (Optional)
  ]);
});
