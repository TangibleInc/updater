<?php
namespace tangible\updater;

use tangible\framework;
use tangible\updater;

const license_action_key = 'tangible_updater_license_action';
const license_cleared_and_deactivated = 'License cleared and deactivated';

// License front end
function render_license_page( $plugin ) {

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
  $is_valid = $license_status === 'valid';

  if ($license_status == 404) {
      $license_status = 'invalid or expired'; 
      $is_valid = false;
  }

  $license_status = esc_html( ucfirst($license_status) );
  ?>
  <h3>
    License Key 
    <span class="license-status-indicator">
  <?php if ( $is_valid ) : ?>
        <span class="valid-license success">&mdash;&nbsp;<b><?php echo $license_status; ?></b></span>
      <?php else : ?>
        <span class="invalid-license error">&mdash;&nbsp;<b><?php echo $license_status; ?></b></span>
      <?php endif; ?>
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
  // submit_button();
}
