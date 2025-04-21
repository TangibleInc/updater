<?php
namespace tangible\updater;
use tangible\framework;
use tangible\updater;

function render_license_page($plugin) {
  if (!class_exists('tangible\\framework')) return;

  $settings_key = framework\get_plugin_settings_key($plugin);
  $subfield = updater\get_license_key_setting_field();

  $field_name = $settings_key . '[' . $subfield . ']';
  $field_value = updater\get_license_key($plugin);

  ?>
  <h2>License key</h2>
  <p>
    <input type="password"
      name="<?php echo esc_attr( $field_name ); ?>"
      value="<?php echo esc_attr( $field_value ); ?>"
    >
  </p>

  <?php
  submit_button();
}

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

function update_license_key($plugin, $license_key) {
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