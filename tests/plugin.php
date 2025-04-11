<?php
/**
 * Plugin Name: Test plugin
 * Description: Plugin description
 * Version: 1.0.0
 */
use tangible\updater;

function register() {
  updater\register([
    'name' => 'test-plugin',
    'file' => __FILE__,
  ]);
}
