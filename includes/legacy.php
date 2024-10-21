<?php
// For backward compatibility

use tangible\updater as updater;

if ( ! function_exists( 'tangible_plugin_updater' ) ) :
  function tangible_plugin_updater( $arg = false ) {
    static $o;
    return $arg === false ? $o : ( $o = $arg );
  }
endif;

tangible_plugin_updater( updater::$instance );
