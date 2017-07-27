<?php
/**
 * WooCommerce has settings for email subject and footer, which defaults to the 'site title'.
 * However, this can easily become out of sync. This uses the provided filters to always
 * return the current site title.
 *
 * @since 0.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_api_dev_email_from_name( $name ) {
	return get_bloginfo( 'name', 'display' );
}
add_filter( 'woocommerce_email_from_name', 'wc_api_dev_email_from_name' );

function wc_api_dev_email_footer_text( $text ) {
	return sprintf( __( '%s - Powered by WooCommerce', 'woocommerce' ), get_bloginfo( 'name', 'display' ) );
}
add_filter( 'woocommerce_email_footer_text', 'wc_api_dev_email_footer_text' );
