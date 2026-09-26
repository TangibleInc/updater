<?php
namespace tangible\updater;

use tangible\framework;
use tangible\updater;

const license_action_key = 'tangible_updater_license_action';
const license_cleared_and_deactivated = 'License cleared and deactivated';

// License front end
function render_license_page( $plugin ) {

  // Free: no key needed, but one can still be attached (a bundle customer's
  // key is validated and recorded by the server all the same).
  $free = updater\is_free_distribution( $plugin );
  if ( $free ) {
    ?>
    <p>This plugin is free &mdash; no license key is needed. Updates arrive automatically.</p>
    <details><summary>Have a license key? Enter it</summary>
    <?php
  }

  // Field name and value
  $settings_key = framework\get_plugin_settings_key( $plugin );
  $subfield = updater\get_license_key_setting_field();

  $license_status = updater\get_license_status( $plugin );

  $field_name = $settings_key . '[' . $subfield . ']';

  $license_key = updater\get_license_key( $plugin );
  
  $field_value = (
      $license_status !== updater\license_cleared_and_deactivated 
      && !empty($license_key)
  ) ? $license_key : '';

  // License status
  $is_valid = $license_status === 'valid' || $license_status === 'active';

  if ($license_status == 404) {
    $license_status = 'invalid or expired';
    $is_valid = false;
  }

  $license_status = esc_html( ucfirst($license_status) );
  
  ?>
  <h3>
    License Key 
    <span class="license-status-indicator">
      <?php $license_status ? '&mdash;&nbsp;':'' ?>
      <span class="<?php
        echo $is_valid ? 'valid-license success' : 'invalid-license error';
      ?>">
        <b><?php echo $license_status; ?></b>
      </span>
    </span>
  </h3>
  <div class="license-input-section">
    <input type="password" class="regular-text"
            id="license_key"
            name="<?php echo esc_attr( $field_name ); ?>" 
            value="<?php echo esc_attr( $field_value ); ?>"
            placeholder="Enter License Key">
  </div>
  <br />
  <div class="license-buttons">
    <?php if ( $is_valid ) : ?>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="deactivate_license" class="button button-secondary">
        Deactivate
      </button>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="deactivate_license_clear" class="button button-danger">
        Deactivate &amp; Clear License
      </button>
    <?php else : ?>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="activate_license" class="button button-primary">
        Activate
      </button>
    <?php endif; ?>
  </div>
  <?php
  if ( $free ) echo '</details>';
  // submit_button();
}
