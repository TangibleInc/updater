<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

function register_plugin($plugin) {

  if ( ! class_exists( 'Puc_v4_Factory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
  }

  if (is_array($plugin)) {
    $name = $plugin['name'];
    $file = $plugin['file'];
    $plugin = (object) $plugin;
  } else {
    $name = $plugin->name;
    $file = $plugin->file_path;
  }

  if ( empty( $name ) || empty( $file ) ) {
    trigger_error( 'Updater needs name and file', E_USER_WARNING );
    return;
  }

  $updater = updater::$instance;
  if (empty($updater->server_url)) return;

  $server_url = $plugin->api ?? $updater->server_url;

  $query = [
    'action' => 'get_metadata',
    'slug' => $name,
  ];

  if (isset($plugin->cloud_id)) {
    $query['pluginId'] = $plugin->cloud_id;
    $query['license'] = $plugin->license ?? updater\get_license_key($name);
    $query['url'] = site_url();
  }

  $url = $server_url . '?' . http_build_query($query);

  $update_checker = \Puc_v4_Factory::buildUpdateChecker(
    $url, $file, $name
  );

  $updater->update_checkers[ $name ] = $update_checker;

  // Add a link "Check for updates" in the admin plugins list
  add_filter('puc_manual_check_link-' . $name, function( $message ) {

    // Optionally, validate license and return empty string to disable link

    return $message;
  }, 10, 1);

  //Store Validation Error to transient
  
   add_filter('puc_request_info_result-' . $name, function($result, $url) use ($name) {
    if (isset($result->fail_update_status)) {
        set_transient('fail_update_status_' . $name, $result->fail_update_status, 0);
    } else {
        delete_transient('fail_update_status_' . $name);
    }
    return $result;
  }, 10, 2);

  //Display the validation error if transient exists
  
  add_action('in_plugin_update_message-' . $name . '/' . $name . '.php', function ($plugin) use ($name) {

    $fail_update_status = get_transient( 'fail_update_status_' . $name );

    if (!empty($fail_update_status)) {
      echo '<br /><span style="color: #d63638; font-weight: bold;">⚠️ <strong>Update Attempt Failed:</strong> ' . $fail_update_status . '</span><br />';
    }
  
  });
}

function register_theme($theme) {
  updater\register_plugin($theme);
}

function set_server_url($url) {
  updater::$instance->server_url = $url;
}
