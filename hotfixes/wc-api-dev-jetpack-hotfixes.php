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
 * This can be removed once we have either of the two fixes above released
 * 
 * See also https://github.com/woocommerce/woocommerce/pull/16158
 */

function wc_api_dev_jetpack_sync_sender_should_load( $sender_should_load ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		$sender_should_load = false;
	}

	return $sender_should_load;
}

add_filter( 'jetpack_sync_sender_should_load', 'wc_api_dev_jetpack_sync_sender_should_load', 999 );
