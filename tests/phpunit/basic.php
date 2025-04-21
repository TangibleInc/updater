<?php
namespace tests\updater;
use tangible\updater;
use tangible\framework;

class Basic_TestCase extends \WP_UnitTestCase {

  public $plugin_name = 'test-plugin';

  function test_updater() {

    $this->assertTrue( class_exists( 'tangible\\updater' ) );
    $this->assertTrue( isset(updater::$instance) );
    $this->assertTrue( isset(updater::$instance->update_checkers) );
  }

  function register_with_framework() {
    $name = $this->plugin_name;

    return framework\register_plugin([
      'name'           => $name,
      'title'          => $name,
      'setting_prefix' => str_replace('-', '_', $name),
      'version'        => '0.0.0',
      'file_path'      => __DIR__ . '../plugin.php',
    ]);
  }

  function test_updater_with_framework() {
    $expected = $this->register_with_framework();
    $this->assertEquals( $expected, framework\get_plugin($this->plugin_name) );
  }

  function test_updater_register_plugin() {
    $name = $this->plugin_name;

    updater\register_plugin([
      'name' => $name,
      'file' => __DIR__ . '../plugin.php',
    ]);

    $this->assertTrue( isset(updater::$instance->update_checkers[ $name ]) );
  }

  function test_updater_license_get_set() {

    $name = $this->plugin_name;
    $license_key = 'abcdefg';

    $this->register_with_framework();

    updater\update_license_key($name, $license_key);

    $this->assertEquals( $license_key, updater\get_license_key($name) );
  }

  function test_updater_register_plugin_with_cloud() {

    $name = $this->plugin_name;
    $api_url = 'https://example.com';
    $cloud_id = '123';
    $license_key = 'abcdefg';

    $this->register_with_framework();

    updater\update_license_key($name, $license_key);
    $this->assertEquals( $license_key, updater\get_license_key($name) );

    updater\register_plugin([
      'name' => $name,
      'file' => __DIR__ . '../plugin.php',
      'cloud_id' => $cloud_id,
      'api' => $api_url
    ]);

    $this->assertTrue( isset(updater::$instance->update_checkers[ $name ]) );

    $checker = updater::$instance->update_checkers[ $name ];

    $this->assertTrue( isset($checker->metadataUrl) );

    $port = $_ENV['WP_ENV_TESTS_PORT'] ?? '3031';
    $test_site_url = 'http://localhost:' . $port;

    $expected =   $api_url . '?' . http_build_query([
      'action' => 'get_metadata',
      'slug' => $name,
      'pluginId' => $cloud_id,
      'license' => $license_key,
      'url' => $test_site_url
    ]);

    $this->assertEquals($expected, $checker->metadataUrl);
  }
}
