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
    $query['install_id'] = updater\ensure_install_id( $plugin );

    // Provide default URLs.
    // Override per-site by defining TANGIBLE_CLOUD_URL in wp-config.php:
    //   define('TANGIBLE_CLOUD_URL', 'https://dev-site.tangible.one/api/edd');
    $default_url = defined('TANGIBLE_CLOUD_URL')
      ? TANGIBLE_CLOUD_URL
      : 'https://api.tangible.one/api/edd';

    $plugin->updater_url    = $plugin->updater_url    ?? $default_url;
    $plugin->activation_url = $plugin->activation_url ?? $default_url;

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

    // Cache the server's `distribution` verdict ("free" | "licensed") so the
    // rest of the module can suppress license nags for free plugins without
    // re-fetching metadata. The server is the sole source of truth here; the
    // plugin declares nothing.
    $update_checker->addResultFilter(function ( $info, $response = null ) use ( $plugin ) {
      $value = updater\distribution_from_response( $response );
      if ( $value !== null ) {
        update_option( ( $plugin->setting_prefix ?? $plugin->name ) . '_distribution', $value, false );
      }
      return $info;
    });
  }

  add_action( 'admin_init', function () use ( $plugin ) {
    if ( ! current_user_can( 'update_plugins' ) || wp_doing_ajax() ) return;
    updater\maybe_check_distribution( $plugin );
  } );

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

function is_free_distribution( $plugin ) {
  return get_option( ( $plugin->setting_prefix ?? $plugin->name ) . '_distribution' ) === 'free';
}

/**
 * The distribution verdict carried by one metadata response: 'free',
 * 'licensed', or null when the response says nothing (a network error or a
 * body that is not JSON) — null never overwrites what was cached. A valid
 * JSON answer WITHOUT the field comes from a server that predates free
 * distribution, where everything is licensed; recording that keeps "unknown"
 * meaning only "not checked yet".
 */
function distribution_from_response( $response ) {
  if ( ! $response || is_wp_error( $response ) ) return null;
  $body = json_decode( wp_remote_retrieve_body( $response ), true );
  if ( ! is_array( $body ) ) return null;
  return ( $body['distribution'] ?? 'licensed' ) === 'free' ? 'free' : 'licensed';
}

/**
 * Whether the server has said free or licensed yet. Until the first update
 * check answers, it has not — and a licence nag shown in that window would be
 * wrong for every free plugin.
 */
function is_distribution_known( $plugin ) {
  return in_array(
    get_option( ( $plugin->setting_prefix ?? $plugin->name ) . '_distribution' ),
    [ 'free', 'licensed' ],
    true
  );
}

/**
 * Ask sooner than the ~12h schedule when the verdict is still unknown (a
 * fresh install): one update check on an admin load, at most once an hour.
 */
function maybe_check_distribution( $plugin ) {
  if ( is_distribution_known( $plugin ) ) return;
  $name = $plugin->name;
  $throttle = 'tangible_updater_distribution_check_' . $name;
  if ( get_transient( $throttle ) ) return;
  set_transient( $throttle, 1, HOUR_IN_SECONDS );
  $checker = updater::$instance->update_checkers[ $name ] ?? null;
  if ( $checker && method_exists( $checker, 'checkForUpdates' ) ) {
    $checker->checkForUpdates();
  }
}
