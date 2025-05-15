<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

$updater = updater::$instance;

$version = $updater->version;
$slug    = $plugin->name;
$license = get_license_key($plugin);
$license_status =get_license_status($plugin);

$plugin->plugin_row_enqueued = [];

add_action('after_plugin_row_' . $slug . '/' . $slug . '.php', function($file) use ($slug, $license, $license_status, $plugin) {

  if (!is_admin()) return;
  
  if ($file === $slug . '/' . $slug . '.php') {
    $activation_url = admin_url('options-general.php?page='.$slug.'-settings&tab=license');

    // Only show if license is missing or invalid
    if (empty($license) || $license_status !== 'valid') {

      $plugin->plugin_row_enqueued [] = $slug . '/' . $slug . '.php';

      $message = empty($license) 
            ? __('License key is missing - please activate your license for plugin updates and support.', $slug)
            : __('License key is invalid or expired - please renew your license for plugin updates and support.', $slug);
        
        echo '<tr class="active plugin-update-tr">'; // Removes the top border
        echo '<td colspan="4" class="plugin-update colspanchange">';
        echo '<div class="update-message notice inline notice-error notice-alt">';
        echo '<p style="display: flex; align-items: center; gap: 8px;">';
          echo esc_html($message) . ' <a href="' . esc_url($activation_url) . '" style="font-weight: bold;">' . __('Activate License', $slug). '</a>';
        echo '</p>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
  }

}, 10, 1);

add_action( 'admin_footer', function() use( $version, $plugin ) {
 
  wp_register_script(
      'tangible-plugin-row-script-gh-js',
      plugin_dir_url(__FILE__).'js/activation-notice.js',
      ['jquery'],
      $version,
      true
  );

  wp_localize_script(
      'tangible-plugin-row-script-gh-js',
      'tangible_plugin_data_gh',
      $plugin->plugin_row_enqueued
  );
  
  wp_enqueue_script( 'tangible-plugin-row-script-gh-js' );
} );
