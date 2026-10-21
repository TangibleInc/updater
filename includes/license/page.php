<?php
namespace tangible\updater;

use tangible\framework;
use tangible\updater;

const license_action_key = 'tangible_updater_license_action';

// Stored as the license status, so it stays in English: it is compared
// against what is saved. license_status_label() translates it for display.
const license_cleared_and_deactivated = 'License cleared and deactivated';

/**
 * The license status as shown on the license page. Statuses are stored as the
 * server sent them (or as the sentinel above); the known ones are translated
 * here, and anything else is shown as it was before, capitalised.
 */
function license_status_label( $status ) {
  switch ( $status ) {
    case 'valid':
      return _x( 'Valid', 'license status', 'tangible-updater' );
    case 'active':
      return _x( 'Active', 'license status', 'tangible-updater' );
    case 'inactive':
      return _x( 'Inactive', 'license status', 'tangible-updater' );
    case 'expired':
      return _x( 'Expired', 'license status', 'tangible-updater' );
    case 'disabled':
      return _x( 'Disabled', 'license status', 'tangible-updater' );
    case 'invalid':
      return _x( 'Invalid', 'license status', 'tangible-updater' );
    case 'invalid or expired':
      return _x( 'Invalid or expired', 'license status', 'tangible-updater' );
    case license_cleared_and_deactivated:
      return _x( 'License cleared and deactivated', 'license status', 'tangible-updater' );
  }
  return ucfirst( (string) $status );
}

// License front end
function render_license_page( $plugin ) {

  // Free: no key needed, but one can still be attached (a bundle customer's
  // key is validated and recorded by the server all the same).
  $free = updater\is_free_distribution( $plugin );
  if ( $free ) {
    ?>
    <p><?php esc_html_e( 'This plugin is free — no license key is needed. Updates arrive automatically.', 'tangible-updater' ); ?></p>
    <details><summary><?php esc_html_e( 'Have a license key? Enter it', 'tangible-updater' ); ?></summary>
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

  $license_status = esc_html( updater\license_status_label( $license_status ) );

  ?>
  <h3>
    <?php esc_html_e( 'License Key', 'tangible-updater' ); ?>
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
            placeholder="<?php esc_attr_e( 'Enter License Key', 'tangible-updater' ); ?>">
  </div>
  <br />
  <div class="license-buttons">
    <?php if ( $is_valid ) : ?>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="deactivate_license" class="button button-secondary">
        <?php esc_html_e( 'Deactivate', 'tangible-updater' ); ?>
      </button>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="deactivate_license_clear" class="button button-danger">
        <?php esc_html_e( 'Deactivate & Clear License', 'tangible-updater' ); ?>
      </button>
    <?php else : ?>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="activate_license" class="button button-primary">
        <?php esc_html_e( 'Activate', 'tangible-updater' ); ?>
      </button>
    <?php endif; ?>
  </div>
  <?php
  if ( $free ) echo '</details>';
  // submit_button();
}
