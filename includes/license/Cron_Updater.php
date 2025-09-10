<?php

namespace tangible\updater;

use tangible\framework;
use tangible\updater;

class Cron_Updater {

  protected $plugin_file;
  protected $plugin_config;

  public function __construct( $plugin_config = null, $plugin_file = null ) {
    $this->plugin_file = $plugin_file ?: __FILE__;
    $this->plugin_config = $plugin_config;

    // Register hooks
    register_activation_hook( $this->plugin_file, [ $this, 'activate_plugin' ] );
    register_deactivation_hook( $this->plugin_file, [ $this, 'deactivate_plugin' ] );

    // The actual action hook
    add_action( 'plugin_daily_expiration_license_check', [ $this, 'plugin_cron_activations_function' ] );
  }

  public function activate_plugin() {
    // Schedule the daily event
    if ( ! wp_next_scheduled( 'plugin_daily_expiration_license_check' ) ) {
      wp_schedule_event( time(), 'daily', 'plugin_daily_expiration_license_check' );
    }

    // Also run your expiration check on activation
    $this->plugin_cron_activations_function();
    // $this->expiration_daily_license_check();
  }

  public function deactivate_plugin() {
    // Clear the schedule on deactivation
    wp_clear_scheduled_hook( 'plugin_daily_expiration_license_check' );
  }

  // Your actual function that will run daily
  public function plugin_cron_activations_function() {
    // Your daily license check code here
    $license = updater\get_license_key( $this->plugin_config );

    // Skip if no license key exists
    if ( empty( $license ) ) {
      return;
    }

    // Cloud post endpoint
    $response = updater\cloud_endpoint( $this->plugin_config, $license, 'get_version' );

    // Get response details
    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );
    $response_headers = wp_remote_retrieve_headers( $response );

    $data = json_decode( $response_body, true );

    if ( ! $data || ! isset( $data['status'] ) ) {
      // error_log('Invalid license check response: ' . $response_body);
      return false;
    }

    updater\set_license_status( $this->plugin_config, $data['status'] );
  }
}
