<?php
/**
 * Jetpack compatibility hotfixes
 *
 * @since 0.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disables jetpack sync during rest requests to avoid lengthly (> 5 second) response
 * times during the shutdown action for things like product creation
 *
 * See also https://core.trac.wordpress.org/ticket/41358#ticket
 * See also https://github.com/Automattic/jetpack/pull/7482
 *
 * This can be removed once we have either of the two fixes above released. The first
 * trigger string is typical of a direct request (e.g. ala Postman) and the second
 * trigger string is typical of a request from WordPress.com for Jetpack.
 * 
 * See also https://github.com/woocommerce/woocommerce/pull/16158
 *
 * @since 0.7.0
 * @version 0.7.1
 */

function wc_api_dev_jetpack_sync_sender_should_load( $sender_should_load ) {
	$trigger_strings = array( '/wp-json/wc/v', '/?rest_route=%2Fwc%2Fv' );

	foreach( $trigger_strings as $trigger_string ) {
		if ( false !== strpos( $_SERVER[ 'REQUEST_URI' ], $trigger_string ) ) {
			$sender_should_load = false;
			break;
		}
	}

	return $sender_should_load;
}

add_filter( 'jetpack_sync_sender_should_load', 'wc_api_dev_jetpack_sync_sender_should_load', 999 );
