<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

$plugin->plugin_row_enqueued = [];

add_action('after_plugin_row_' . $name . '/' . $name . '.php', function($file) use ($plugin, $name) {

  if ($file !== $name . '/' . $name . '.php') return;

  $license = get_license_key($plugin);
  $license_status =get_license_status($plugin);

  $activation_url = admin_url('options-general.php?page='.$name.'-settings&tab=license');

  // Only show if license is missing or invalid
  if (!empty($license) && $license_status === 'valid') return;

  $plugin->plugin_row_enqueued [] = $name . '/' . $name . '.php';

  $message = empty($license) 
    ? __('License key is missing - please activate your license for plugin updates and support.', $name)
    : __('License key is invalid or expired - please renew your license for plugin updates and support.', $name);
    
  ?>
  <tr class="active plugin-update-tr">
    <td colspan="4" class="plugin-update colspanchange">
      <div class="update-message notice inline notice-error notice-alt">
        <p style="display: flex; align-items: center; gap: 8px;">
          <?php echo esc_html($message); ?>
          <a href="<?php echo esc_url($activation_url); ?>"
            style="font-weight: bold;">
            <?php echo __('Activate License', $name); ?>
          </a>
        </p>
      </div>
    </td>
  </tr>
  <?php

}, 10, 1);

add_action( 'admin_footer', function() use( $plugin ) {

  if (
    empty($plugin->plugin_row_enqueued)
    || get_current_screen()->id !== 'plugins'
  ) return;

  $updater = updater::$instance;

  $name = 'tangible-updater-license-activation-notice';
  $url = trailingslashit( $updater->url ) . 'includes/license/js/activation-notice.js';

  wp_register_script( $name, $url, [], $updater->version, true );

  wp_localize_script(
      $name,
      'tangibleUpdaterPluginRowEnqueued',
      $plugin->plugin_row_enqueued
  );
  
  wp_enqueue_script( $name );
} );
