<?php
namespace tangible\updater;

use tangible\updater;

/**
 * Initialize plugin with license
 */
function init_plugin_with_license( $plugin ) {

  $name = $plugin->name;
  $file = $plugin->file_path ?? $plugin->file;

  // Store validation result to transient

  $transient_key = 'tangible_updater_fail_update_status_' . $name;

  add_filter('puc_request_info_result-' . $name, function ( $result, $url ) use ( $transient_key ) {
    if ( isset( $result->fail_update_status ) ) {
        set_transient( $transient_key, $result->fail_update_status, 0 );
    } else {
        delete_transient( $transient_key );
    }
    return $result;
  }, 10, 2);

  // Display any error if transient exists

  $basename = plugin_basename( $file );
  $action_name = "in_plugin_update_message-{$basename}";

  add_action($action_name, function ( $plugin ) use ( $transient_key ) {

    $fail_update_status = get_transient( $transient_key );
    if (empty( $fail_update_status )) return;

    ?><br /><span style="color: #d63638; font-weight: bold;">
      ⚠️ Update failed: <?php
        echo esc_html( $fail_update_status );
      ?>
    </span><?php
  });

  // Set up plugin notices
  require __DIR__ . '/notice.php';

  // Check plugin license and cloud_id 
  updater\plugin_needs_license_check($plugin);

  // Initialize cron job
  add_action('init', function () use ($plugin) {

    $updater = new updater\Cron_Updater($plugin);
    // Run immediately: remove this later - for testing
    //$updater->plugin_cron_activations_function();
  });  
}
