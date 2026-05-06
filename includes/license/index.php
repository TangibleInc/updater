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

  return $settings[$field] ?? '';
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

// Install ID — persistent UUID that survives site URL changes.
//
// Stored as a single WP option shared across ALL Tangible plugins running on
// the same WordPress install. Server-side (Tangible cloud) keys site identity
// by install_id; treating install_id as per-plugin would create N distinct
// site rows for one physical WP install and thrash byInstallId lookups when
// multiple Tangible plugins phone home from the same site.
//
// The $plugin parameter is retained for call-site back-compat but is only
// consulted to migrate any pre-existing per-plugin install_id (from updater
// versions <= a19878d) into the shared option on first read. After migration
// the per-plugin setting is no longer consulted.
const SITE_INSTALL_ID_OPTION = 'tangible_site_install_id';

function get_install_id($plugin = null)
{
  $shared = \get_option(SITE_INSTALL_ID_OPTION, '');
  if (!empty($shared)) return $shared;

  // Back-compat: promote a legacy per-plugin install_id (if any) into the
  // shared option. First-found wins; subsequent plugins reuse it.
  if (!is_null($plugin)) {
    if (is_string($plugin)) {
      $plugin = framework\get_plugin($plugin);
      if (empty($plugin)) return '';
    }
    $settings = framework\get_plugin_settings($plugin);
    $legacy = $settings['install_id'] ?? '';
    if (!empty($legacy)) {
      \update_option(SITE_INSTALL_ID_OPTION, $legacy);
      return $legacy;
    }
  }

  return '';
}

function ensure_install_id($plugin = null)
{
  $existing = updater\get_install_id($plugin);
  if (!empty($existing)) return $existing;

  $install_id = \wp_generate_uuid4();
  \update_option(SITE_INSTALL_ID_OPTION, $install_id);

  return $install_id;
}
