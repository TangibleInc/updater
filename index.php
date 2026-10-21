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
  public $version = '20261014'; // Automatically updated with npm run version

  public $server_url = 'https://updater.tangible.one';
  public $update_checkers = [];
  public $license_key_setting_field = 'license_key';
  public $license_status_setting_field = 'status_key';

  function load() {
    updater::$instance = $this;
    $this->url = plugins_url( '/', __FILE__ );

    // The updater's own strings (license page and notices) are in the
    // tangible-updater domain, translated in languages/. As in the framework,
    // a site-level file in wp-content/languages/plugins/ wins: it is loaded
    // first, and the earliest file loaded for a domain takes precedence.
    $load_translations = function () {
      $mofile = 'tangible-updater-' . determine_locale() . '.mo';
      load_textdomain( 'tangible-updater', WP_LANG_DIR . '/plugins/' . $mofile );
      load_textdomain( 'tangible-updater', __DIR__ . '/languages/' . $mofile );
    };
    did_action( 'init' ) ? $load_translations() : add_action( 'init', $load_translations, 0 );

    require_once __DIR__ . '/includes/index.php';
  }

  function register_plugin( $plugin ) {
    updater\register_plugin( $plugin );
  }

  function register_theme( $theme ) {
    updater\register_plugin( $theme );
  }
});
