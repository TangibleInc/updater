<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

const license_action_key = 'tangible_updater_license_action';
const license_cleared_and_deactivated = 'License cleared and deactivated';

// License front end
function render_license_page($plugin) {

  // Field name and value
  $settings_key = framework\get_plugin_settings_key($plugin);
  $subfield = updater\get_license_key_setting_field();

  $license_status = updater\get_license_status($plugin);

  $field_name = $settings_key . '[' . $subfield . ']';
  $field_value = $license_status !== updater\license_cleared_and_deactivated
    ? updater\get_license_key($plugin)
    : ''
  ;

  // License status
  $is_valid = $license_status === 'valid';

  ?>
  <h2>License Management</h2>
  <div class="license-input-section">
    <label for="license_key">License Key:</label>
    <input type="password" 
            id="license_key"
            name="<?php echo esc_attr($field_name); ?>" 
            value="<?php echo esc_attr($field_value); ?>"
            placeholder="Enter your license key">
    <?php if (!empty($license_status)) { ?>
      <span class="license-status-indicator">
        <?php if ($is_valid) : ?>
          <span class="valid-license"><b>✓ Activated</b></span>
        <?php else : ?>
          <span class="invalid-license"><b>✗ <?php echo ucfirst($license_status); ?></b></span>
        <?php endif; ?>
      </span>
    <?php } ?>
  </div>
  <br />
  <div class="license-buttons">
    <?php if ($is_valid) : ?>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="deactivate_license" class="button button-secondary">
        Deactivate
      </button>
      <button type="submit" name="<?php echo updater\license_action_key; ?>" value="deactivate_license_clear" class="button button-danger">
        Clear & Deactivate
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
