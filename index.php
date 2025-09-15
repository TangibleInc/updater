<?php
namespace tangible;

use tangible\updater;

if ( ! class_exists( 'tangible\\updater' ) ) {
  class updater {
    static $instance;
  }
}

( require __DIR__ . '/module-loader.php' )(new class() extends \StdClass {

  public $name = 'tangible_plugin_updater';
  public $version = '20250915'; // Automatically updated with npm run version

  public $server_url = 'https://updater.tangible.one';
  public $update_checkers = [];
  public $license_key_setting_field = 'license_key';
  public $license_status_setting_field = 'status_key';

  function load() {
    updater::$instance = $this;
    $this->url = plugins_url( '/', __FILE__ );
    require_once __DIR__ . '/includes/index.php';
  }

  function register_plugin( $plugin ) {
    updater\register_plugin( $plugin );
  }

  function register_theme( $theme ) {
    updater\register_plugin( $theme );
  }
});
