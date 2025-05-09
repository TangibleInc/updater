<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

CONST LICENSE_CLEARED_AND_DEACTIVATED = 'License Cleared and Deactivated';

//license page
function render_license_page($plugin) {
  if (!class_exists('tangible\\framework')) return;

  //field name and value
  $settings_key = framework\get_plugin_settings_key($plugin);
  $subfield = updater\get_license_key_setting_field();

  $license_status = get_license_status($plugin);

  $field_name = $settings_key . '[' . $subfield . ']';
  $field_value = (($license_status !== LICENSE_CLEARED_AND_DEACTIVATED)? updater\get_license_key($plugin):'');

  //license status
  $is_valid = ($license_status === 'valid');

  ?>
    <h2>License Management</h2>
    <div class="license-input-section">
      <label for="license_key">License Key:</label>
      <input type="password" 
             id="license_key"
             name="<?php echo esc_attr($field_name); ?>" 
             value="<?php echo esc_attr($field_value); ?>"
             placeholder="Enter your license key">
      <?php if(!empty($license_status)) { ?>
        <span class="license-status-indicator">
          <?php echo $is_valid 
            ? '<span class="valid-license"><b>✓ Activated</b></span>'
            : '<span class="invalid-license"><b>✗ '.ucfirst($license_status).'</b></span>'; ?>
        </span>
      <?php } ?>
    </div>
    <br />
    <div class="license-buttons">
      <?php if ($is_valid) : ?>
        <button type="submit" name="license_action" value="deactivate_license" class="button button-secondary">
          Deactivate
        </button>
        <button type="submit" name="license_action" value="deactivate_license_clear" class="button button-danger">
          Clear & Deactivate
        </button>
      <?php else : ?>
        <button type="submit" name="license_action" value="activate_license" class="button button-primary">
          Activate
        </button>
      <?php endif; ?>
    </div>
  <?php
  //auto change this to activate and deactivate button
  //submit_button();
}

//license key
function get_license_key_setting_field() {
  return updater::$instance->license_key_setting_field;
}

function get_license_key($plugin) {
  if (!class_exists('tangible\\framework')) return;
  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return;
  }

  // See /vendor/tangible/framework/plugin/settings
  $settings = framework\get_plugin_settings($plugin);
  $field = updater\get_license_key_setting_field();

  return $settings[ $field ] ?? '';
}

function update_license_key($plugin, $license_key ='') {

  if (!class_exists('tangible\\framework')) return;

  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return;
  }

  $field = updater\get_license_key_setting_field();
 
  framework\update_plugin_settings($plugin, [
    $field => $license_key
  ]);
}

//status key
function get_license_status_setting_field() {
  // Check if instance and property exist, otherwise return default
  return property_exists(updater::$instance, 'license_status_setting_field') 
      ? updater::$instance->license_status_setting_field
      : 'license_status'; // Default fallback
}

function get_license_status($plugin) {
  if (!class_exists('tangible\\framework')) return '';
  
  if (is_string($plugin)) {
      $plugin = framework\get_plugin($plugin);
      if (empty($plugin)) return '';
  }

  $settings = framework\get_plugin_settings($plugin);
  $field = get_license_status_setting_field(); // Use the function
  
  return $settings[$field] ?? '';
}

function set_license_status($plugin, $status) {
  if (!class_exists('tangible\\framework')) return false;
  
  if (is_string($plugin)) {
      $plugin = framework\get_plugin($plugin);
      if (empty($plugin)) return false;
  }

  $field = get_license_status_setting_field(); // Use the same helper function
  
  return framework\update_plugin_settings($plugin, [
      $field => $status
  ]);
}

//create an update function here for cloud to activate on wordpress end.
add_filter('tangible_plugin_save_settings_on_submit', function(
  $should_update, 
  $plugin, 
  $new_settings
) {

  if (!isset($_POST['license_action'])) {
    return $should_update;
  }

  try {

    process_license_action($plugin, $new_settings);
    return $should_update;

  } catch (Exception $e) {
    handle_error($e->getMessage());
    return false;
  }

}, 10, 3);

function process_license_action($plugin, $new_settings) {
  // Validate required plugin properties
  if (empty($plugin->cloud_activation_url)) {
    throw new Exception('Cloud license activation URL is missing');
  }

  if (empty($plugin->cloud_id)) {
    throw new Exception('Product ID is missing');
  }

  $action = submit_action($plugin, $_POST['license_action']);
  $license_key = sanitize_text_field($new_settings['license_key'] ?? '');

  $response = updater\cloud_endpoint($plugin, $license_key, $action);

  //set license key activation status if valid or invalid based on the $response
  $response_code = updater\response_code($response);
  $response_body = updater\response_body($response);

  // Check response if successful
  $error_message = '';
  if (is_wp_error($response) || $response_code === 403) {

    if ( is_wp_error( $response ) ) {
      $error_message = 'License server error: ' . $response->get_error_message();
    } else {
      $error_message = $response_body['error'] ?? 'License validation failed (Forbidden)';
    }

  } else {
    if(false === $response_body->success) {
      $error_message = updater\check_license_response($response_body, $plugin);
    }
  }

  //set license status
  $status = ($_POST['license_action'] === 'deactivate_license_clear' || !empty($error_message)) ? LICENSE_CLEARED_AND_DEACTIVATED :$response_body->license;
  set_license_status($plugin, $status);

  //add notice here - add redirect notice (need on tangibe framework)
  if(!empty($error_message)) {
    handle_error($error_message);
  }
}

function handle_error($message) {
  error_log('License error: ' . $message);
  
  framework\register_admin_notice(function() use ($message) {
      echo '<div class="notice notice-error is-dismissible">'
         . '<p>' . esc_html($message) . '</p>'
         . '</div>';
  });
}
