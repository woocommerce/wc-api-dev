<?php
/**
 * Plugin Name: WooCommerce API Dev
 * Plugin URI: https://woocommerce.com/
 * Description: A feature plugin providing a bleeding edge version of the WooCommerce REST API.
 * Version: 0.8.4
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Requires at least: 4.4
 * Tested up to: 4.7
 */

// If the WC_API_Dev class already exists, the conventionally installed
// plugin must exist. Do nothing in order to avoid conflicts.
//
// TODO: Remove this after we are sure that all sites have the previously
// installed instances removed.
if ( class_exists( 'WC_API_Dev' ) ) {
	return;
}

if ( ! defined( 'WC_API_DEV_ENABLE_HOTFIXES' ) ){
	define( 'WC_API_DEV_ENABLE_HOTFIXES', true );
}

// The coast is clear, load the class.
include_once( dirname( __FILE__ ) . '/wc-api-dev-class.php' );

