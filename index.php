<?php
namespace tangible;
use tangible\updater as updater;

if (!class_exists('tangible\\updater')) {
  class updater {
    static $instance;
  };
}

(include __DIR__ . '/module-loader.php')(new class extends \StdClass {

  public $name = 'tangible_plugin_updater';
  public $version = '20250122'; // Automatically updated with npm run version

  public $server_url = 'https://updater.tangible.one';
  public $update_checkers = [];

  function load() {
    updater::$instance = $this;
    require_once __DIR__ . '/includes/index.php';
  }

  function register_plugin( $plugin ) {
    updater\register_plugin( $plugin );
  }

  function register_theme( $theme ) {
    updater\register_plugin( $theme );
  }
});
