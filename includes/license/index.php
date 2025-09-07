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
