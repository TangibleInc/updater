<?php

namespace tangible\updater;

use tangible\framework;
use tangible\updater;

require_once __DIR__ . '/action.php';
require_once __DIR__ . '/checker.php';
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/page.php';
require_once __DIR__ . '/Cron_Updater.php';

// License key
function get_license_key_setting_field()
{
  return updater::$instance->license_key_setting_field;
}

function get_license_key($plugin)
{
  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return;
  }

  // See /vendor/tangible/framework/plugin/settings
  $settings = framework\get_plugin_settings($plugin);
  $field = updater\get_license_key_setting_field();

  $key = $settings[$field] ?? '';
  if (is_string($key) && trim($key) !== '') return $key;

  return updater\adopt_legacy_license_key($plugin);
}

/**
 * Carry a licence key forward from the legacy plugin-framework.
 *
 * The old framework stored the key in its own option, "{setting_prefix}_license_key".
 * This one keeps it as a field inside "{setting_prefix}_settings", and nothing read
 * across the two. So a site upgrading from a legacy build to a modern one lost its
 * key: the field went blank, the plugin stopped activating and updating, and the
 * customer had to find and paste their key back in.
 *
 * That matters most for the very upgrade being shipped to move customers onto
 * tangible.one — an update whose entire point is that the customer does nothing.
 * Verified on a WordPress site with the released 0.2.1 build of Beaver Profile
 * Builder: without this, the key is gone; with it, the upgrade is invisible.
 *
 * Read-repair rather than a one-shot migration on activation, so adoption happens
 * whenever the key is first asked for (admin page, cron, update check) and does
 * not depend on load order or on an upgrade hook firing. Writing it forward makes
 * this a no-op from then on.
 */
function adopt_legacy_license_key($plugin)
{
  $prefix = $plugin->setting_prefix ?? '';
  if (!is_string($prefix) || $prefix === '') return '';

  $legacy = get_option("{$prefix}_license_key", '');
  if (!is_string($legacy) || trim($legacy) === '') return '';

  $legacy = trim($legacy);

  // Leave the legacy option in place: harmless, and it keeps a downgrade working.
  updater\update_license_key($plugin, $legacy);

  return $legacy;
}

function update_license_key($plugin, $license_key = '')
{

  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return;
  }

  $field = updater\get_license_key_setting_field();

  framework\update_plugin_settings($plugin, [
    $field => $license_key
  ]);
}

// Status key
function get_license_status_setting_field()
{
  return updater::$instance->license_status_setting_field ?? 'license_status';
}

function get_license_status($plugin)
{

  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return '';
  }

  $settings = framework\get_plugin_settings($plugin);
  $field = get_license_status_setting_field();

  return $settings[$field] ?? '';
}

function set_license_status($plugin, $status)
{

  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return false;
  }

  // Use the same helper function
  $field = get_license_status_setting_field();

  return framework\update_plugin_settings($plugin, [
    $field => $status
  ]);
}

// Install ID — persistent UUID that survives site URL changes
function get_install_id($plugin)
{
  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return '';
  }

  $settings = framework\get_plugin_settings($plugin);
  return $settings['install_id'] ?? '';
}

function ensure_install_id($plugin)
{
  if (is_string($plugin)) {
    $plugin = framework\get_plugin($plugin);
    if (empty($plugin)) return '';
  }

  $install_id = updater\get_install_id($plugin);
  if (!empty($install_id)) return $install_id;

  $install_id = wp_generate_uuid4();
  framework\update_plugin_settings($plugin, [
    'install_id' => $install_id,
  ]);

  return $install_id;
}
