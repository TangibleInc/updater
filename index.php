<?php

if ( ! function_exists( 'tangible_plugin_updater' ) ) :

  function tangible_plugin_updater( $arg = false ) {
    static $o;
    return $arg === false ? $o : ( $o = $arg );
  }

endif;

new class {

  public $name = 'tangible_plugin_updater';

  // Remember to update the version - Expected format: YYYYMMDD
  public $version = '20220629';

  // Update server URL
  public $server_url =
    'https://updater.tangible.one';
    // 'http://localhost/updater' // For local development

  function __construct() {

    $name     = $this->name;
    $priority = 99999999 - absint( $this->version );

    remove_all_filters( $name, $priority );
    add_action( $name, [ $this, 'load' ], $priority );

    add_action('plugins_loaded', function() use ( $name ) {
      if ( ! did_action( $name )) do_action( $name );
    }, 0);

  }

  // Dynamic methods
  function __call( $method = '', $args = [] ) {
    if ( isset( $this->$method ) ) {
      return call_user_func_array( $this->$method, $args );
    }
    $caller = current( debug_backtrace() );
    echo "Warning: Undefined method \"$method\" for {$this->name}, called from <b>{$caller['file']}</b> in <b>{$caller['line']}</b><br>";
  }

  function load() {

    $name    = $this->name;
    $updater = $this;

    remove_all_filters( $name ); // First one to load wins
    tangible_plugin_updater( $this );
  }

  function register_plugin( $plugin ) {

    if ( ! class_exists( 'Puc_v4_Factory' ) ) {
      require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
    }

    $name = $plugin['name'];
    $file = $plugin['file'];

    if ( empty( $name ) || empty( $file ) ) {
      trigger_error( 'Plugin updater needs name and file', E_USER_WARNING );
      return;
    }

    $license = !empty( $plugin['license'] ) ? $plugin['license'] : 'free';

    $url = "{$this->server_url}?action=get_metadata&slug=$name&license_key=$license";

    Puc_v4_Factory::buildUpdateChecker( $url, $file, $name );

    // "Check for updates" link in the Plugins list page
    add_filter('puc_manual_check_link-' . $name, function( $message ) {
      // Validate license and return empty string to disable link
      return $message;
    }, 10, 1);
  }

  function register_theme( $theme ) {
    return $this->register_plugin( $theme );
  }
};
