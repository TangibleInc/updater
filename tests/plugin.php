<?php
/**
 * Plugin Name: Test plugin
 * Description: Plugin description
 * Version: 1.0.0
 */
use tangible\framework;
use tangible\updater;

add_action('plugins_loaded', function() {

  $plugin = framework\register_plugin([
    'name' => 'test-plugin',
    'title'          => 'Test Plugin',
    'setting_prefix' => 'test_plugin',

    'version'        => '1.0.0',
    'file_path'      => __FILE__,
    'base_path'      => plugin_basename( __FILE__ ),
    'dir_path'       => plugin_dir_path( __FILE__ ),
    'url'            => plugins_url( '/', __FILE__ ),
    'assets_url'     => plugins_url( '/assets', __FILE__ ),

    'cloud_id'       => 123,
    'updater_url'    => 'http://localhost:4004/update',    // Update server URL (Optional)
    'activation_url' => 'http://localhost:4004/activate',  // License activation URL (Optional)
  ]);

  framework\register_plugin_settings($plugin, [
    'tabs' => [
      'license' => [
        'title' => 'License',
        'callback' => function($plugin) {
          updater\render_license_page($plugin);
        }
      ],
    ],
  ]);

  updater\register_plugin($plugin);
});
