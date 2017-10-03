<?php
/**
 * Adds WordPress.com, and Calypso localhost to safe redirect whitelist
 *
 * @since 0.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_api_dev_add_redirect_hosts( $content ) {
	$content[] = 'wordpress.com';
	$content[] = 'calypso.localhost';
	return $content;
}
add_filter( 'allowed_redirect_hosts', 'wc_api_dev_add_redirect_hosts' );
