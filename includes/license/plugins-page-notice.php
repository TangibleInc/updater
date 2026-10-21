<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

$plugin->plugin_row_enqueued = [];

// The registered name can differ from the plugin's real folder/file, so hook
// the basename WordPress actually fires for instead of {name}/{name}.php.
$plugin_file = $plugin->file_path ?? $plugin->file ?? '';
$plugin_basename = $plugin->base_path
  ?? ( $plugin_file ? plugin_basename( $plugin_file ) : $name . '/' . $name . '.php' );

add_action('after_plugin_row_' . $plugin_basename, function($file) use ($plugin, $name, $plugin_basename) {

  if ($file !== $plugin_basename) return;

  // Free plugins never nag, and neither does a plugin whose distribution the
  // server has not stated yet (first update check pending).
  if (is_free_distribution($plugin) || !is_distribution_known($plugin)) return;

  // One licence message at a time: while the onboarder's setup notice speaks
  // for this plugin, the row stays quiet (its "Setup" link is right there).
  if (licence_notice_yields($plugin)) return;

  $license = get_license_key($plugin);
  $license_status =get_license_status($plugin);

  // Filterable so an onboarding shell can point "Activate License" at its
  // wizard while setup is still pending, instead of at a bare settings field.
  $activation_url = apply_filters(
    'tangible_updater_activation_url',
    admin_url('options-general.php?page='.$name.'-settings&tab=license'),
    $plugin
  );

  // Only show if license is missing or invalid
  if ( !empty($license) && ($license_status === 'valid' || $license_status === 'active')) return;

  $plugin->plugin_row_enqueued [] = $plugin_basename;

  // One whole sentence per status, so translators never see a status word
  // spliced into a sentence. A key that is present and active has returned
  // above, and any status other than inactive or expired reads as
  // "invalid or expired".
  if (empty($license)) {
    $message = __('License key is missing - please activate your license for plugin updates and support.', 'tangible-updater');
  } elseif ($license_status === 'inactive') {
    $message = __('License key is inactive - please renew your license for plugin updates and support.', 'tangible-updater');
  } elseif ($license_status === 'expired') {
    $message = __('License key is expired - please renew your license for plugin updates and support.', 'tangible-updater');
  } else {
    $message = __('License key is invalid or expired - please renew your license for plugin updates and support.', 'tangible-updater');
  }

  ?>
  <tr class="active plugin-update-tr">
    <td colspan="4" class="plugin-update colspanchange">
      <div class="update-message notice inline notice-error notice-alt">
        <p style="display: flex; align-items: center; gap: 8px;">
          <?php echo esc_html($message); ?>
          <a href="<?php echo esc_url($activation_url); ?>"
            style="font-weight: bold;">
            <?php esc_html_e('Activate License', 'tangible-updater'); ?>
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
