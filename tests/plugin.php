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
  ]);
});
