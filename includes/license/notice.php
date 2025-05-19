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
        
        ?>
        <tr class="active plugin-update-tr">
          <td colspan="4" class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
              <p style="display: flex; align-items: center; gap: 8px;">
                <?php echo esc_html($message); ?>
                <a href="<?php echo esc_url($activation_url); ?>"
                  style="font-weight: bold;">
                  <?php echo __('Activate License', $slug); ?>
                </a>
              </p>
            </div>
          </td>
        </tr>
        <?php
    }
  }

}, 10, 1);

add_action( 'admin_footer', function() use( $version, $plugin ) {
  if (
    empty($plugin->plugin_row_enqueued)
    || get_current_screen()->id !== 'plugins'
  ) return;
  $name = 'tangible-updater-license-activation-notice';
  $url = $plugin->url . 'vendor/tangible/updater/includes/license/js/activation-notice.js';

  wp_register_script( $name, $url, [], $version, true );

  wp_localize_script(
      $name,
      'tangibleUpdaterPluginRowEnqueued',
      $plugin->plugin_row_enqueued
  );
  
  wp_enqueue_script( $name );
} );
