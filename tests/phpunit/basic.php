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

  function test_updater_register_with_plugin_instance() {
    $name = $this->plugin_name;
    $plugin = $this->register_with_framework();

    updater\register_plugin( $plugin );

    $this->assertTrue( isset(updater::$instance->update_checkers[ $name ]) );
  }

}
