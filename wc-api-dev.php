<?php
/**
 * Plugin Name: WooCommerce API Dev
 * Plugin URI: https://woocommerce.com/
 * Description: A feature plugin providing a bleeding edge version of the WooCommerce REST API.
 * Version: 0.9.7
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Requires at least: 4.4
 * Tested up to: 4.8.2
 */

if ( ! file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
	// No WooCommerce installed, we don't need this.
	return;
}

// If the wc-api-dev plugin already exists in the conventionally installed
// directory, do nothing in order to avoid conflicts.
//
// TODO: Remove this after we are sure that all sites have the previously
// installed instances removed.
if ( file_exists( WP_PLUGIN_DIR . '/wc-api-dev/wc-api-dev.php' ) ) {
	if ( WP_PLUGIN_DIR . '/wc-api-dev/' !== plugin_dir_path( __FILE__ ) ) {
		// wc-api-dev is already installed conventionally, exiting to avoid conflict.
		return;
	}
}

// The coast is clear, load the class.
include_once( dirname( __FILE__ ) . '/wc-api-dev-class.php' );
