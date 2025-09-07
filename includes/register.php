<?php
namespace tangible\updater;

use tangible\framework;
use tangible\updater;

function register_plugin( $plugin ) {

  if ( ! class_exists( 'Puc_v4_Factory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
  }

  if ( is_array( $plugin ) ) {
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

  $query = [
    'action' => 'get_metadata',
    'slug'   => $name,
  ];

  if ( isset( $plugin->cloud_id ) ) {

    // Query parameters passed to Cloud API
    $query['pluginId'] = $plugin->cloud_id;
    $query['license'] = $plugin->license ?? updater\get_license_key( $name );
    $query['url'] = site_url();

    // Provide default URLs
    $plugin->updater_url = $plugin->updater_url ??
    'https://cloud.tangible.one/api/plugin-update';
    // 'http://localhost:83/api/plugin-update'

    $plugin->activation_url = $plugin->activation_url ??
    'https://cloud.tangible.one/api/edd';
    // 'http://localhost:83/api/plugin-activation'

  }

  $server_url = $plugin->updater_url ?? $updater->server_url;

  if ( ! empty( $server_url ) ) {

    $url = $server_url . '?' . http_build_query( $query );

    $update_checker = \Puc_v4_Factory::buildUpdateChecker(
      $url, $file, $name
    );

    $updater->update_checkers[ $name ] = $update_checker;

    // Add a link "Check for updates" in the admin plugins list
    add_filter('puc_manual_check_link-' . $name, function ( $message ) {
      // Optionally, validate license and return empty string to disable link
      return $message;
    }, 10, 1);
  }

  if ( isset( $plugin->cloud_id ) ) {
    updater\init_plugin_with_license( $plugin );
  }
}

function register_theme( $theme ) {
  updater\register_plugin( $theme );
}

function set_server_url( $url ) {
  updater::$instance->server_url = $url;
}
