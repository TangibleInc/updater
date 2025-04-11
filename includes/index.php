<?php
namespace tangible\updater;
use tangible\updater as updater;

function register_plugin($plugin) {

  if ( ! class_exists( 'Puc_v4_Factory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
  }

  $name = $plugin['name'];
  $file = $plugin['file'];

  if ( empty( $name ) || empty( $file ) ) {
    trigger_error( 'Updater needs name and file', E_USER_WARNING );
    return;
  }

  $updater = updater::$instance;
  if (empty($updater->server_url)) return;

  $server_url = $plugin['api'] ?? $updater->server_url;
  $cloud = $plugin['cloud'] ?? []; // id, license

  $query = [
    'action' => 'get_metadata',
    'slug' => $name,
  ];

  if (isset($cloud['id'])) {
    $server_url = $cloud['api'] ?? $server_url;
    $query['pluginId'] = $cloud['id'];
    $query['license'] = $cloud['license'] ?? '';
    $query['url'] = site_url();
  }

  $url = $server_url . '?' . http_build_query($query);

  $update_checker = \Puc_v4_Factory::buildUpdateChecker(
    $url, $file, $name
  );

  $updater->update_checkers[ $name ] = $update_checker;

  // Add a link "Check for updates" in the admin plugins list
  add_filter('puc_manual_check_link-' . $name, function( $message ) {

    // TODO: Validate license and return empty string to disable link

    return $message;
  }, 10, 1);

}

function register_theme($theme) {
  updater\register_plugin($theme);
}

function set_server_url($url) {
  updater::$instance->server_url = $url;
}

require_once __DIR__ . '/legacy.php';
