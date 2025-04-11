<?php
namespace tests\updater;
use tangible\updater;

class Basic_TestCase extends \WP_UnitTestCase {

  function test_updater() {

    $this->assertTrue( class_exists( 'tangible\\updater' ) );
    $this->assertTrue( isset(updater::$instance) );
    $this->assertTrue( isset(updater::$instance->update_checkers) );
  }

  function test_updater_register_plugin() {
    $name = 'test-plugin';
    
    updater\register_plugin([
      'name' => $name,
      'file' => __DIR__ . '../plugin.php',
    ]);

    $this->assertTrue( isset(updater::$instance->update_checkers[ $name ]) );
  }

  function test_updater_register_plugin_with_cloud() {
    $name = 'test-plugin';
    $api_url = 'https://example.com';
    $pluginId = '1';
    
    updater\register_plugin([
      'name' => $name,
      'file' => __DIR__ . '../plugin.php',
      'cloud' => [
        'id' => '1',
        'license' => 'none',
        'api' => $api_url
      ]
    ]);

    $this->assertTrue( isset(updater::$instance->update_checkers[ $name ]) );

    $checker = updater::$instance->update_checkers[ $name ];

    $this->assertTrue( isset($checker->metadataUrl) );

    $port = $_ENV['WP_ENV_TESTS_PORT'] ?? '3031';
    $test_site_url = 'http://localhost:' . $port;

    $expected =   $api_url . '?' . http_build_query([
      'action' => 'get_metadata',
      'slug' => $name,
      'pluginId' => $pluginId,
      'license' => 'none',
      'url' => $test_site_url
    ]);

    $this->assertEquals($expected, $checker->metadataUrl);
  }
}
