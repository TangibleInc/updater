<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

/**
 * Process license action on saving license key
 * @see /framework/plugin/settings
 */
add_filter('tangible_plugin_save_settings_on_submit', function(
  $should_update, 
  $plugin, 
  $new_settings
) {

  if (!$should_update) return;

  if (isset($_POST[ updater\license_action_key ])) {
    try {
      updater\process_license_action($plugin, $new_settings);
    } catch (Exception $e) {
      updater\handle_license_error($e->getMessage());
    }
  }

  return $should_update;
}, 10, 3);

function process_license_action($plugin, $new_settings) {

  // Validate required plugin properties
  if (empty($plugin->cloud_id)) return;

  $license_action = $_POST[ updater\license_action_key ];

  $action = updater\submit_action($plugin, $license_action);
  $license_key = sanitize_text_field($new_settings['license_key'] ?? '');

  $response = updater\cloud_endpoint($plugin, $license_key, $action);

  $response_code = updater\response_code($response);
  $response_body = updater\response_body($response);

   // Handle error responses
  $error_message = '';
  if (is_wp_error($response) || $response_code === 403) {

     $error_message = is_wp_error($response) 
            ? 'License server error: ' . $response->get_error_message()
            : $response_body['error'] ?? 'License validation failed (Forbidden)';
  } else {
    if (false === $response_body->success) {
      $error_message = updater\check_license_response($response_body, $plugin);
    }
  }

  // Set license status based on action or error
  $status = ($license_action === 'deactivate_license_clear' || !empty($error_message))
    ? updater\license_cleared_and_deactivated
    : $response_body->license
  ;

  updater\set_license_status($plugin, $status);

  // Display error notice if applicable
  if (!empty($error_message)) {
    updater\handle_license_error($error_message);
  }
}

function handle_license_error($message) {
  error_log('License error: ' . $message);
  
  framework\register_admin_notice(function() use ($message) {
      echo '<div class="notice notice-error is-dismissible">'
         . '<p>' . esc_html($message) . '</p>'
         . '</div>';
  });
}
