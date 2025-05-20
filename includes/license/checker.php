<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

function cloud_endpoint($plugin, $license_key, $action) {
  
  // Cloud post endpoint
  $response = wp_remote_post($plugin->activation_url, [
    'timeout'   => 30,
    'sslverify' => false,
    'body'      => [
        'edd_action' => $action,
        'item_id'    => $plugin->cloud_id,
        'license'    => $license_key,
        'url'        => home_url(),
    ]
  ]);

  return $response;
}

function response_code($response) {
  return wp_remote_retrieve_response_code($response);
}

function response_body($response) {
  return json_decode(wp_remote_retrieve_body($response));
}

function submit_action($plugin, $action) {

  $action = sanitize_text_field($action);

  if ($action === 'deactivate_license_clear') {
    $action = 'deactivate_license';

    // Clear license fields
    updater\update_license_key($plugin, '');
  }

  return $action;
}

function check_license_response($response, $plugin) {

  $message = __( 'An error occurred, please try again.' );

  switch( $response->error ) {
    case 'expired' :
      $message = sprintf(
        __( 'Your license key expired on %s.' ),
        date_i18n( get_option( 'date_format' ), strtotime( $response->expires, current_time( 'timestamp' ) ) )
      );
      break;
    case 'revoked' :
      $message = __( 'Your license key has been disabled.' );
      break;
    case 'missing' :
      $message = __( 'Invalid license.' );
      break;
    case 'invalid' :
    case 'site_inactive' :
      $message = __( 'Your license is not active for this URL.' );
      break;
    case 'item_id_mismatch' :
      $message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $plugin->name );
      break;
    case 'no_activations_left':
      $message = __( 'Your license key has reached its activation limit.' );
      break;
    case 'invalid_item_id':
      $message = sprintf( __( 'The license key is not valid for product ID %s.' ), $plugin->cloud_id );
      break;
    default:
      $message = __( 'An error occurred, please try again.' );
  }

  return $message;
}
