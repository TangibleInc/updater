<?php
namespace tangible\updater;

use tangible\framework;
use tangible\updater;

/**
 * Whether licence calls verify the server's TLS certificate. On by default:
 * with it off, anything on the network path can impersonate the licence
 * server and read the key. Local development against a server whose
 * certificate PHP does not trust (a self-signed or mkcert host) can opt out
 * with TANGIBLE_UPDATER_SSLVERIFY false or the filter.
 */
function should_verify_ssl( $plugin = null ) {
  $verify = defined( 'TANGIBLE_UPDATER_SSLVERIFY' ) ? (bool) TANGIBLE_UPDATER_SSLVERIFY : true;
  return (bool) apply_filters( 'tangible_updater_sslverify', $verify, $plugin );
}

function cloud_endpoint( $plugin, $license_key, $action ) {

  // Cloud post endpoint
  $response = wp_remote_post($plugin->activation_url, [
    'timeout'   => 30,
    'sslverify' => updater\should_verify_ssl( $plugin ),
    'body'      => [
        'edd_action'  => $action,
        'item_id'     => $plugin->cloud_id,
        'license'     => $license_key,
        'url'         => home_url(),
        'install_id'  => updater\get_install_id($plugin),
    ],
  ]);

  return $response;
}

/**
 * Whether another surface already speaks for this plugin's licence, so the
 * updater's own nags (the admin notice and the Plugins-row strip) stay quiet
 * and the admin sees one message, not two. The onboarding shell answers true
 * while its setup notice is showing for the plugin; once setup is finished,
 * a key removed, expired or revoked later is the updater's to report again.
 */
function licence_notice_yields( $plugin ) {
  return (bool) apply_filters( 'tangible_updater_licence_notice_yields', false, $plugin );
}

function check_plugin_license_exists($plugin, $endpoint = null) {
  if (empty($plugin->cloud_id)) {

    if ($endpoint == null) {
      add_admin_license_error_notice(
        $plugin,
        function () use ($plugin) {
          return sprintf(
            /* translators: %s: plugin name */
            __('The plugin %s does not have a valid Cloud ID.', 'tangible-updater'),
            $plugin->name
          );
        }
      );
    }

    return false;
  }

  $license = updater\get_license_key($plugin);

  if (empty($license)) {

    if (updater\is_free_distribution($plugin)) {
      return false;
    }

    if ($endpoint == null) {
      add_admin_license_error_notice(
        $plugin,
        function () use ($plugin) {
          return sprintf(
            /* translators: %s: plugin name */
            __('License key is missing for the plugin %s.', 'tangible-updater'),
            $plugin->name
          );
        },
        true
      );
    }

    return false;
  }
}

/**
 * $message is a string, or a closure that returns one. Pass a closure for a
 * translated message: the check runs as the plugin registers, before init,
 * when the tangible-updater translations are not loaded yet.
 */
function add_admin_license_error_notice($plugin, $message, $can_yield = false) {

  add_action('admin_notices', function () use ($plugin, $message, $can_yield) {
    // Decided at render, not when the check runs: the check runs as the plugin
    // registers with the updater, before the onboarding shell has registered
    // the wizard that answers the yield filter.
    if ($can_yield && licence_notice_yields($plugin)) return;
    if ($message instanceof \Closure) $message = $message();
    ?>
    <div class="notice notice-error">
      <p><?php echo esc_html($message); ?></p>
      <p><?php
        printf(
          /* translators: 1: start of the link to the license page, 2: its end */
          esc_html__('Please visit %1$sthe plugin license page%2$s.', 'tangible-updater'),
          '<a href="' . esc_attr(framework\get_plugin_settings_page_url($plugin, 'license')) . '">',
          '</a>'
        );
      ?></p>
    </div>
    <?php
  });
  
}

function response_code( $response ) {
  return wp_remote_retrieve_response_code( $response );
}

function response_body( $response ) {
  return json_decode( wp_remote_retrieve_body( $response ) );
}

function submit_action( $plugin, $action ) {

  $action = sanitize_text_field( $action );

  if ( $action === 'deactivate_license_clear' ) {
    $action = 'deactivate_license';

    // Clear license fields
    updater\update_license_key( $plugin, '' );
  }

  return $action;
}

function check_license_response( $response, $plugin ) {

  $message = __( 'An error occurred, please try again.', 'tangible-updater' );

  switch ( $response->error ) {
    case 'expired':
      $message = sprintf(
        /* translators: %s: the date the license key expired */
        __( 'Your license key expired on %s.', 'tangible-updater' ),
        date_i18n( get_option( 'date_format' ), strtotime( $response->expires, current_time( 'timestamp' ) ) )
      );
        break;
    case 'inactive':
      $message = sprintf(
        /* translators: %s: the license key's expiry date */
        __( 'Your license key inactive on %s.', 'tangible-updater' ),
        date_i18n( get_option( 'date_format' ), strtotime( $response->expires, current_time( 'timestamp' ) ) )
      );
        break;
    case 'revoked':
      $message = __( 'Your license key has been disabled.', 'tangible-updater' );
        break;
    case 'missing':
      $message = __( 'Invalid license.', 'tangible-updater' );
        break;
    case 'invalid':
    case 'site_inactive':
      $message = __( 'Your license is not active for this URL.', 'tangible-updater' );
        break;
    case 'item_id_mismatch':
      $message = sprintf(
        /* translators: %s: plugin name */
        __( 'This appears to be an invalid license key for %s.', 'tangible-updater' ),
        $plugin->name
      );
        break;
    case 'no_activations_left':
      $message = __( 'Your license key has reached its activation limit.', 'tangible-updater' );
        break;
    case 'invalid_item_id':
      $message = sprintf(
        /* translators: %s: the product's ID on the license server */
        __( 'The license key is not valid for product ID %s.', 'tangible-updater' ),
        $plugin->cloud_id
      );
        break;
    default:
      $message = __( 'An error occurred, please try again.', 'tangible-updater' );
  }

  return $message;
}
