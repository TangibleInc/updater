<?php
namespace tests\updater;
use tangible\updater;
use tangible\framework;

/**
 * Free distribution: the cached verdict, and which licence UI it silences.
 */
class Distribution_TestCase extends \WP_UnitTestCase {

  private function response($body) {
    return [ 'response' => [ 'code' => 200 ], 'body' => $body ];
  }

  private function plugin() {
    return framework\register_plugin([
      'name' => 'dist-plugin', 'title' => 'Dist Plugin', 'setting_prefix' => 'dist_plugin',
      'version' => '0.0.0', 'file_path' => __DIR__ . '/../plugin.php',
    ]);
  }

  function tearDown(): void {
    delete_option('dist_plugin_distribution');
    parent::tearDown();
  }

  function test_free_and_licensed_answers() {
    $this->assertSame('free', updater\distribution_from_response($this->response('{"distribution":"free"}')));
    $this->assertSame('licensed', updater\distribution_from_response($this->response('{"distribution":"licensed"}')));
  }

  function test_an_answer_without_the_field_counts_as_licensed() {
    // Older servers never send it; unknown must not mean "never nag".
    $this->assertSame('licensed', updater\distribution_from_response($this->response('{"version":"1.0"}')));
  }

  function test_errors_and_non_json_change_nothing() {
    $this->assertNull(updater\distribution_from_response(new \WP_Error('http', 'down')));
    $this->assertNull(updater\distribution_from_response($this->response('<html>502</html>')));
    $this->assertNull(updater\distribution_from_response(null));
  }

  function test_known_only_once_the_server_has_answered() {
    $plugin = $this->plugin();
    $this->assertFalse(updater\is_distribution_known($plugin));
    update_option('dist_plugin_distribution', 'licensed');
    $this->assertTrue(updater\is_distribution_known($plugin));
    $this->assertFalse(updater\is_free_distribution($plugin));
    update_option('dist_plugin_distribution', 'free');
    $this->assertTrue(updater\is_free_distribution($plugin));
  }

  function test_free_licence_page_says_so_and_keeps_an_optional_field() {
    $plugin = $this->plugin();
    update_option('dist_plugin_distribution', 'free');
    ob_start();
    updater\render_license_page($plugin);
    $html = ob_get_clean();
    $this->assertStringContainsString('no license key is needed', $html);
    $this->assertStringContainsString('<details>', $html);
    $this->assertStringContainsString('id="license_key"', $html);
  }
}
